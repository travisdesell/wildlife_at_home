#include <iostream>
#include <wildlife_graphics.hpp>

#include <util.h>
#include <diagnostics.h>
#include <boinc_api.h>
#include <graphics2.h>

#include <OpenGL/gl.h>
#include <OpenGL/glu.h>
#include <GLUT/glut.h>

#include <opencv2/opencv.hpp>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>

using namespace std;
using namespace cv;

//TEXTURE_DESC logo;
APP_INIT_DATA uc_aid;
UC_SHMEM* shmem = NULL;

int window_width, window_height;
int mouse_x, mouse_y;
bool mouse_down;
GLuint texture = 0;

// OpenCV
VideoCapture capture;

void drawVideo() {
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

    drawVideo();
    glFlush();
    glutSwapBuffers();
}

void idle() {
    Mat frame;
    capture >> frame;
    cvtColor(frame, frame, CV_BGR2RGB);
    gluBuild2DMipmaps(GL_TEXTURE_2D, GL_RGB, frame.cols, frame.rows, GL_RGB, GL_UNSIGNED_BYTE, frame.data);
    glutPostRedisplay();
}

GLuint LoadTexture() {
    unsigned char data[] = {255, 0, 0, 0, 255, 0, 0, 0, 255, 255, 255, 255 };

    glGenTextures(1, &texture);
    glBindTexture(GL_TEXTURE_2D, texture);
    glPixelStorei(GL_UNPACK_ALIGNMENT, 1);
    glTexEnvf(GL_TEXTURE_ENV, GL_TEXTURE_ENV_MODE, GL_MODULATE);

    glTexParameterf(GL_TEXTURE_2D, GL_TEXTURE_MIN_FILTER, GL_NEAREST);
    glTexParameterf(GL_TEXTURE_2D, GL_TEXTURE_MAG_FILTER, GL_NEAREST);

    glTexParameterf(GL_TEXTURE_2D, GL_TEXTURE_WRAP_S, GL_REPEAT);
    glTexParameterf(GL_TEXTURE_2D, GL_TEXTURE_WRAP_T, GL_REPEAT);

    glTexImage2D(GL_TEXTURE_2D, 0, GL_RGB, 2, 2, 0, GL_RGB, GL_UNSIGNED_BYTE, data);
    return texture;
}

void draw_logo() {
    //if (logo.present) {
        cerr << "Logo is present" << endl;
    //}
}

void app_graphics_init() {
    // Load logo from disk here and initialize the viewport
}

void app_graphics_render(int xs, int ys, double time_of_day) {
    if (shmem == NULL) {
        shmem = (UC_SHMEM*)boinc_graphics_get_shmem("wildlife_surf_collect");
    }
    if (shmem) {
        // Update stuff here.
        //shmem->countdown = 5;
    } else {
        // Check simple timeout function
        //boinc_close_window_and_quit("shmem not updated");
    }

    cout << "Running." << endl;
    display();

    if (shmem->status.suspended) {
        // Show suspended message
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

    // Init
    glutInit(&argc, argv);
    glutInitDisplayMode(GLUT_RGBA | GLUT_DEPTH | GLUT_DOUBLE);
    glutInitWindowSize(800, 600);
    glutCreateWindow("Wildlife@Home");
    glutDisplayFunc(display);
    glutReshapeFunc(app_graphics_resize);
    glutIdleFunc(idle);
    //texture = LoadTexture();
    glutMainLoop();

    boinc_parse_init_data_file();
    boinc_get_init_data(uc_aid);
    if (uc_aid.project_preferences) {
//        parse_project_prefs(uc_aid.project_preferences);
    }
    //cout << "Starting..." << endl;
    boinc_graphics_loop(argc, argv);
//    boinc_finish_diag();
}
