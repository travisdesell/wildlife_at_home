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
#include <ttfont.h>

#include <boinc_gl.h>
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
float color[4] = {0.7, 0.2, 0.5, 1.0};

//TEXTURE_DESC logo;
APP_INIT_DATA uc_aid;
WILDLIFE_SHMEM* shmem = NULL;

int window_width, window_height;
int mouse_x, mouse_y;
bool mouse_down;
GLuint texture = 0;

// OpenCV
VideoCapture capture;
unsigned int currentTime = 0;
unsigned int previousTime = 0;
unsigned int frameCount = 0;
unsigned int currentFrame = 0;
unsigned int shmemFrame = 0;
double fps = 0;

static void draw_text() {
    static float x=0, y=0;
    static float dx=0.0003, dy=0.0007;
    char buf[256];
    x += dx;
    y += dy;
    if (x < 0 || x > .5) dx *= -1;
    if (y < 0 || y > .4) dy *= -1;
    double fd = 0, cpu=0, dt;
    if (shmem) {
        fd = shmem->fraction_done;
        cpu = shmem->cpu_time;
    }
    sprintf(buf, "User: %s", uc_aid.user_name);
    TTFont::ttf_render_string(x, y, 0, 500, white, buf);
    sprintf(buf, "Team: %s", uc_aid.team_name);
    TTFont::ttf_render_string(x, y+.1, 0, 500, white, buf);
    sprintf(buf, "%% Done: %f", 100*fd);
    TTFont::ttf_render_string(x, y+.2, 0, 500, white, buf);
    sprintf(buf, "CPU time: %f", cpu);
    TTFont::ttf_render_string(x, y+.3, 0, 500, white, buf);
/*
    if (shmem) {
        dt = dtime() - shmem->update_time;
        if (dt > 10) {
            boinc_close_window_and_quit("shmem not updated");
        } else if (dt > 5) {
            TTFont::ttf_render_string(0, 0, 0, 500, white, "App not running - exiting in 5 seconds");
        } else if (shmem->status.suspended) {
            TTFont::ttf_render_string(0, 0, 0, 500, white, "App suspended");
        }
    } else {
        ttf_render_string(0, 0, 0, 500, white, "No shared mem");
    }
*/
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

    draw_video();
    //draw_text();

    glFlush();
}

void load_frame() {
    usleep(1000000/fps);
    if(currentFrame != shmemFrame) {
        currentFrame = shmemFrame;
        capture.set(CV_CAP_PROP_POS_FRAMES, currentFrame);
    }
    Mat frame;
    capture >> frame;
    if(frame.empty()) {
        capture.release();
        cout << "Exiting... " << endl;
        exit(0);
    } else {
        cvtColor(frame, frame, CV_BGR2RGB);
        gluBuild2DMipmaps(GL_TEXTURE_2D, GL_RGB, frame.cols, frame.rows, GL_RGB, GL_UNSIGNED_BYTE, frame.data);
        display();
    }
}

void draw_logo() {
    //if (logo.present) {
        cerr << "Logo is present" << endl;
    //}
}

void app_graphics_init() {
    // Load logo from disk here and initialize the viewport
    glClearColor(0.0f, 0.0f, 0.0f, 0.0f);
}

void app_graphics_render(int xs, int ys, double time_of_day) {
    if (shmem == NULL) {
        shmem = (WILDLIFE_SHMEM*)boinc_graphics_get_shmem("wildlife_surf_collect");
    } else {
        // Update stuff here.
        // Check simple timeout function
        //boinc_close_window_and_quit("shmem not updated");

        shmemFrame = shmem->frame;
        fps = shmem->fps;
        cout << "Frame: " << shmemFrame << endl;
        cout << "FPS: " << fps << endl;

        load_frame();

        if (shmem->status.suspended) {
            // Show suspended message
            cout << "Suspended." << endl;
        }
    }
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
    string filename = "/Users/kgoehner/Dropbox/birds/validation/CH00_20120625_125529MN.mp4";
    //capture.open(0);
    capture.open(filename.c_str());
    if(!capture.isOpened()) {
        //cerr << "Failed to open '" << vidFilename.c_str() << "'" << endl;
        return 1;
    }
    fps = capture.get(CV_CAP_PROP_FPS);

    cout << "FPS: " << fps << endl;

    // Init
    /*
    glutInit(&argc, argv);
    glutInitDisplayMode(GLUT_RGBA | GLUT_DEPTH | GLUT_DOUBLE);
    glutInitWindowSize(800, 600);
    glutCreateWindow("Wildlife@Home");
    glutDisplayFunc(display);
    glutReshapeFunc(app_graphics_resize);
    glutIdleFunc(idle);
    glutMainLoop();
    */

    boinc_parse_init_data_file();
    boinc_get_init_data(uc_aid);
    if (uc_aid.project_preferences) {
//        parse_project_prefs(uc_aid.project_preferences);
    }
    cout << "Starting..." << endl;
    boinc_graphics_loop(argc, argv);
    boinc_finish_diag();
}
