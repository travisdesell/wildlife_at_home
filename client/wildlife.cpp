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

/**
 * Includes required for BOINC
 */
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

//OpenCV Includes
#include <opencv/cv.h>
#include <opencv/highgui.h>

#define SLICE_TIME_S 180

#define PIXEL_THRESHOLD 10
#define BLOCK_THRESHOLD 20
#define BLOCK_WIDTH 5
#define BLOCK_HEIGHT 5

using boost::thread;

using namespace std;

void write_checkpoint(string checkpoint_filename, string video_filename, vector<float> &slice_probabilities) {
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

    checkpoint_file << "VIDEO_FILE_NAME: " << video_filename << endl;
    checkpoint_file << "INTERVALS: " << slice_probabilities.size() << endl;
    for(int i = 0; i < slice_probabilities.size(); i++) {
        checkpoint_file << slice_probabilities[i] << endl;
    }
    checkpoint_file << endl;

    checkpoint_file.close();
}

bool read_checkpoint(string checkpoint_filename, string &video_filename, vector<float> &slice_probabilities ) {
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
    checkpoint_file >> s >> video_filename;
    if (s.compare("VIDEO_FILE_NAME:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'VIDEO_FILE_NAME'" << endl;

#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    int intervals;
    checkpoint_file >> s >> intervals;
    if (s.compare("INTERVALS:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'INTERVALS'" << endl;

#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    float current;
    for(int i = 0; i < intervals; i++) {
        checkpoint_file >> current;
        slice_probabilities.push_back(current);
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

int frameWidth, frameHeight;

#ifdef USE_OPENGL
char *gl_pixels;

int gl_width, gl_height;

bool analysis_finished = false;

void display() {
    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

    //http://msdn2.microsoft.com/en-us/library/ms537062.aspx
    //glDrawPixels writes a block of gl_pixels to the framebuffer.

    glDrawPixels(gl_width, gl_height, GL_RGB, GL_UNSIGNED_BYTE, gl_pixels);

    glFlush();
    glutSwapBuffers();

    glutPostRedisplay();
}

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

            gl_pixels[gl_pos]       = r;
            gl_pixels[gl_pos + 1]   = g;
            gl_pixels[gl_pos + 2]   = b;
        }
    }
}

#endif

int main(int argc, char** argv)
{
    //Variable Declarations
    std::string resolved_outputPixel;
    std::string resolved_outputBlock;
    std::string resolved_checkpoint;

    int currentFrameNum;

    int numFoundPixel;
    int numFoundBlock;

    int key = 0;

    int ** blockHolder;

	//Assure we have at least an argument to attempt to open
    assert(argc == 2);

#ifdef _BOINC_APP_	
    boinc_init();
#endif

    vector<float> slice_probabilities;

    string checkpoint_filename = "checkpoint.txt";

    string checkpointed_video_filename;

    bool successful_checkpoint_read = read_checkpoint(	checkpoint_filename, 
            checkpointed_video_filename, 
            slice_probabilities);

    if(successful_checkpoint_read) {
        if(checkpointed_video_filename.compare(argv[1]) != 0) {
            cout << "Checkpointed video filename was not the same as given video filename... Restarting" << endl;
        } else {
            cout << "Continuing from checkpoint..." << endl;
        }
    } else {
        cout << "Unsuccessful checkpoint read" << endl << "Starting from beginning of video" << endl;
    }

    //Get the video
    CvCapture *capture = cvCaptureFromAVI(argv[1]);
    if(!capture) return 1;

    //Get some video properties
    int fps 				    = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);
    int frameCount 			    = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);

    //frameWidth and frameHight are defined globally
    frameWidth 			        = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_WIDTH);
    frameHeight 		        = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_HEIGHT);

    gl_width = frameWidth * 2;
    gl_height = frameHeight * 2;
//    gl_width = ((frameWidth * frameHeight) * 2.0) / frameHeight;
//    gl_height = ((frameWidth * frameHeight) * 2.0) / frameWidth;
//    gl_width = frameWidth * 3.0 / 2.0;
//    gl_height = frameHeight * 3.0 / 2.0;

    int frameBlockWidth 	    = ceil((double)frameWidth / (double)BLOCK_WIDTH);
    int frameBlockHeight 	    = ceil((double)frameHeight / (double)BLOCK_HEIGHT);

    int numBlocks			    = frameBlockHeight * frameBlockWidth;
    int framesInThreeMinutes    = fps * SLICE_TIME_S;

    float *blockFractionArray = new float[framesInThreeMinutes];

    for(int i = 0; i < slice_probabilities.size(); i++) {
        cout << "prob " << i << "is: " << slice_probabilities[i] << endl;
    }

    blockHolder = new int*[frameBlockHeight];
    for(int i = 0; i < frameBlockHeight; i++) {
        blockHolder[i] = new int[frameBlockWidth];
        for(int j = 0; j < frameBlockWidth; j++) {
            blockHolder[i][j] = 0;
        }
    }

#ifdef USE_OPENGL
    gl_pixels = (char*)malloc(gl_width * gl_height * 3 * sizeof(char));
    for (int i = 0; i < gl_width * gl_height * 3; i++) {
        gl_pixels[i] = 0;
    }

    glutInit(&argc, argv);

    glutInitDisplayMode(GLUT_RGB | GLUT_DOUBLE | GLUT_DEPTH);
    glutInitWindowSize(gl_width, gl_height);

    glutCreateWindow(argv[1]);      //the name of the window is the name of the video

    glutDisplayFunc(display);

    glEnable(GL_DEPTH_TEST);
    glClearColor(0.0, 0.0, 0.0, 1.0);

    boost::thread opengl_thread( glutMainLoop );
#endif

    //Create the windows we see the output in
//    cvNamedWindow("Video", 0);
//    cvNamedWindow("Video Pixels", 0);
//    cvNamedWindow("Video Blocks", 0);

    //This is what should happen, but does not work currently
    //cvSetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES, startFrame); 

    //Instead...
    IplImage *currentFrame = cvQueryFrame(capture);
    int startFrame = slice_probabilities.size() * framesInThreeMinutes;
    for(int i = 0; i < startFrame; i++) {
        currentFrame = cvQueryFrame(capture);
    }

    IplImage *pixelFrame = cvCloneImage(currentFrame);
    IplImage *blockFrame = cvCloneImage(currentFrame);        
    IplImage *lastFrame = cvCloneImage(currentFrame);

    currentFrameNum = startFrame;	
    while(true) //quit when q is pressed
    {

        currentFrame = cvQueryFrame(capture);

        if(!currentFrame) break; //quit when no frames remain

        numFoundPixel = 0;

        for(int h = 0; h < frameHeight; h++) {
            for(int w = 0; w < frameWidth; w++) {
                int pixelPos = (h * frameWidth + w) * 3;
                if(abs(currentFrame->imageDataOrigin[pixelPos] - lastFrame->imageDataOrigin[pixelPos]) > PIXEL_THRESHOLD && abs(currentFrame->imageDataOrigin[pixelPos + 1] - lastFrame->imageDataOrigin[pixelPos+ 1]) > PIXEL_THRESHOLD && abs(currentFrame->imageDataOrigin[pixelPos + 2] - lastFrame->imageDataOrigin[pixelPos + 2]) > PIXEL_THRESHOLD) {
                    pixelFrame->imageDataOrigin[pixelPos] = 0;
                    pixelFrame->imageDataOrigin[pixelPos+ 1] = 0;
                    pixelFrame->imageDataOrigin[pixelPos + 2] = 0;
                    numFoundPixel++;

                    int toBlockW = w / BLOCK_WIDTH;
                    int toBlockH = h / BLOCK_HEIGHT;

                    blockHolder[toBlockH][toBlockW]++;

                } else {
                    pixelFrame->imageDataOrigin[pixelPos] = 255;
                    pixelFrame->imageDataOrigin[pixelPos + 1] = 255;
                    pixelFrame->imageDataOrigin[pixelPos + 2] = 255;
                }
            }
        }

        numFoundBlock = 0;

        for(int h = 0; h < frameBlockHeight; h++) {
            for(int w = 0; w < frameBlockWidth; w++) {
                if(blockHolder[h][w] > BLOCK_THRESHOLD) 
                    numFoundBlock++;

                for(int i = 0; i < BLOCK_HEIGHT; i++) {
                    for(int j = 0; j < BLOCK_WIDTH; j++){
                        int addr = (h * BLOCK_HEIGHT * frameWidth + w * BLOCK_WIDTH + i * frameWidth + j) * 3;

                        if(blockHolder[h][w] > BLOCK_THRESHOLD) {
                            blockFrame->imageDataOrigin[addr]     = 255 - currentFrame->imageDataOrigin[addr];
                            blockFrame->imageDataOrigin[addr + 1] = 255 - currentFrame->imageDataOrigin[addr + 1];
                            blockFrame->imageDataOrigin[addr + 2] = 255 - currentFrame->imageDataOrigin[addr + 2];

                        } else {
                            blockFrame->imageDataOrigin[addr + 0] = currentFrame->imageDataOrigin[addr];
                            blockFrame->imageDataOrigin[addr + 1] = currentFrame->imageDataOrigin[addr + 1];
                            blockFrame->imageDataOrigin[addr + 2] = currentFrame->imageDataOrigin[addr + 2];
                        }
                    }
                }
                blockHolder[h][w] = 0;
            }
        }

        draw_gl_pixels(0, frameHeight, frameWidth, frameHeight, currentFrame->imageDataOrigin);
        draw_gl_pixels(frameWidth, 0, frameWidth, frameHeight, pixelFrame->imageDataOrigin);
        draw_gl_pixels(frameWidth, frameHeight, frameWidth, frameHeight, blockFrame->imageDataOrigin);


        blockFractionArray[currentFrameNum % framesInThreeMinutes] = (float)((float) numFoundBlock/ (float) numBlocks);

        if (((currentFrameNum + 1) % framesInThreeMinutes == 0) || (currentFrameNum + 1 == frameCount)) {
            double probability = 0;
            for(int i = 0; i < framesInThreeMinutes; i++) {
                probability += blockFractionArray[i]; 
            }

            slice_probabilities.push_back(probability / framesInThreeMinutes);

            cout << "In interval " << slice_probabilities.size() - 1 << " probability is: " << slice_probabilities.back() << " for " << (currentFrameNum + 1) % framesInThreeMinutes << " frames." << endl;

#ifdef _BOINC_APP_
            boinc_fraction_done((double)((double)currentFrameNum / (double)frameCount));

//            if(boinc_time_to_checkpoint() || key == 's')
//            if (key == s) {
            cout << "boinc_time_to_checkpoint encountered, checkpointing" << endl;
            write_checkpoint(checkpoint_filename, argv[1], slice_probabilities);
            boinc_checkpoint_completed();
//                if(key == 's')	boinc_finish(1);
//            }
#endif
        }

        cvReleaseImage(&lastFrame); //frame currently pointed too by lastFrame has been analyzed twice, no longer needed

        lastFrame = cvCloneImage(currentFrame);

//        cvShowImage("Video", currentFrame);
//        cvShowImage("Video Pixels", pixelFrame);
//        cvShowImage("Video Blocks", blockFrame);

        key = cvWaitKey( 1000 / fps );

        currentFrameNum++;
    }

    fprintf(stderr, "<slice_time>%d</slice_time\n", SLICE_TIME_S);
    fprintf(stderr, "<slice_probabilities>\n");
    for (uint32_t i = 0; i < slice_probabilities.size(); i++) {
        fprintf(stderr, "%1.5f\n", slice_probabilities[i]);
    }
    fprintf(stderr, "</slice_probabilities>\n");

    /*for(int i = 0; i < currentFrameNum; i++) 
      {
      fprintf(fPixel,   "%d\n",  receivedPixelChanges[i]);
      fprintf(fBlock,   "%d\n",  receivedBlockChanges[i]);
      }

    //Close Used files
    fclose(fPixel);
    fclose(fBlock);
    fclose(fCheckpoint);*/

    //Free remaining frame
    cvReleaseCapture( &capture );

    //Destroy Windows
//    cvDestroyWindow("Video");
//    cvDestroyWindow("Video Pixels");
//    cvDestroyWindow("Video Blocks");

#ifdef USE_OPENGL
    analysis_finished = true;
//    opengl_thread.join();
#endif

    //Finish
#ifdef _BOINC_APP_
    boinc_finish(0);
#else
    exit(0);
#endif
}

