#include <cstdio>
#include <fstream> 
#include <iostream>
#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/calib3d/calib3d.hpp>

using namespace std;
using namespace cv;

void printUsage();
void my_mouse_callback(int event, int x, int y, int flags, void* param);

CvRect box;
bool drawing_box = false;
int minHessian = 400;

void draw_box( IplImage* img, CvRect rect ){
	cvRectangle( img, cvPoint(box.x, box.y), cvPoint(box.x+box.width,box.y+box.height),
				cvScalar(0xff,0x00,0x00) );
}

int main(int argc, char **argv) {
	if(argc != 2) {
		printUsage();
		return -1;
	}
	
	string vidFileName(argv[1]);
	
	CvCapture *tempCap = cvCaptureFromFile(vidFileName.c_str());	
    
    const char* name = "Highlight ROI";
	box = cvRect(-1,-1,0,0);

	IplImage *image = cvQueryFrame(tempCap);
	IplImage *temp = cvCloneImage(image);
	cvNamedWindow(name);
	
	// Set up the callback
	cvSetMouseCallback(name, my_mouse_callback, (void*) image);

	while(true){
		cvCopy(image, temp);
		if(drawing_box) 
			draw_box(temp, box);
		cvShowImage(name, temp);

		if(cvWaitKey(15)==27) break;
	}
	
	cvReleaseImage(&temp);
	cvDestroyWindow(name);
	
	cvReleaseCapture(&tempCap);
	
	CvCapture *capture = cvCaptureFromFile(vidFileName.c_str());
	
	double framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
    double total = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);
	
	Mat descriptors_good;
	
	while(framePos/total < 0.1) {
		cout << framePos/total << endl;
		Mat img(cvQueryFrame(capture), true);
		framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
		
		cout << "Set size." << endl;
		// Crop image.
		Mat newImage(img);
        Rect roi;
        roi.x = box.x;
        roi.y = box.y;
        roi.width = box.width;
        roi.height = box.height;
        
        cout << "Crop." << endl;
        // Crop the original image to the defined ROI
    	Mat frame = newImage(roi);
		
		SurfFeatureDetector detector(minHessian);
		vector<KeyPoint> keypoints_frame;
		detector.detect(frame, keypoints_frame);
		
		SurfDescriptorExtractor extractor;
		Mat descriptors_frame;
		extractor.compute(frame, keypoints_frame, descriptors_frame);
		
		if(descriptors_good.empty()) descriptors_good.push_back(descriptors_frame);
		else {		
			// Find Matches
			FlannBasedMatcher matcher;
			vector<DMatch> matches;
			matcher.match(descriptors_frame, descriptors_good, matches);
		
			double max_dist = 0;
			double min_dist = 100;
		
			for(int i=0; i<matches.size(); i++) {
				double dist = matches[i].distance;
				if(dist < min_dist) min_dist = dist;
				if(dist > max_dist) max_dist = dist;
			}
		
			cout << "Max dist: " << max_dist << endl;
			cout << "Min dist: " << min_dist << endl;
		
			vector<DMatch> new_matches;
		
			for(int i=0; i<matches.size(); i++) {
				if(matches[i].distance > 2*min_dist) {
				//if(matches[i].distance < 0.1) {
					new_matches.push_back(matches[i]);
				}
			}
			
			Mat new_descriptors;
			cout << "Descriptors Found: " << descriptors_frame.rows << endl;
			for(int i=0; i<new_matches.size(); i++) {
				new_descriptors.push_back(descriptors_frame.row(new_matches[i].queryIdx));
			}
			
			cout << "Descriptors Added: " << new_descriptors.rows << endl;
			if (new_descriptors.rows > 0) {
				descriptors_good.push_back(new_descriptors);
			}
			cout << "Descriptors Total: " << descriptors_good.size() << endl;
		}
		
		// Code to draw the points.
		Mat frame_points;
		drawKeypoints(frame, keypoints_frame, frame_points, Scalar::all(-1), DrawMatchesFlags::DEFAULT);
		
		// Display image.
		imshow("SURF", frame_points);
		if((cvWaitKey(10) & 255) == 27) break;
	}
	
	FileStorage outfile(vidFileName + ".feats", FileStorage::WRITE);
	write(outfile, "Descriptors", descriptors_good);
	outfile.release();
	
	
	//cvDestroyWindow("SURF");
    cvReleaseCapture(&capture);
    return 0;
}

// Implement mouse callback
void my_mouse_callback(int event, int x, int y, int flags, void* param){
	IplImage* image = (IplImage*) param;

	switch( event ){
		case CV_EVENT_MOUSEMOVE: 
			if( drawing_box ){
				box.width = x-box.x;
				box.height = y-box.y;
			}
			break;

		case CV_EVENT_LBUTTONDOWN:
			drawing_box = true;
			box = cvRect( x, y, 0, 0 );
			break;

		case CV_EVENT_LBUTTONUP:
			drawing_box = false;
			if( box.width < 0 ){
				box.x += box.width;
				box.width *= -1;
			}
			if( box.height < 0 ){
				box.y += box.height;
				box.height *= -1;
			}
			draw_box( image, box );
			break;
	}
}

/** @function printUsage */
void printUsage() {
	cout << "Usage: cv_collect <vid>" << endl;
}