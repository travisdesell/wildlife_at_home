#include <stdio.h>
#include <stdlib.h>
#include <iostream>

//BOINC includes
#include "boinc_api.h"
#include "filesys.h"

//OpenCV Includes
#include "opencv.hpp"

#define PIXEL_THRESHOLD 20
#define BLOCK_THRESHOLD 70
#define BLOCK_WIDTH 20
#define BLOCK_HEIGHT 20

int main(int argc, char** argv)
{
	//Variable Declarations
	std::string resolved_outputPixel;
	std::string resolved_outputBlock;
	std::string resolved_checkpoint;
	
	FILE *fPixel;
	FILE *fBlock;
	FILE *fCheckpoint;
	
	int fileResolveRetval;
	
	char varStrFromFile[100];
	char * prop;
	char * val;
	
	CvCapture *capture;
	
	IplImage  *currentFrame;
	IplImage  *lastFrame;
	
	IplImage  *pixelFrame;
	IplImage  *blockFrame;
	
	int startFrame;
	int frameCount;
	int fps;
	int counter;
	
	int frameWidth;
	int frameHeight;

	int frameBlockHeight;
	int frameBlockWidth;
	
	int *pixelValues;
	bool *blockValues;
	int * receivedPixelChanges;
	int * receivedBlockChanges;
	
	int numFoundPixel;
	
	int key;
	
	int myInt;
	int myInt2;
	
	int ** blockHolder;
	//Assure we have at least an argument to attempt to open
	assert(argc == 2);
	
	boinc_init();
	
	//Open pixel, block, and checkpoint filesys
	
	//a+ for pixel and block, because we want to create if non-existant, or append if exists
	fileResolveRetval = boinc_resolve_filename_s("outputPixel.txt", resolved_outputPixel);
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
	
	//Get Checkpoint Properties, currently frame would seem to be the correct annd only property needed to resume
	startFrame = 0;
	if(fCheckpoint) {
		while(!feof(fCheckpoint)){
			fgets(varStrFromFile, 100, fCheckpoint);
			prop = strtok(varStrFromFile, " ");
			val = strtok(NULL, " ");
			
			if(!strcmp(prop, "Frame")) {
				startFrame = atoi(val);
			}
		}
	}
	
	//checkpoint file has been read, now reset
	fCheckpoint = boinc_fopen(resolved_checkpoint.c_str(), "w");
	
	//Get the video
	capture = cvCaptureFromAVI(argv[1]);
	if(!capture) return 1;

	//Get some video properties
	fps = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FPS );
	frameCount = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);
	frameWidth = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_WIDTH);
	frameHeight = (int)cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_HEIGHT);

	frameBlockWidth = ceil((double)frameWidth / (double)BLOCK_WIDTH);
	frameBlockHeight = ceil((double)frameHeight / (double)BLOCK_HEIGHT);
	
	blockHolder = new int*[frameBlockHeight];
	
	for(int i = 0; i < frameBlockHeight; i++) {
		blockHolder[i] = new int[frameBlockWidth];
		for(int j = 0; j < frameBlockWidth; j++) {
			blockHolder[i][j] = 0;
		}
			
	}
	
	
	printf("FPS:          %d\n", fps);;
	printf("Frame Count:  %d\n", frameCount);
	printf("Frame Width:  %d\n", frameWidth);
	printf("Frame Heigth: %d\n", frameHeight);
	
	printf("Frame Block Width: %d\n", frameBlockWidth);
	printf("Frame Block Height: %d\n", frameBlockHeight);
	
	//Create the windows we see the output in
	cvNamedWindow("Video", 0);
	cvNamedWindow("Video Pixels", 0);
	cvNamedWindow("Video Blocks", 0);

	pixelValues = new int[504];
	blockValues = new bool[504];
	
	receivedPixelChanges = new int[frameCount];
	receivedBlockChanges = new int[frameCount];

	//This is what should happen, but does not work currently
		//cvSetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES, startFrame); 
	
	//Instead...
	currentFrame = cvQueryFrame(capture);
	
	for(int i = 0; i < startFrame; i++) {
		currentFrame = cvQueryFrame(capture);
	}
	
	pixelFrame = cvCloneImage(currentFrame);
	blockFrame = cvCloneImage(currentFrame);        
	lastFrame = cvCloneImage(currentFrame);

	counter = startFrame;	
	
	while(true) //quit when q is pressed
	{
		boinc_fraction_done((double)((double)counter / (double)frameCount));
		
		if(boinc_time_to_checkpoint() || key == 's')
		{
			printf("checkpointing\n");
			fprintf(fCheckpoint, "\nFrame %d", counter);
			boinc_checkpoint_completed();
			if(key == 's') break;
		}
		
		currentFrame = cvQueryFrame(capture);
		
		if(!currentFrame) break; //quit when no frames remain
		
	/*	for(int positiony=0;positiony<24;positiony++)
		{
			for(int positionx=0;positionx<21;positionx++)
			{
				int position=positiony*21+positionx;
				
				int actual = 0;
				int actual2 = 0;
				
				int numFoundPixel = 0;
				bool blockFound = false;

				for(int y = 0; y < 20; y++)
				{
					actual = (positiony * 2112 * 20) + (y * 2112);
					for(int x = 0; x < 34; x++)
					{
						if(positionx * 34 + x > 703)
						{
							continue;
						}

						actual2 = actual + (positionx * 34 * 3 + x * 3);

						if(abs(currentFrame->imageDataOrigin[actual2] - lastFrame->imageDataOrigin[actual2]) > PIXEL_THRESHOLD
						&& abs(currentFrame->imageDataOrigin[actual2 + 1] - lastFrame->imageDataOrigin[actual2+ 1]) > PIXEL_THRESHOLD
						&& abs(currentFrame->imageDataOrigin[actual2 + 2] - lastFrame->imageDataOrigin[actual2 + 2]) > PIXEL_THRESHOLD)
						{
							pixelFrame->imageDataOrigin[actual2] = 0;
							pixelFrame->imageDataOrigin[actual2+ 1] = 0;
							pixelFrame->imageDataOrigin[actual2 + 2] = 0;
							numFoundPixel++;
						}
						else
						{
							pixelFrame->imageDataOrigin[actual2] = 255;
							pixelFrame->imageDataOrigin[actual2 + 1] = 255;
							pixelFrame->imageDataOrigin[actual2 + 2] = 255;
						}
					}
				}

				for(int y = 0; y < 20; y++)
				{
					actual = (positiony * 2112 * 20) + (y * 2112);
					for(int x = 0; x < 34; x++)
					{
						if(positionx * 34 + x > 703)
						{
							continue;		
						}
						
						actual2 = actual + (positionx * 34 * 3 + x * 3);
						
						if(numFoundPixel > BLOCK_THRESHOLD)
						{
							blockFrame->imageDataOrigin[actual2] = 0;
							blockFrame->imageDataOrigin[actual2+ 1] = 0;
							blockFrame->imageDataOrigin[actual2 + 2] = 0;
							blockFound = true;
						}
						else
						{
							blockFrame->imageDataOrigin[actual2] = 255;
							blockFrame->imageDataOrigin[actual2 + 1] = 255;
							blockFrame->imageDataOrigin[actual2 + 2] = 255;
							blockFound = false;
						}
					}
				}

				pixelValues[position] = numFoundPixel;
				blockValues[position] = blockFound;
			}
		}*/
		
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
		
		for(int h = 0; h < frameBlockHeight; h++) {
			for(int w = 0; w < frameBlockWidth; w++) {
				for(int i = 0; i < BLOCK_HEIGHT; i++) {
					for(int j = 0; j < BLOCK_WIDTH; j++){
						int addr = (h * BLOCK_HEIGHT * frameWidth + w * BLOCK_WIDTH + i * frameWidth + j) * 3;
						if(blockHolder[h][w] > BLOCK_THRESHOLD) {
							blockFrame->imageDataOrigin[addr] = 0;
							blockFrame->imageDataOrigin[addr + 1] = 0;
							blockFrame->imageDataOrigin[addr + 2] = 0;
						}
						else{
							blockFrame->imageDataOrigin[addr] = 255;
							blockFrame->imageDataOrigin[addr + 1] = 255;
							blockFrame->imageDataOrigin[addr + 2] = 255;
						}
					}
				}
				blockHolder[h][w] = 0;
			}
		}
	
		cvReleaseImage(&lastFrame); //frame currently pointed too by lastFrame has been analyzed twice, no longer needed

		lastFrame = cvCloneImage(currentFrame);

		cvShowImage("Video",currentFrame);
		cvShowImage("Video Pixels", pixelFrame);
		cvShowImage("Video Blocks",blockFrame);

		key = cvWaitKey( 1000/ fps );
		
		myInt = 0;
		for(int i = 0; i < 504; i++)
		{
			myInt += pixelValues[i];
		}
		
		myInt2 = 0;
		for(int i = 0; i < 504; i++)
		{
			if(blockValues[i])
			{
				myInt2++;
			}
		}
		receivedPixelChanges[counter] = myInt;
		receivedBlockChanges[counter] = myInt2;
		
		counter++;
   }

	for(int i = 0; i < counter; i++) 
	{
		fprintf(fPixel,   "%d\n",  receivedPixelChanges[i]);
		fprintf(fBlock,   "%d\n",  receivedBlockChanges[i]);
	}
	
	//Close Used files
	fclose(fPixel);
	fclose(fBlock);
	fclose(fCheckpoint);

	//Free remaining frame
	cvReleaseCapture( &capture );

	//Destroy Windows
	cvDestroyWindow("Video");
	cvDestroyWindow("Video Pixels");
	cvDestroyWindow("Video Blocks");
	
	//Finish
	boinc_finish(0);
}

