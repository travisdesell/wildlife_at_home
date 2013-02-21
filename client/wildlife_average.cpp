#include <cstdlib>
#include <cstring>
#include <climits>
#include <cmath>
#include <time.h>

#include <string>
#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <iomanip>

#include "stdint.h"

#ifdef USE_OPENGL

#ifdef __APPLE__
#  include <OpenGL/gl.h>
#  include <OpenGL/glu.h>
#  include <GLUT/glut.h>
#else
#  include <GL/gl.h>
#  include <GL/glu.h>
#  include <GL/glut.h>
#endif

#endif

//Boost includes
#include <boost/thread.hpp>
#include <boost/random.hpp>
#include <boost/generator_iterator.hpp>
//OpenCV Includes
#include <opencv/cv.h>
#include <opencv/highgui.h>


#ifdef _BOINC_APP_
#include "parse.h"
#include "util.h"
#include "gutil.h"
#include "boinc_gl.h"
#include "app_ipc.h"
#include "boinc_api.h"
#include "graphics2.h"
#include "diagnostics.h"
#endif


#define SLICE_TIME_S 180

#define PIXEL_THRESHOLD 10
#define BLOCK_THRESHOLD 75
#define BLOCK_WIDTH 10
#define BLOCK_HEIGHT 10

using boost::thread;

using boost::variate_generator;
using boost::mt19937;
using boost::exponential_distribution;
using boost::gamma_distribution;
using boost::uniform_real;

using namespace std;

int startFrame;
int currentFrameNum;

int fps;
int frameCount;

int key = 0;

CvCapture *capture;

IplImage *currentFrame;
IplImage *pixelFrame;
IplImage *blockFrame;
IplImage *convolveFrame;        
IplImage *lastFrame;
IplImage * avgFrame;
IplImage *globalAverageFrame; 

float *** avgImagePlaceholder;
float *** avgImageFinal;

variate_generator<mt19937, uniform_real<> > u_rand( mt19937(time(0)), uniform_real<>(0.0, 1.0));

int frameWidth, frameHeight;
char *gl_pixels;

int gl_width, gl_height;

bool analysis_finished = false;

int frame_range;

vector<IplImage *> * future_images;

//from: http://en.wikipedia.org/wiki/YUV#Y.27UV420p_.28and_Y.27V12_or_YV12.29_to_RGB888_conversion
void YprimeUV444toRGB888(char yp, char u, char v, char &r, char &g, char &b) {
char cr = u - 128;
	char cb = v - 128;

	r = yp + cr + (cr >> 2) + (cr >> 3) + (cr >> 5);
	g = yp - ((cb >> 2) + (cb >> 4) + (cb >> 5)) - ((cr >> 1) + (cr >> 3) + (cr >> 4) + (cr >> 5));
	b = yp + cb + (cb >> 1) + (cb >> 2) + (cb >> 6);
}

void YprimeUV444toRGB888_2(char yp, char u, char v, char &r, char &g, char &b) {
	char c = yp - 16;
	char d = u - 128;
	char e = v - 128;
	r = (298 * c + 409 * e + 128) >> 8;
	g = (298 * c - 100 * d - 208 * e + 128) >> 8;
	b = (298 * c + 516 * d + 128) >> 8;
}
void YUVtoRGB(char y, char u, char v, char &r, char &g, char &b) {
	r = y + (v / 0.877);
	g = -1.7036 * y - 3.462 * u - 1.942 * v;
	b = y + (u / 0.492);
}

void clear_gl_pixels() {
    for (int i = 0; i < (gl_width * gl_height * 3); i++) gl_pixels[i] = 0;
}

void draw_gl_pixels(int gl_start_w, int gl_start_h, int image_width, int image_height, char* cl_pixels) {
	for (int h = 0; h < image_height; h++) {
		for (int w = 0; w < image_width; w++) {
			int cl_pos = (h * image_width + w) * 3;

			char b = cl_pixels[cl_pos];
			char g = cl_pixels[cl_pos + 1];
			char r = cl_pixels[cl_pos + 2];

			int gl_w = gl_start_w + w;
			int gl_h = gl_start_h + (image_height - h - 1);

			int gl_pos = ((gl_h * gl_width) + gl_w) * 3;

			gl_pixels[gl_pos]       = b;
			gl_pixels[gl_pos + 1]   = g;
			gl_pixels[gl_pos + 2]   = r;
		}
	}
}

void ImageAverage() {
	for(int y = 0; y < frameHeight; y++) {
		for(int x = 0; x < frameWidth; x++) {
			int pixelPos = (y * frameWidth + x) * 3;
			if(currentFrameNum == 0) { 
				avgImagePlaceholder[y][x][0] = (unsigned char)currentFrame->imageDataOrigin[pixelPos];
				avgImagePlaceholder[y][x][1] = (unsigned char)currentFrame->imageDataOrigin[pixelPos + 1];
				avgImagePlaceholder[y][x][2] = (unsigned char)currentFrame->imageDataOrigin[pixelPos + 2];
			}
			else {
				float i1 = currentFrameNum;
				float i2 = currentFrameNum + 1;
				float a1 = avgImagePlaceholder[y][x][0];
				float a2 = avgImagePlaceholder[y][x][1];
				float a3 = avgImagePlaceholder[y][x][2];
				float j1 = 1;
				float j2 = currentFrameNum + 1;
				float c1 = (unsigned char)currentFrame->imageDataOrigin[pixelPos    ];
				float c2 = (unsigned char)currentFrame->imageDataOrigin[pixelPos + 1];
				float c3 = (unsigned char)currentFrame->imageDataOrigin[pixelPos + 2];

				avgImagePlaceholder[y][x][0] = ( ( (i1 / i2) * a1) + ( (j1 / j2) * c1) );
				avgImagePlaceholder[y][x][1] = ( ( (i1 / i2) * a2) + ( (j1 / j2) * c2) );
				avgImagePlaceholder[y][x][2] = ( ( (i1 / i2) * a3) + ( (j1 / j2) * c3) );
			}

			avgFrame->imageDataOrigin[pixelPos    ] = (unsigned char)avgImagePlaceholder[y][x][0];
			avgFrame->imageDataOrigin[pixelPos + 1] = (unsigned char)avgImagePlaceholder[y][x][1];
			avgFrame->imageDataOrigin[pixelPos + 2] = (unsigned char)avgImagePlaceholder[y][x][2];
		}
	}
}

void difference(IplImage * avg, IplImage * toFrame, IplImage * fromFrame) {
	for(int y = 0; y < frameHeight; y++) {
		for(int x = 0; x < frameWidth; x++) {
			int pixelPos = (y * frameWidth + x) * 3;

			toFrame->imageDataOrigin[pixelPos] = (unsigned char)abs((unsigned char)avg->imageDataOrigin[pixelPos] - (unsigned char)fromFrame->imageDataOrigin[pixelPos]);
			toFrame->imageDataOrigin[pixelPos + 1] = (unsigned char)abs((unsigned char)avg->imageDataOrigin[pixelPos + 1] - (unsigned char)fromFrame->imageDataOrigin[pixelPos + 1]);
			toFrame->imageDataOrigin[pixelPos + 2] = (unsigned char)abs((unsigned char)avg->imageDataOrigin[pixelPos + 2] - (unsigned char)fromFrame->imageDataOrigin[pixelPos + 2]);
		}
	}
}

void local_average_advance() {
	IplImage * last = future_images->at(0);
	future_images->erase(future_images->begin());
	future_images->push_back(cvCloneImage(currentFrame));
	
	float num_frames_scanned = frame_range * 2 + 1;
	for(int y = 0; y < frameHeight; y++) {
		for(int x = 0; x < frameWidth; x++) {
			int pixelPos = (y * frameWidth + x) * 3;
			
			avgImagePlaceholder[y][x][0] -= (unsigned char)last->imageDataOrigin[pixelPos];
			avgImagePlaceholder[y][x][1] -= (unsigned char)last->imageDataOrigin[pixelPos + 1];
			avgImagePlaceholder[y][x][2] -= (unsigned char)last->imageDataOrigin[pixelPos + 2];
		
			avgImagePlaceholder[y][x][0] += (unsigned char)currentFrame->imageDataOrigin[pixelPos];
			avgImagePlaceholder[y][x][1] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 1];
			avgImagePlaceholder[y][x][2] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 2];
		
			avgFrame->imageDataOrigin[pixelPos    ] = (unsigned char)(avgImagePlaceholder[y][x][0] / num_frames_scanned);
			avgFrame->imageDataOrigin[pixelPos + 1] = (unsigned char)(avgImagePlaceholder[y][x][1] / num_frames_scanned);
			avgFrame->imageDataOrigin[pixelPos + 2] = (unsigned char)(avgImagePlaceholder[y][x][2] / num_frames_scanned);
		}
	}
	
	cvReleaseImage(&last);
}

void handle_keyboard(unsigned char key, int x, int y) {
	int it = 0;
	while(it++ < 1000) {
		currentFrame = cvQueryFrame(capture);
		local_average_advance();
	}
}

bool resized = true;

boost::mutex guard_render;

void app_graphics_render(int xs, int ys, double time_of_day) {
    boost::mutex::scoped_lock lock(guard_render);

//    cout << "app_graphics_render -- xs: " << xs << ", ys: " << ys << endl;

	glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);
	currentFrame = cvQueryFrame(capture);

    if(!currentFrame) exit(0);

	difference(avgFrame, globalAverageFrame, future_images->at(frame_range));

	draw_gl_pixels(0, 0, frameWidth, frameHeight, future_images->at(frame_range)->imageDataOrigin);
	draw_gl_pixels(frameWidth, 0, frameWidth, frameHeight, avgFrame->imageDataOrigin);
	draw_gl_pixels(0, frameHeight, frameWidth, frameHeight, globalAverageFrame->imageDataOrigin);

	local_average_advance();	

	currentFrameNum++;


    //Setup a 2D projection
    glMatrixMode (GL_PROJECTION);
    glLoadIdentity();
    glOrtho (0, gl_width, gl_height, 0, 0, 1);
    glDisable(GL_DEPTH_TEST);
    glMatrixMode (GL_MODELVIEW);
    glLoadIdentity();

    glDrawPixels(gl_width, gl_height, GL_BGR, GL_UNSIGNED_BYTE, gl_pixels);

    //Displacement trick for exact pixelization
//    glTranslatef(0.375, 0.375, 0)

	glFlush();

	glutSwapBuffers();

	glutPostRedisplay();
}

void app_graphics_resize(int w, int h) {
    boost::mutex::scoped_lock lock(guard_render);

    cout << "app_graphics_resize -- w: " << w << ", h: " << h << endl;
//    gl_width = w;
//    gl_height = h;

    resized = true;
//    glViewport(0, 0, gl_width, gl_height);
}

// mouse drag w/ left button rotates 3D objects;
// mouse draw w/ right button zooms 3D objects
//
void boinc_app_mouse_move(int x, int y, int left, int middle, int right) {}

void boinc_app_mouse_button(int x, int y, int which, int is_down) {}

void boinc_app_key_press(int, int){}

void boinc_app_key_release(int, int){}

void app_graphics_init() {
    glutInitDisplayMode(GLUT_RGB | GLUT_DOUBLE);
    glClearColor(0.0f, 0.0f, 0.0f, 0.0f);
}


int main(int argc, char** argv)
{
//	assert(argc == 4);

    boinc_init_graphics_diagnostics(BOINC_DIAG_DEFAULTS);

	capture = cvCaptureFromAVI(argv[1]);

	int startAvgFrame = atoi(argv[2]);
	
	frame_range = atoi(argv[3]);

	if(!capture) 
		return 1;

	fps 				    = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);
	frameCount 			    = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);

	frameWidth 			        = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_WIDTH);
	frameHeight 		        = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_HEIGHT);

	gl_width = frameWidth * 2;
	gl_height = frameHeight * 2;

    cout << "starting gl_width: " << gl_width << ", gl_height: " << gl_height << endl;

	gl_pixels = (char*)malloc(gl_width * gl_height * 3 * sizeof(char));
    clear_gl_pixels();

//	glutInit(&argc, argv);
//    glutInitDisplayMode(GLUT_RGB | GLUT_DOUBLE);
//    glutInitWindowSize(gl_width, gl_height);
//    glutCreateWindow(argv[1]);      //the name of the window is the name of the video
//    glutDisplayFunc(display);
//	glutKeyboardFunc(handle_keyboard);
//    glClearColor(0.0, 0.0, 0.0, 1.0);

	currentFrame = cvQueryFrame(capture);
	startFrame = 0;
	
	for(int i = 0; i < startFrame; i++) {
		currentFrame = cvQueryFrame(capture);
	}

	pixelFrame = 			cvCloneImage(currentFrame);
	blockFrame = 			cvCloneImage(currentFrame);
	convolveFrame = 		cvCloneImage(currentFrame);        
	lastFrame = 			cvCloneImage(currentFrame);
	avgFrame = 				cvCloneImage(currentFrame);
	globalAverageFrame = 	cvCloneImage(currentFrame);

	avgImagePlaceholder = new float ** [frameHeight];
	avgImageFinal = new float ** [frameHeight];
	for(int i = 0; i < frameHeight; i++) {
		avgImagePlaceholder[i] = new float * [frameWidth];
		avgImageFinal[i] = new float * [frameWidth];
		for(int j = 0; j < frameWidth; j++) {
			avgImagePlaceholder[i][j] = new float[3];
			avgImageFinal[i][j] = new float[3];
			
			int pixelPos = (i * frameWidth + j) * 3;
			avgFrame->imageDataOrigin[pixelPos] = 0;
			avgFrame->imageDataOrigin[pixelPos + 1] = 0;
			avgFrame->imageDataOrigin[pixelPos + 2] = 0;

			for(int k = 0; k < 3; k++) {
				avgImagePlaceholder[i][j][k] = 0;
				avgImageFinal[i][j][k] = 0;
			}
		}
	}

	for(int i = 0; i < startAvgFrame; i++) {
		currentFrame = cvQueryFrame(capture);
	}
	
	int its = 0;
	if(frame_range != -1) {
		future_images = new vector<IplImage *>();
		
		//You have to start (future_images) frames into the video, therefore
		//Therefore you must first sum all the items from 0 to frame_num
		for(int i = 0; i < frame_range * 2 + 1; i++) {
			future_images->push_back(cvCloneImage(currentFrame));
			cout << " performed " << ++its << " iterations" << endl;
			for(int y = 0; y < frameHeight; y++) {
				for(int x = 0; x < frameWidth; x++) {
					int pixelPos = (y * frameWidth + x) * 3;
					
					avgImagePlaceholder[y][x][0] += (unsigned char)currentFrame->imageDataOrigin[pixelPos];
					avgImagePlaceholder[y][x][1] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 1];
					avgImagePlaceholder[y][x][2] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 2];
				}
			}
			currentFrame = cvQueryFrame(capture);
		}
	}
	
	currentFrameNum = frame_range;	

    cout << "entering boinc graphics loop" << endl;

    boinc_graphics_loop(argc, argv);

    cout << "after boinc graphics loop" << endl;

	cvReleaseCapture(&capture);

	analysis_finished = true;
	exit(0);
}

