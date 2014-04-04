#include <iostream>
#include <wildlife_graphics.hpp>

#ifdef __APPLE__
#  include <OpenGL/gl.h>
#  include <OpenGL/glu.h>
#  include <GLUT/glut.h>
#else
#  include <GL/gl.h>
#  include <GL/glu.h>
#  include <GL/glut.h>
#endif


#include <util.h>
#include <diagnostics.h>
#include <boinc_api.h>
#include <graphics2.h>

#include <FTGL/ftgl.h>

#include <parse.h>
#include <gutil.h>
#include <app_ipc.h>

#include <opencv2/opencv.hpp>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>

#include "boinc_utils.hpp"

using namespace std;
using namespace cv;

float white[4] = {1.0, 1.0, 1.0, 1.0};
float black[4] = {0.0, 0.0, 0.0, 1.0};
float green[4] = {0.0, 0.6, 0.3, 1.0};
float color[4];

//TEXTURE_DESC logo;
APP_INIT_DATA uc_aid;
WILDLIFE_SHMEM* shmem = NULL;

int window_width, window_height;
int mouse_x, mouse_y;
bool mouse_down;
GLuint texture = 0;

// FTGL
FTGLPixmapFont *font;
string font_filename = "LiberationSans-Regular.ttf";

// OpenCV
VideoCapture capture;
unsigned int currentTime = 0;
unsigned int previousTime = 0;
unsigned int frameCount = 0;
unsigned int currentFrame = 0;
unsigned int shmemFrame = 0;
double fps = 0;


void renderText(float x, float y, const char* text, const int size, const float color[4] = white) {
    int viewport[4];
    glGetIntegerv(GL_VIEWPORT, viewport);
    glMatrixMode(GL_PROJECTION);
    glPushMatrix();
    glLoadIdentity();
    glOrtho(viewport[0], viewport[2], viewport[1], viewport[3], -1, 1);
    glMatrixMode(GL_MODELVIEW);
    glLoadIdentity();
    glColor4f(color[0], color[1], color[2], color[3]);
    glRasterPos2f(x, viewport[3] - y);
    const int length = (int)strlen(text);
    font->FaceSize(size);
    font->Render(text);
    glMatrixMode(GL_PROJECTION);
    glPopMatrix();
    glMatrixMode(GL_MODELVIEW);
    glPopMatrix();
}

static void draw_text() {
    static float x=0, y=0;
    char buf[256];
    double fd = 0, cpu=0, dt;
    if (shmem) {
        fd = shmem->fraction_done;
        cpu = shmem->cpu_time;
    }
    sprintf(buf, "Wildlife@Home");
    renderText(window_width-(window_width/1.5), window_width/12 + 12, buf, window_width/12, color);
    sprintf(buf, "User: %s", uc_aid.user_name);
    renderText(10, window_height-10, buf, 25);
    sprintf(buf, "Team: %s", uc_aid.team_name);
    renderText(10, window_height-30, buf, 25);
    sprintf(buf, "%% Done: %f", 100*fd);
    renderText(10, window_height-50, buf, 25);
    sprintf(buf, "CPU time: %f", cpu);
    renderText(10, window_height-70, buf, 25);
    if(shmem) {
        //cout << fixed << "Time: " << getTimeInSeconds() << endl;
        dt = getTimeInSeconds() - shmem->update_time;
        //cout << "DT: " << dt << endl;
        if(dt > 10) {
            boinc_close_window_and_quit("shmem not updated");
        } else if (dt > 5) {
            renderText(10, window_height*0.5, "App not running - exiting", 25);
        } else if (shmem->status.suspended) {
            renderText(10, window_height*0.5, "App Suspended", 25);
        }

        sprintf(buf, "Features Collected: %d", shmem->feature_count);
        renderText(10, window_height-90, buf, 25);
        sprintf(buf, "Average Collected Features: %f", shmem->feature_average);
        renderText(10, window_height-110, buf, 25);
        sprintf(buf, "Species: %s", shmem->species);
        renderText(10, window_height-130, buf, 25);
    } else {
        glRasterPos2f(0.05f, 0.21f);
        renderText(10, window_height*0.5, "No Shared Mem", 25);
    }
}

void draw_video() {
    glEnable(GL_TEXTURE_2D);
    glBindTexture(GL_TEXTURE_2D, texture);

    // Set image width/height
    const int iw = 500;
    const int ih = 500;

    // Set Projection Matrix
    glMatrixMode(GL_PROJECTION);
    glLoadIdentity();
    gluOrtho2D(0, iw, ih, 0);

    // Switch to Model View Matrix
    glMatrixMode(GL_MODELVIEW);
    glLoadIdentity();

    glBegin(GL_QUADS);
        glTexCoord2i(0,0); glVertex2i(0,0);
        glTexCoord2i(1,0); glVertex2i(iw,0);
        glTexCoord2i(1,1); glVertex2i(iw,ih);
        glTexCoord2i(0,1); glVertex2i(0,ih);
    glEnd();
    glDisable(GL_TEXTURE_2D);
}

void display() {
    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

    if(shmem) {
        draw_video();
    }
    mode_unshaded();
    mode_ortho();
    draw_text();
    ortho_done();

    glFlush();
}

void load_frame() {
    usleep(1000000/fps);
    if(shmem) {
        if(currentFrame != shmemFrame) {
            currentFrame = shmemFrame;
            capture.set(CV_CAP_PROP_POS_FRAMES, currentFrame);
        }
        Mat frame;
        capture >> frame;
        if(frame.empty()) {
            capture.release();
            cerr << "Exiting... " << endl;
            exit(0);
        } else {
            cv::Rect roi(window_width-(window_width/1.5), window_width/12 + 12, 100, 50);
            Mat image_roi = frame(roi);
            Scalar avg = cv::mean(image_roi);
            if (avg.val[0] * 0.2126 < 30) {
                color[0] = green[0];
                color[1] = green[1];
                color[2] = green[2];
                color[3] = green[3];
            } else {
                color[0] = white[0];
                color[1] = white[1];
                color[2] = white[2];
                color[3] = white[3];
            }
            //cout << "Pixel intensity over ROI = " << avg.val[0] * 0.2126 << ", " << avg.val[1] * 0.7152 << ", " << avg.val[2] * 0.0722 << endl;
            cvtColor(frame, frame, CV_BGR2RGB);
            gluBuild2DMipmaps(GL_TEXTURE_2D, GL_RGB, frame.cols, frame.rows, GL_RGB, GL_UNSIGNED_BYTE, frame.data);
        }
    }
    display();
}

void draw_logo() {
    //if (logo.present) {
        cerr << "Logo is present" << endl;
    //}
}

void app_graphics_init() {
    // Load logo from disk here and initialize the viewport
    cerr << "Init..." << endl;
    glClearColor(0.0f, 0.0f, 0.0f, 1.0f);

#ifdef _BOINC_APP_
    font_filename = getBoincFilename(font_filename);
#endif

    font = new FTGLPixmapFont(font_filename.c_str());
    if(font->Error()) {
        cerr << "The font file could not be loaded." << endl;
        exit(1);
    }

    cerr << "Start SHMEM..." << endl;
    if (shmem == NULL) {
        cerr << "Init SHMEM... " << endl;
        shmem = (WILDLIFE_SHMEM*)boinc_graphics_get_shmem("wildlife_surf_collect");
    }
    if(shmem != NULL) {
        cerr << "Init video file... " << endl;
        capture.open(shmem->filename);
        if(!capture.isOpened()) {
            cerr << "Failed to open '" << shmem->filename << "'" << endl;
            exit(1);
        }
        cerr << "Filename: '" << shmem->filename << "'" << endl;
        fps = capture.get(CV_CAP_PROP_FPS);
        cerr << "FPS: " << fps << endl;
    }
}

void app_graphics_render(int xs, int ys, double time_of_day) {
    if (shmem == NULL) {
        cerr << "Init SHMEM... " << endl;
        shmem = (WILDLIFE_SHMEM*)boinc_graphics_get_shmem("wildlife_surf_collect");
        if(shmem != NULL) {
            capture.open(shmem->filename);
            if(!capture.isOpened()) {
                cerr << "Failed to open '" << shmem->filename << "'" << endl;
                exit(1);
            }
            fps = capture.get(CV_CAP_PROP_FPS);
            cerr << "FPS: " << fps << endl;
        }
    } else {
        shmemFrame = shmem->frame;
        fps = shmem->fps;
    }
    load_frame();
}

void app_graphics_resize(int w, int h) {
    window_width = w;
    window_height = h;
    glViewport(0, 0, window_width, window_height);
}

void boinc_app_mouse_move(int x, int y, int left, int middle, int right) {}
void boinc_app_mouse_button(int x, int y, int which, int is_down) {}
void boinc_app_key_press(int which, int is_down) {}
void boinc_app_key_release(int which, int is_down) {}

int main(int argc, char** argv) {
    boinc_init_graphics_diagnostics(BOINC_DIAG_DEFAULTS);

#ifdef __APPLE__
    //setMacIcon(argv[0], MacAppIconData, sizeof(MacAppIconData));
#endif

    boinc_parse_init_data_file();
    boinc_get_init_data(uc_aid);
    if (uc_aid.project_preferences) {
//        parse_project_prefs(uc_aid.project_preferences);
    }
    cerr << "Starting..." << endl;
    boinc_graphics_loop(argc, argv);
    boinc_finish_diag();
}
