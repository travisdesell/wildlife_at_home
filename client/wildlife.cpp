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
#include "opencv.hpp"

#define PIXEL_THRESHOLD 10
#define BLOCK_THRESHOLD 20
#define BLOCK_WIDTH 5
#define BLOCK_HEIGHT 5

using boost::thread;

using namespace std;

void write_checkpoint(string checkpoint_filename, string video_filename, int frame, int intervals, float * probArr)
{
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
    checkpoint_file << "FRAME: " << frame << endl;
    checkpoint_file << "INTERVALS: " << intervals << endl;
    for(int i = 0; i < intervals; i++) {
        checkpoint_file << probArr[i] << endl;
    }
    checkpoint_file << endl;

    checkpoint_file.close();
}

bool read_checkpoint(string checkpoint_filename, string &video_filename, int &frame, int &intervals, vector<float> *probVec ) {
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

    checkpoint_file >> s >> frame;
    if (s.compare("FRAME:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'FRAME'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

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
        probVec->push_back(current);
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
float *pixels;

void display() {
/*
    for (int i = 0; i < NUMBER_DROPLETS; i++) {
        unset_pixel(droplet_x[i], droplet_y[i]);
    }   

    for (int i = 0; i < NUMBER_DROPLETS; i++) {
        move_droplet(droplet_x[i], droplet_y[i]);
    }   

    for (int i = 0; i < NUMBER_DROPLETS; i++) {
        if (droplet_adjacent(droplet_x[i], droplet_y[i])) {
            set_pixel(droplet_x[i], droplet_y[i]);
            new_droplet(droplet_x[i], droplet_y[i]);
        }   
    }   

    for (int i = 0; i < NUMBER_DROPLETS; i++) {
        set_pixel(droplet_x[i], droplet_y[i]);
    }   
*/
//    cout << "droplet is at: " << droplet_x << ", " << droplet_y << endl;


    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

    //http://msdn2.microsoft.com/en-us/library/ms537062.aspx
    //glDrawPixels writes a block of pixels to the framebuffer.

    glDrawPixels(frameWidth, frameHeight, GL_RGB, GL_FLOAT, pixels);

    glFlush();
    glutSwapBuffers();

    glutPostRedisplay();
}
void glutMainLoopWrapper() {
    glutMainLoop();
}
#endif

int main(int argc, char** argv)
{
    //Variable Declarations
    std::string resolved_outputPixel;
    std::string resolved_outputBlock;
    std::string resolved_checkpoint;

    //FILE *fPixel;
    //FILE *fBlock;
    //FILE *fCheckpoint;

    //int fileResolveRetval;

    //char varStrFromFile[100];
    //char * prop;
    //char * val;

    CvCapture *capture;

    IplImage  *currentFrame;
    IplImage  *lastFrame;

    IplImage  *pixelFrame;
    IplImage  *blockFrame;

    int startFrame;
    int interval;
    int frameCount;
    int fps;
    int currentFrameNum;


    //These are now global for OpenGL
//    int frameWidth;
//    int frameHeight;

    int frameBlockHeight;
    int frameBlockWidth;
    int numBlocks;

    int framesInThreeMinutes;
    int numberOfThreeMinuteIntervals;
    float * blockFractionArray;
    float * threeMinuteIntervalProbabilityArray;

    //int *pixelValues;
    //bool *blockValues;
    //int * receivedPixelChanges;
    //int * receivedBlockChanges;

    int numFoundPixel;
    int numFoundBlock;

    int key = 0;

    //int myInt;
    //int myInt2;


    int ** blockHolder;
    //Assure we have at least an argument to attempt to open
    assert(argc == 2);

#ifdef _BOINC_APP_	
    boinc_init();
#endif

    //Open pixel, block, and checkpoint filesys

    //a+ for pixel and block, because we want to create if non-existant, or append if exists
    /*fileResolveRetval = boinc_resolve_filename_s("outputPixel.txt", resolved_outputPixel);
      if (fileResolveRetval) boinc_finish(-1);
      fPixel = boinc_fopen(resolved_outputPixel.c_str(), "a+");

      fileResolveRetval = boinc_resolve_filename_s("outputBlock.txt", resolved_outputBlock);
      if (fileResolveRetval) boinc_finish(-1);
      fBlock = boinc_fopen(resolved_outputBlock.c_str(), "a+");

    //r for checkpoint, becuase we currently just want to read it
    //It is re-opened after read because after it is read we want to overwrite it completetly
    fileResolveRetval = boinc_resolve_filename_s("testCheckpoint.txt", resolved_checkpoint);
    if (fileResolveRetval) boinc_finish(-1);
    fCheckpoint = boinc_fopen(resolved_checkpoint.c_str(), "r");

    //Get Checkpoint Properties, currently frame would seem to be the correct and only property needed to resume
    startFrame = 0;
    if(fCheckpoint) {
    while(!feof(fCheckpoint)){
    fgets(varStrFromFile, 100, fCheckpoint);
    prop = strtok(varStrFromFile, " ");
    val = strtok(NULL, " ");

    if(!strcmp(prop, "Frame")) {
    startFrame = atoi(val);
    }
    }s
    }*/

    string checkpoint_filename = "checkpoint.txt";

    string checkpointed_video_filename;
    int checkpointed_video_frame;
    int checkpointed_video_intervals;
    vector<float> *checkpointed_video_probabilities = new vector<float>();

    startFrame = 0;
    interval = 0;
    bool successful_checkpoint_read = read_checkpoint(	checkpoint_filename, 
            checkpointed_video_filename, 
            checkpointed_video_frame,
            checkpointed_video_intervals,
            checkpointed_video_probabilities);

    if(successful_checkpoint_read) {
        if(checkpointed_video_filename.compare(argv[1]) != 0) {
            cout << "Checkpointed video filename was not the same as given video filename... Restarting" << endl;
        } else {
            cout << "Continuing from checkpoint..." << endl;
            interval = checkpointed_video_intervals;
            startFrame = checkpointed_video_frame; //no longer needed
        }
    } else {
        cout << "Unsuccessful checkpoint read" << endl << "Starting from beginning of video" << endl;
    }

#ifdef _BOINC_APP_
    //boinc_finish(1);
#else
    //exit(1);
#endif

    //Get the video
    capture = cvCaptureFromAVI(argv[1]);
    if(!capture) return 1;
    //Get some video properties
    fps 				= (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);
    frameCount 			= (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);
    frameWidth 			= (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_WIDTH);
    frameHeight 		= (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_HEIGHT);
    frameBlockWidth 	= ceil((double)frameWidth / (double)BLOCK_WIDTH);
    frameBlockHeight 	= ceil((double)frameHeight / (double)BLOCK_HEIGHT);

    numBlocks			= frameBlockHeight * frameBlockWidth;
    framesInThreeMinutes = fps * 180;

    blockFractionArray = new float[framesInThreeMinutes];
    numberOfThreeMinuteIntervals = ceil((float)((float) frameCount / (float) framesInThreeMinutes));
    threeMinuteIntervalProbabilityArray = new float[numberOfThreeMinuteIntervals];

    for(int i = 0; i < interval; i++) {
        threeMinuteIntervalProbabilityArray[i] = checkpointed_video_probabilities->at(i);
        cout << "prob " << i << "is: " << threeMinuteIntervalProbabilityArray[i] << endl;
    }

    blockHolder = new int*[frameBlockHeight];
    for(int i = 0; i < frameBlockHeight; i++) {
        blockHolder[i] = new int[frameBlockWidth];
        for(int j = 0; j < frameBlockWidth; j++) {
            blockHolder[i][j] = 0;
        }
    }

#ifdef USE_OPENGL
    pixels = (float*)malloc(frameWidth * frameHeight * 3 * sizeof(float));
    for (int i = 0; i < frameWidth * frameHeight * 3; i++) {
        pixels[i] = 0.0;
    }

    glutInit(&argc, argv);

    glutInitDisplayMode(GLUT_RGB | GLUT_DOUBLE | GLUT_DEPTH);
    glutInitWindowSize(frameWidth, frameHeight);

    glutCreateWindow(argv[1]);      //the name of the window is the name of the video

    glutDisplayFunc(display);

    glEnable(GL_DEPTH_TEST);
    glClearColor(0.0, 0.0, 0.0, 1.0);

    boost::thread opengl_thread( glutMainLoop );
#endif

    //Create the windows we see the output in
    cvNamedWindow("Video", 0);
    cvNamedWindow("Video Pixels", 0);
    cvNamedWindow("Video Blocks", 0);

    //pixelValues = new int[504];
    //blockValues = new bool[504];

    //receivedPixelChanges = new int[frameCount];
    //receivedBlockChanges = new int[frameCount];

    //This is what should happen, but does not work currently
    //cvSetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES, startFrame); 

    //Instead...
    currentFrame = cvQueryFrame(capture);
    startFrame = interval * framesInThreeMinutes;
    for(int i = 0; i < startFrame; i++) {
        currentFrame = cvQueryFrame(capture);
    }

    pixelFrame = cvCloneImage(currentFrame);
    blockFrame = cvCloneImage(currentFrame);        
    lastFrame = cvCloneImage(currentFrame);

    currentFrameNum = startFrame;	
    while(true) //quit when q is pressed
    {

#ifdef _BOINC_APP_
        boinc_fraction_done((double)((double)currentFrameNum / (double)frameCount));

        if(boinc_time_to_checkpoint() || key == 's')
        {
            cout << "boinc_time_to_checkpoint encountered, checkpointing" << endl;
            write_checkpoint(checkpoint_filename, argv[1], currentFrameNum, interval, threeMinuteIntervalProbabilityArray);
            boinc_checkpoint_completed();
            if(key == 's')	boinc_finish(1);
        }
#endif
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

                        int pixel_addr = (((frameHeight - ((h * BLOCK_HEIGHT) + i) - 1) * frameWidth) + (w * BLOCK_WIDTH + j)) * 3;

                        if(blockHolder[h][w] > BLOCK_THRESHOLD) {
                            blockFrame->imageDataOrigin[addr]     = 255 - currentFrame->imageDataOrigin[addr];
                            blockFrame->imageDataOrigin[addr + 1] = 255 - currentFrame->imageDataOrigin[addr + 1];
                            blockFrame->imageDataOrigin[addr + 2] = 255 - currentFrame->imageDataOrigin[addr + 2];

#ifdef USE_OPENGL
                            pixels[pixel_addr + 0]    = blockFrame->imageDataOrigin[addr];
                            pixels[pixel_addr + 1]    = blockFrame->imageDataOrigin[addr + 1];
                            pixels[pixel_addr + 2]    = blockFrame->imageDataOrigin[addr + 2];
#endif

                        } else {
                            blockFrame->imageDataOrigin[addr]     = currentFrame->imageDataOrigin[addr];
                            blockFrame->imageDataOrigin[addr + 1] = currentFrame->imageDataOrigin[addr + 1];
                            blockFrame->imageDataOrigin[addr + 2] = currentFrame->imageDataOrigin[addr + 2];

#ifdef USE_OPENGL
                            pixels[pixel_addr + 0]    = blockFrame->imageDataOrigin[addr];
                            pixels[pixel_addr + 1]    = blockFrame->imageDataOrigin[addr + 1];
                            pixels[pixel_addr + 2]    = blockFrame->imageDataOrigin[addr + 2];
#endif
                        }
                    }
                }
                blockHolder[h][w] = 0;
            }
        }

        blockFractionArray[currentFrameNum % framesInThreeMinutes] = (float)((float) numFoundBlock/ (float) numBlocks);

        if((currentFrameNum + 1) % framesInThreeMinutes == 0) {
            threeMinuteIntervalProbabilityArray[interval] = 0;
            for(int i = 0; i < framesInThreeMinutes; i++) {
                threeMinuteIntervalProbabilityArray[interval] += blockFractionArray[i]; 
            }

            threeMinuteIntervalProbabilityArray[interval] = threeMinuteIntervalProbabilityArray[interval] / framesInThreeMinutes;

            cout << "In interval " << interval << " probability is: " << threeMinuteIntervalProbabilityArray[interval] << endl;
            interval++;
        }

        cvReleaseImage(&lastFrame); //frame currently pointed too by lastFrame has been analyzed twice, no longer needed

        lastFrame = cvCloneImage(currentFrame);

        cvShowImage("Video",currentFrame);
        cvShowImage("Video Pixels", pixelFrame);
        cvShowImage("Video Blocks",blockFrame);

        key = cvWaitKey( 1000/ fps );

        /*myInt = 0;
          for(int i = 0; i < 504; i++) {
          myInt += pixelValues[i];
          }

          myInt2 = 0;
          for(int i = 0; i < 504; i++) {
          if(blockValues[i]) {
          myInt2++;
          }
          }
          receivedPixelChanges[currentFrameNum] = myInt;
          receivedBlockChanges[currentFrameNum] = myInt2;*/

        currentFrameNum++;
    }

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
    cvDestroyWindow("Video");
    cvDestroyWindow("Video Pixels");
    cvDestroyWindow("Video Blocks");

#ifdef USE_OPENGL
    opengl_thread.join();
#endif

    //Finish
#ifdef _BOINC_APP_
    boinc_finish(0);
#else
    exit(0);
#endif
}

