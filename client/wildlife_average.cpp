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

#ifdef _BOINC_APP_
#ifdef _WIN32
#include "boinc_win.h"
#include "str_util.h"
#endif

#include "diagnostics.h"
#include "util.h"
#include "filesys.h"
#include "boinc_api.h"
#include "mfile.h"
#endif

#ifdef USE_OPENGL
#ifdef __APPLE__
//#include <OpenGL/gl.h>
//#include <OpenGL/glu.h>
//#include <GLUT/glut.h>
#else
//#include <GL/gl.h>
//#include <GL/glu.h>
//#include <GL/glut.h>
#endif
#endif

//#include <boost/thread.hpp>
//#include <boost/random.hpp>
//#include <boost/generator_iterator.hpp>

#include <opencv/cv.h>
#include <opencv/highgui.h>

//using boost::thread;
//using boost::variate_generator;
//using boost::mt19937;
//using boost::exponential_distribution;
//using boost::gamma_distribution;
//using boost::uniform_real;

using namespace std;

int averageRange;
int framesInThreeMinutes;

int currentFrameNumber;

int key;

CvCapture *capture;
int fps, frameCount, frameWidth, frameHeight;

IplImage *currentFrame;
IplImage *averageFrame;
IplImage *differenceFrame; 

float ***avgImagePlaceholder;

char *gl_pixels;

int gl_width, gl_height;

bool analysis_finished = false;

vector<IplImage *> * future_images;

ofstream outfile;

int * responseStrengths;
char * responseGraph;

int * intermediateResponses;

vector<float> * probabilities;
int numberOfSlices;

float numberOfFramesInAverage;

string checkpoint_filename;
string checkpointed_video_file_name;
string video_file_name;

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

void write_checkpoint() {
#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if (retval) {
        cerr << "Couldn't resolve file name..." << endl;
        return;
    }

    ofstream checkpoint_file(resolved_path.c_str());
#else
    ofstream checkpoint_file(checkpoint_filename.c_str());
#endif

    if (!checkpoint_file.is_open()) {
        cerr << "Checkpoint file not open..." << endl;
        return;
    }

    checkpoint_file << "VIDEO_FILE_NAME: " << video_file_name << endl;
    checkpoint_file << "PROBABILITIES: " << probabilities->size() << endl;

    for (uint32_t i = 0; i < probabilities->size(); i++) {
        checkpoint_file << probabilities->at(i) << endl;
    }
    checkpoint_file << endl;

    checkpoint_file.close();
}

bool read_checkpoint() {
#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if (retval) {
        return false;
    }

    ifstream checkpoint_file(resolved_path.c_str());
#else
    ifstream checkpoint_file(checkpoint_filename.c_str());
#endif
    if (!checkpoint_file.is_open()) return false;

    string s;
    checkpoint_file >> s >> checkpointed_video_file_name;
    if (s.compare("VIDEO_FILE_NAME:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'VIDEO_FILE_NAME'" << endl;

#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    int intervals;
    checkpoint_file >> s >> intervals;
    if (s.compare("PROBABILITIES:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'INTERVALS'" << endl;

#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    float current;
    for(int i = 0; i < intervals; i++) {
        checkpoint_file >> current;
        probabilities->push_back(current);
        if (!checkpoint_file.good()) {
            cerr << "ERROR: malformed checkpoint! not enough probabilities present" << endl;

#ifdef _BOINC_APP_
            boinc_finish(1);
#endif
            exit(1);
        }
    }

    return true;
}

    int absToRel(int absVal, int totalFrames, int avgRng) {
        if(absVal < avgRng)
            return absVal;
        else if(absVal > totalFrames - avgRng) 
            return avgRng + (absVal - (totalFrames - avgRng));
        else
            return avgRng;
    }


void difference(IplImage * average, IplImage * destination, IplImage * current) {
    for(int y = 0; y < frameHeight; y++) {
        for(int x = 0; x < frameWidth; x++) {
            int pixelPos = (y * frameWidth + x) * 3;

            //simply calculate the difference
            destination->imageDataOrigin[pixelPos    ] = (unsigned char)abs((unsigned char)average->imageDataOrigin[pixelPos    ] - (unsigned char)current->imageDataOrigin[pixelPos    ]);
            destination->imageDataOrigin[pixelPos + 1] = (unsigned char)abs((unsigned char)average->imageDataOrigin[pixelPos + 1] - (unsigned char)current->imageDataOrigin[pixelPos + 1]);
            destination->imageDataOrigin[pixelPos + 2] = (unsigned char)abs((unsigned char)average->imageDataOrigin[pixelPos + 2] - (unsigned char)current->imageDataOrigin[pixelPos + 2]);

            //calculate a scalar value for the difference, which is that pixel's response value
            unsigned int xStrength = destination->imageDataOrigin[pixelPos    ];
            unsigned int yStrength = destination->imageDataOrigin[pixelPos + 1];
            unsigned int zStrength = destination->imageDataOrigin[pixelPos + 2];

            unsigned int responseStrength = (xStrength + yStrength + zStrength) / 3;

            if(responseStrength < 0) {
                responseStrength = 0;
            }

            if(responseStrength > 255) {
                responseStrength = 255;
            }

            responseStrengths[responseStrength]++;
        }
    }
}

void local_average_advance() {
    if(!currentFrame) return;

    IplImage * last = future_images->at(0);

    future_images->erase(future_images->begin());
    future_images->push_back(cvCloneImage(currentFrame));

    for(int y = 0; y < frameHeight; y++) {
        for(int x = 0; x < frameWidth; x++) {
            int pixelPos = (y * frameWidth + x) * 3;

            //Simply ignore the area the clock is located at by simply using the current frame's values there
            if(x > (frameWidth * .8) && y > (frameHeight * .8)) {
                averageFrame->imageDataOrigin[pixelPos    ] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->imageDataOrigin[pixelPos    ];
                averageFrame->imageDataOrigin[pixelPos + 1] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->imageDataOrigin[pixelPos + 1];
                averageFrame->imageDataOrigin[pixelPos + 2] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->imageDataOrigin[pixelPos + 2];
                continue;
            } 

            //get rid of the oldest frame in the average
            avgImagePlaceholder[y][x][0] -= (unsigned char)last->imageDataOrigin[pixelPos    ];
            avgImagePlaceholder[y][x][1] -= (unsigned char)last->imageDataOrigin[pixelPos + 1];
            avgImagePlaceholder[y][x][2] -= (unsigned char)last->imageDataOrigin[pixelPos + 2];

            //add in the newest frame to the average
            avgImagePlaceholder[y][x][0] += (unsigned char)currentFrame->imageDataOrigin[pixelPos    ];
            avgImagePlaceholder[y][x][1] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 1];
            avgImagePlaceholder[y][x][2] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 2];

            //cast the average stored in floats into the image's char array
            averageFrame->imageDataOrigin[pixelPos    ] = (unsigned char)(avgImagePlaceholder[y][x][0] / numberOfFramesInAverage);
            averageFrame->imageDataOrigin[pixelPos + 1] = (unsigned char)(avgImagePlaceholder[y][x][1] / numberOfFramesInAverage);
            averageFrame->imageDataOrigin[pixelPos + 2] = (unsigned char)(avgImagePlaceholder[y][x][2] / numberOfFramesInAverage);
        }
    }

    cvReleaseImage(&last);
}

void display() {
#ifdef OPENGL
    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);
#endif

#ifndef _BOINC_APP_
    if (currentFrameNumber % 100 == 0) {
        cout << "Frame: " << currentFrameNumber << endl;
    }
#endif

    //end of video reached
    if(currentFrameNumber == frameCount) {
        //account for last fragment
        float prob = 0;

        for(int i = 0; i < currentFrameNumber % framesInThreeMinutes; i++) {
            float inter = (float)((float)intermediateResponses[i] / (float)1000000.0f);
            prob += inter;
        }

        probabilities->push_back(prob);

        //output
        cerr << "<video_name>" << video_file_name << "s</video_name>" << endl;
        cerr << "<slice_time>" << 180 << "</slice_time>" << endl;
        cerr << "<slice_probabilities>" << endl;

        for (uint32_t i = 0; i < probabilities->size(); i++) {
            cerr << fixed << setprecision(2) << probabilities->at(i) << endl;
//            fprintf(stderr, "%1.5f\n", probabilities->at(i));
        }

        cerr << "</slice_probabilities>" << endl;

#ifdef _BOINC_APP_
        boinc_finish(0);
#else
        exit(0);
#endif
    }

    responseStrengths = new int[256];
    for(int i = 0; i < 256; i++) {
        responseStrengths[i] = 0;
    }

    //beginning, end, or middle of video are handled slightly differently
    difference(averageFrame, differenceFrame, future_images->at(absToRel(currentFrameNumber, frameCount, averageRange)));

    responseGraph = new char[frameWidth * frameHeight * 3];
    for(int i = 0; i < frameWidth * frameHeight * 3; i++) {
        responseGraph[i] = 255;
    }

    int interestingFactor = 0;

    for(int i = 1; i < 257; i++) {
        if (i > 15) {
            interestingFactor += i * responseStrengths[i - 1];
        }

        for(int x = i; x < i + 1; x++) {
            for(int y = 0; y < responseStrengths[i - 1]; y++) {
                int pixelPos = ((frameHeight - y) * frameWidth + x) * 3;

                if(pixelPos > frameWidth * frameHeight * 3 - 3 || pixelPos < 0) {
                    continue;
                }

                responseGraph[pixelPos    ] = 0;
                responseGraph[pixelPos + 1] = 0;
                responseGraph[pixelPos + 2] = 0;
            }
        }
    }

    //store am intermediate response for calculating the 3 minute response
    intermediateResponses[currentFrameNumber % framesInThreeMinutes] = interestingFactor;

    //Don't advance the average if we are near the beginning or end of the video;
    if(averageRange == absToRel(currentFrameNumber, frameCount, averageRange)) {
        currentFrame = cvQueryFrame(capture);
        local_average_advance();    
    }

    //Calculate a three minute response
    if((currentFrameNumber + 1) % framesInThreeMinutes == 0) {
        float prob = 0;

        for(int i = 0; i < framesInThreeMinutes; i++) {
            float inter = (float)((float)intermediateResponses[i] / (float)1000000.0f);
            prob += inter;
        }

        probabilities->push_back(prob);
    }

#ifdef _BOINC_APP_
    boinc_fraction_done((double)((double)currentFrameNumber / (double)frameCount));

    if(boinc_time_to_checkpoint() || key == 's') {
        cerr << "boinc_time_to_checkpoint encountered, checkpointing" << endl;
        write_checkpoint();
        boinc_checkpoint_completed();
    }
#endif

    /*draw_gl_pixels(0,          0,           frameWidth, frameHeight, future_images->at(averageRange)->imageDataOrigin);
      draw_gl_pixels(frameWidth, 0,           frameWidth, frameHeight, averageFrame->imageDataOrigin);
      draw_gl_pixels(0,          frameHeight, frameWidth, frameHeight, differenceFrame->imageDataOrigin);
      draw_gl_pixels(frameWidth, frameHeight, frameWidth, frameHeight, responseGraph);*/

    delete responseGraph;
    delete responseStrengths;

    //  cout << "CurrentFrameNumber: " << currentFrameNumber << endl;
    //  boost::this_thread::sleep( boost::posix_time::seconds(1) );

    currentFrameNumber++;

#ifdef OPENGL
    //glDrawPixels(gl_width, gl_height, GL_BGR, GL_UNSIGNED_BYTE, gl_pixels);
    //glFlush();
    //glutSwapBuffers();
    glutPostRedisplay();
#endif
}

#ifdef OPENGL
void doGlut(int argc, char ** argv) {
    glutInit(&argc, argv);
    glutInitDisplayMode(GLUT_RGB | GLUT_DOUBLE);
    glutInitWindowSize(gl_width, gl_height);
    glutCreateWindow(video_file_name.c_str());
    glutDisplayFunc(display);
    glClearColor(0.0, 0.0, 0.0, 1.0);
}
#endif

int main(int argc, char** argv) {
    video_file_name = string(argv[1]);

#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(video_file_name.c_str(), resolved_path);
    if (retval) {
        cerr << "Error, could not open file: '" << video_file_name << "'" << endl;
        cerr << "Resolved to: '" << resolved_path << "'" << endl;
        return false;
    }

    video_file_name = resolved_path;
#endif

    capture = cvCaptureFromAVI(video_file_name.c_str());

    if (!capture) {
        cerr << "ERROR: could get capture from input file: '" << video_file_name << "'" << endl;
        return 1;
    }

    key = 0;

    fps = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);
    frameCount = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);
    frameWidth = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_WIDTH);
    frameHeight = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_HEIGHT);

    gl_width = frameWidth * 2;
    gl_height = frameHeight * 2;

    gl_pixels = (char*)malloc(gl_width * gl_height * 3 * sizeof(char));

    for (int i = 0; i < gl_width * gl_height * 3; i++) {
        gl_pixels[i] = rand();
    }

#ifdef OPENGL
    doGlut(argc, argv);
#endif

    currentFrame = cvQueryFrame(capture);

    //just to allocate the pointers some room easily.
    averageFrame = cvCloneImage(currentFrame);
    differenceFrame = cvCloneImage(currentFrame);

    averageRange = fps * 5;
    framesInThreeMinutes = fps * 180;
    numberOfFramesInAverage = averageRange * 2 + 1;

    intermediateResponses = new int[framesInThreeMinutes];

    numberOfSlices = ceil((double)frameCount / (double)framesInThreeMinutes);

    probabilities = new vector<float>();

#ifdef _BOINC_APP_  
    boinc_init();
#endif

    cerr << "Video File Name: " << video_file_name.c_str() << endl;
    cerr << "Frames Per Second: " << fps << endl;
    cerr << "Frame Count: " << frameCount << endl;
    cerr << "Frame Width: " << frameWidth << endl;
    cerr << "Frame Height: " << frameHeight << endl; 
    cerr << "Average Range: " << averageRange << endl;
    cerr << "Number of Frames in Three Minutes: " << framesInThreeMinutes << endl;
    cerr << "Number of Slices: " << numberOfSlices << endl;
    cerr << "Number of Frames in Average: " << numberOfFramesInAverage << endl;

    checkpoint_filename = "checkpoint.txt";

    if(read_checkpoint()) {
        if(checkpointed_video_file_name.compare(video_file_name) != 0) {
            cerr << "Checkpointed video filename was not the same as given video filename... Restarting" << endl;
        } else {
            cerr << "Continuing from checkpoint..." << endl;
        }
    } else {
        cerr << "Unsuccessful checkpoint read" << endl << "Starting from beginning of video" << endl;
    }

    avgImagePlaceholder = new float ** [frameHeight];

    for(int i = 0; i < frameHeight; i++) {
        avgImagePlaceholder[i] = new float * [frameWidth];

        for(int j = 0; j < frameWidth; j++) {
            avgImagePlaceholder[i][j] = new float[3];

            int pixelPos = (i * frameWidth + j) * 3;

            averageFrame->imageDataOrigin[pixelPos] = 0;
            averageFrame->imageDataOrigin[pixelPos + 1] = 0;
            averageFrame->imageDataOrigin[pixelPos + 2] = 0;

            for(int k = 0; k < 3; k++) {
                avgImagePlaceholder[i][j][k] = 0;
            }
        }
    }

    future_images = new vector<IplImage *>();

    //Start either at frame 0 or at the frame next in line after checkpointing
    currentFrameNumber = probabilities->size() * framesInThreeMinutes;

    cerr << "Starting at Frame: " << currentFrameNumber << endl;

    //Advance the video to the appropriate spot, which is averageRange frames behind the current Frame
    for(int i = 0; i < currentFrameNumber - averageRange; i++) {    
        currentFrame = cvQueryFrame(capture);
    }

    //populate the Images used in the average calculation   
    for(int i = 0; i < averageRange * 2 + 1; i++) {
        future_images->push_back(cvCloneImage(currentFrame));

        for(int y = 0; y < frameHeight; y++) {
            for(int x = 0; x < frameWidth; x++) {
                int pixelPos = (y * frameWidth + x) * 3;

                avgImagePlaceholder[y][x][0] += (unsigned char)currentFrame->imageDataOrigin[pixelPos];
                avgImagePlaceholder[y][x][1] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 1];
                avgImagePlaceholder[y][x][2] += (unsigned char)currentFrame->imageDataOrigin[pixelPos + 2];

                //The last time through this loop, calculate the average for real
                if(i == averageRange * 2) {
                    if(x > (frameWidth * .8) && y > (frameHeight * .8)) {
                        averageFrame->imageDataOrigin[pixelPos    ] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->imageDataOrigin[pixelPos    ];
                        averageFrame->imageDataOrigin[pixelPos + 1] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->imageDataOrigin[pixelPos + 1];
                        averageFrame->imageDataOrigin[pixelPos + 2] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->imageDataOrigin[pixelPos + 2];
                        continue;
                    }

                    averageFrame->imageDataOrigin[pixelPos    ] = (unsigned char)(avgImagePlaceholder[y][x][0] / numberOfFramesInAverage);
                    averageFrame->imageDataOrigin[pixelPos + 1] = (unsigned char)(avgImagePlaceholder[y][x][1] / numberOfFramesInAverage);
                    averageFrame->imageDataOrigin[pixelPos + 2] = (unsigned char)(avgImagePlaceholder[y][x][2] / numberOfFramesInAverage);
                }
            }
        }

        //Handle beginning of video by simply using the first frame multiple times
        if((currentFrameNumber - averageRange) + i > 0) {
            currentFrame = cvQueryFrame(capture);
        }
    }

#ifdef OPENGL 
    glutMainLoop();
#else
    while (true) {
        display();
    }
#endif

    //cvReleaseCapture(&capture);

    //analysis_finished = true;
    //exit(0);
}
