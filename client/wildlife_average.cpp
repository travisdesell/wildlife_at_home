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
#include <time.h>

 #ifdef __cplusplus
#define __STDC_CONSTANT_MACROS
#ifdef _STDINT_H
#undef _STDINT_H
#endif
# include <stdint.h>
#endif

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

extern "C" {
	#include <libavcodec/avcodec.h>
	#include <libavformat/avformat.h>
	#include <libswscale/swscale.h>
}   


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
using namespace std;
int averageRange;
int framesInThreeMinutes;

int currentFrameNumber;

int key;

int fps, frameCount, frameWidth, frameHeight;

float ***avgImagePlaceholder;

char *gl_pixels;

int gl_width, gl_height;

bool analysis_finished = false;

vector<AVFrame *> * future_images;

ofstream outfile;

int * responseStrengths;
char * responseGraph;

double * intermediateResponses;
double frameStrength;

double maxFrameStrength;

vector<float> * probabilities;
int numberOfSlices;

float numberOfFramesInAverage;

bool endOfVideoReached = false;

string checkpoint_filename;
string checkpointed_video_file_name;
string video_file_name;

AVFormatContext *pFormatCtx;
int             i, videoStreamIdx;
AVCodecContext  *pCodecCtx;
AVCodec         *pCodec;
AVFrame         *currentFrame;
AVFrame         *averageFrame;
AVFrame         *differenceFrame;
AVFrame         *pFrameRGB;
int             numBytes;
uint8_t         *buffer;
static struct SwsContext *img_convert_ctx;

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

AVFrame * CopyFrame(AVFormatContext * fCtx, AVCodecContext * cCtx, AVFrame * fromFrame) {
	AVFrame * newFrame = avcodec_alloc_frame();
	
	uint8_t *newBuffer = (uint8_t *) av_malloc(numBytes*sizeof(uint8_t));;
	
	avpicture_fill((AVPicture *)newFrame, newBuffer, PIX_FMT_RGB24, cCtx->width, cCtx->height);
	
	memcpy(newFrame->data[0], fromFrame->data[0], cCtx->width*cCtx->height*3); // This 
	
	return newFrame;
}

void GetNextFrame(AVFormatContext * fCtx, AVCodecContext * cCtx, int streamID, AVFrame * toFrame)
{
	AVPacket packet2;
	int frameFinished;
	AVFrame * placeholder;
	placeholder = avcodec_alloc_frame();
	av_read_frame(fCtx, &packet2);

	if(currentFrameNumber + averageRange + 10 > frameCount) {
		endOfVideoReached = true;
		return;
	}

    if(packet2.stream_index == streamID) {
    	avcodec_decode_video2(cCtx, placeholder, &frameFinished, &packet2);
    }
    if(frameFinished) {    
		sws_scale(img_convert_ctx, (const uint8_t * const *)placeholder->data, placeholder->linesize, 0, cCtx->height, toFrame->data, toFrame->linesize);
    }
    else
    {
    	toFrame = NULL;
    	endOfVideoReached = true;
    }

    av_free(placeholder);
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


void difference(AVFrame * average, AVFrame * destination, AVFrame * current) {
	frameStrength = 0;
    for(int y = 0; y < frameHeight; y++) {
        for(int x = 0; x < frameWidth; x++) {
            int pixelPos = (y * frameWidth + x) * 3;

            //simply calculate the difference
            destination->data[0][pixelPos    ] = (unsigned char)abs((unsigned char)average->data[0][pixelPos    ] - (unsigned char)current->data[0][pixelPos    ]);
            destination->data[0][pixelPos + 1] = (unsigned char)abs((unsigned char)average->data[0][pixelPos + 1] - (unsigned char)current->data[0][pixelPos + 1]);
            destination->data[0][pixelPos + 2] = (unsigned char)abs((unsigned char)average->data[0][pixelPos + 2] - (unsigned char)current->data[0][pixelPos + 2]);

            //calculate a scalar value for the difference, which is that pixel's response value
            unsigned int xStrength = destination->data[0][pixelPos    ];
            unsigned int yStrength = destination->data[0][pixelPos + 1];
            unsigned int zStrength = destination->data[0][pixelPos + 2];

			frameStrength += xStrength;
			frameStrength += yStrength;
			frameStrength += zStrength;

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
    
    frameStrength = frameStrength / maxFrameStrength;
}

void local_average_advance() {
    if(!currentFrame) return;

    AVFrame * last = future_images->at(0);

    future_images->erase(future_images->begin());
    future_images->push_back(CopyFrame(pFormatCtx, pCodecCtx, currentFrame));

    for(int y = 0; y < frameHeight; y++) {
        for(int x = 0; x < frameWidth; x++) {
            int pixelPos = (y * frameWidth + x) * 3;

            //Simply ignore the area the clock is located at by simply using the current frame's values there
            if(x > (frameWidth * .8) && y > (frameHeight * .8)) {
                averageFrame->data[0][pixelPos    ] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->data[0][pixelPos    ];
                averageFrame->data[0][pixelPos + 1] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->data[0][pixelPos + 1];
                averageFrame->data[0][pixelPos + 2] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->data[0][pixelPos + 2];
                continue;
            } 

            //get rid of the oldest frame in the average
            avgImagePlaceholder[y][x][0] -= (unsigned char)last->data[0][pixelPos    ];
            avgImagePlaceholder[y][x][1] -= (unsigned char)last->data[0][pixelPos + 1];
            avgImagePlaceholder[y][x][2] -= (unsigned char)last->data[0][pixelPos + 2];

            //add in the newest frame to the average
            avgImagePlaceholder[y][x][0] += (unsigned char)currentFrame->data[0][pixelPos    ];
            avgImagePlaceholder[y][x][1] += (unsigned char)currentFrame->data[0][pixelPos + 1];
            avgImagePlaceholder[y][x][2] += (unsigned char)currentFrame->data[0][pixelPos + 2];

            //cast the average stored in floats into the image's char array
            averageFrame->data[0][pixelPos    ] = (unsigned char)(avgImagePlaceholder[y][x][0] / numberOfFramesInAverage);
            averageFrame->data[0][pixelPos + 1] = (unsigned char)(avgImagePlaceholder[y][x][1] / numberOfFramesInAverage);
            averageFrame->data[0][pixelPos + 2] = (unsigned char)(avgImagePlaceholder[y][x][2] / numberOfFramesInAverage);
        }
    }

	free(last->data[0]);
	av_frame_unref(last);
	av_frame_free(&last);
}

long start_time = 0;

void display() {
#ifdef OPENGL
    glClear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);
#endif

    if (currentFrameNumber % 100 == 0) {
        cout << "Frame: " << currentFrameNumber << endl;
        cout << "FPS: " << currentFrameNumber / ((double)time(NULL) - double(start_time)) << endl;
    }

    //end of video reached
    if(currentFrameNumber == frameCount || endOfVideoReached) {
        //account for last fragment
        double prob = 0;

        for(int i = 0; i < framesInThreeMinutes; i++) {
			prob += intermediateResponses[i];

            intermediateResponses[i] = 0;
        }
       
        prob = prob / (double)framesInThreeMinutes;
       
        probabilities->push_back(prob);

        //output
        cerr << "<video_name>" << video_file_name << "</video_name>" << endl;
        cerr << "<slice_time>" << 180 << "</slice_time>" << endl;
        cerr << "<slice_probabilities>" << endl;

        for (uint32_t i = 0; i < probabilities->size(); i++) {
            cerr << fixed << setprecision(4) << probabilities->at(i) << endl;
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
    //store an intermediate response for calculating the 3 minute response

    intermediateResponses[currentFrameNumber % framesInThreeMinutes] = frameStrength;
    
    GetNextFrame(pFormatCtx, pCodecCtx, videoStreamIdx, currentFrame);
   
    if(!endOfVideoReached) {
    	local_average_advance();
    }    
    //Calculate a three minute response
    if((currentFrameNumber + 1) % framesInThreeMinutes == 0) {
        double prob = 0;

        for(int i = 0; i < framesInThreeMinutes; i++) {
			prob += intermediateResponses[i];

            intermediateResponses[i] = 0;
        }
       
        prob = prob / (double)framesInThreeMinutes;
       
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

    delete responseGraph;
    delete responseStrengths;

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

	 av_register_all();

    if(avformat_open_input(&pFormatCtx, video_file_name.c_str(), NULL, NULL) != 0) {
        cerr << "ERROR: couldn't get capture from input file: '" << video_file_name << "'" << endl;
        return -1;
    }
    
   if(avformat_find_stream_info(pFormatCtx, NULL) < 0) {
		cerr << "ERROR: couldn't get stream info: '" << video_file_name << "'" << endl;
        return -1; // Couldn't find stream information
	}
	
	videoStreamIdx = -1;
	for(i = 0; i < pFormatCtx->nb_streams; i++) {
		if(pFormatCtx->streams[i]->codec->codec_type == AVMEDIA_TYPE_VIDEO) { //CODEC_TYPE_VIDEO
			videoStreamIdx=i;
			break;
		}
	}
	
	if(videoStreamIdx==-1) {
		cerr << "ERROR: couldn't find a video stream'" << video_file_name << "'" << endl;
		return -1; // Didn't find a video stream
	}
	
	pCodecCtx = pFormatCtx->streams[videoStreamIdx]->codec;
	
	pCodec = avcodec_find_decoder( pCodecCtx->codec_id);
    if(pCodec==NULL) {
        fprintf(stderr, "Unsupported codec!\n");
        return -1; // Codec not found
    }

    if( avcodec_open2(pCodecCtx, pCodec, NULL) < 0 ) {
        return -1; // Could not open codec
    }
	
	AVRational rational = pCodecCtx->time_base;
	
	fps = rational.den / 2;
	frameCount = (pFormatCtx->duration / AV_TIME_BASE) * fps;
	frameWidth = pCodecCtx->width;
	frameHeight = pCodecCtx->height;
	
    key = 0;

    gl_width = frameWidth * 2;
    gl_height = frameHeight * 2;

    gl_pixels = (char*)malloc(gl_width * gl_height * 3 * sizeof(char));

    for (int i = 0; i < gl_width * gl_height * 3; i++) {
        gl_pixels[i] = rand();
    }

#ifdef OPENGL
    doGlut(argc, argv);
#endif
 	
    averageRange = fps * 5;
    framesInThreeMinutes = fps * 180;
    numberOfFramesInAverage = averageRange * 2 + 1;

    intermediateResponses = new double[framesInThreeMinutes];

	for(int i = 0; i < framesInThreeMinutes; i++) {
		intermediateResponses[i] = 0;
	}

	frameStrength = 0;

    numberOfSlices = ceil((double)frameCount / (double)framesInThreeMinutes);

    probabilities = new vector<float>();

	numBytes = avpicture_get_size(PIX_FMT_RGB24, pCodecCtx->width, pCodecCtx->height);
	buffer = (uint8_t *) av_malloc(numBytes*sizeof(uint8_t));
	
	currentFrame = avcodec_alloc_frame(); 
	averageFrame = avcodec_alloc_frame();
	differenceFrame = avcodec_alloc_frame();

	avpicture_fill((AVPicture *)currentFrame, buffer, PIX_FMT_RGB24, pCodecCtx->width, pCodecCtx->height);
	avpicture_fill((AVPicture *)averageFrame, buffer, PIX_FMT_RGB24, pCodecCtx->width, pCodecCtx->height);
	avpicture_fill((AVPicture *)differenceFrame, buffer, PIX_FMT_RGB24, pCodecCtx->width, pCodecCtx->height);

    img_convert_ctx = sws_getContext(pCodecCtx->width, pCodecCtx->height, pCodecCtx->pix_fmt, pCodecCtx->width, pCodecCtx->height, PIX_FMT_RGB24, SWS_BICUBIC, NULL, NULL, NULL);
      
    maxFrameStrength = frameWidth * frameHeight * 3 * 256;
   
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
			
            for(int k = 0; k < 3; k++) {
                avgImagePlaceholder[i][j][k] = 0;
            }
        }
    }

	future_images = new vector<AVFrame *>();

    //Start either at frame 0 or at the frame next in line after checkpointing
    currentFrameNumber = probabilities->size() * framesInThreeMinutes;

    cerr << "Starting at Frame: " << currentFrameNumber << endl;

	GetNextFrame(pFormatCtx, pCodecCtx, videoStreamIdx, currentFrame);
	
    //Advance the video to the appropriate spot, which is averageRange frames behind the current Frame
    for(int i = 0; i < currentFrameNumber - averageRange; i++) {    
		GetNextFrame(pFormatCtx, pCodecCtx, videoStreamIdx, currentFrame);
    }

    for(int i = 0; i < averageRange * 2 + 1; i++) {
   		future_images->push_back(CopyFrame(pFormatCtx, pCodecCtx, currentFrame));
   		for(int y = 0; y < frameHeight; y++) {
            for(int x = 0; x < frameWidth; x++) {
                int pixelPos = (y * frameWidth + x) * 3;

				avgImagePlaceholder[y][x][0] += (unsigned char)currentFrame->data[0][pixelPos];
                avgImagePlaceholder[y][x][1] += (unsigned char)currentFrame->data[0][pixelPos + 1];
                avgImagePlaceholder[y][x][2] += (unsigned char)currentFrame->data[0][pixelPos + 2];

                //The last time through this loop, calculate the average for real
                if(i == averageRange * 2) {
                    if(x > (frameWidth * .8) && y > (frameHeight * .8)) {
                        averageFrame->data[0][pixelPos    ] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->data[0][pixelPos    ];
                        averageFrame->data[0][pixelPos + 1] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->data[0][pixelPos + 1];
                        averageFrame->data[0][pixelPos + 2] = future_images->at(absToRel(currentFrameNumber, frameCount, averageRange))->data[0][pixelPos + 2];
                        continue;
                    }
					averageFrame->data[0][pixelPos    ] = (unsigned char)(avgImagePlaceholder[y][x][0] / numberOfFramesInAverage);
                    averageFrame->data[0][pixelPos + 1] = (unsigned char)(avgImagePlaceholder[y][x][1] / numberOfFramesInAverage);
                    averageFrame->data[0][pixelPos + 2] = (unsigned char)(avgImagePlaceholder[y][x][2] / numberOfFramesInAverage);
                }
            }
        }
		GetNextFrame(pFormatCtx, pCodecCtx, videoStreamIdx, currentFrame);
    }
    
    endOfVideoReached = false;
    

    start_time = time(NULL);

    if(currentFrameNumber < averageRange)
    	currentFrameNumber = averageRange;
#ifdef OPENGL 
    glutMainLoop();
#else
    while (true) {
        display();
    }
#endif	
}
