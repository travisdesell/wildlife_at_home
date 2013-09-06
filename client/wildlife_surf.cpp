#include <cmath>
#include <cstdio>
#include <fstream>
#include <iostream>

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

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <opencv2/imgproc/imgproc.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/calib3d/calib3d.hpp>
//
//#include <opencv2/core/types_c.h>
//#include <opencv2/imgproc/imgproc_c.h>
#include <opencv2/core/mat.hpp>
#include <opencv2/core/core_c.h>
#include <opencv2/highgui/highgui_c.h>


using namespace std;
using namespace cv;

#define GUI

void write_checkpoint();
bool read_checkpoint();
int skipNFrames(CvCapture* capture, int n);
void printUsage();

int minHessian = 400;
Scalar color;
int currentFrame = 0;
vector<double> percentages;
string checkpoint_filename;

string vidFileName;
string checkpointVidFileName;
string featFileName;
string checkpointFeatFileName;

int main(int argc, char **argv) {
    if (argc != 3) {
        printUsage();
        return -1;
    }

    cv::Rect finalRect;
    vector<cv::Rect> boundingRects;
    vector<Point2f> tlPoints;
    vector<Point2f> brPoints;

    vidFileName = string(argv[1]);
    featFileName = string(argv[2]);

    // Get BOINC resolved file paths.
#ifdef _BOINC_APP_
    string resolved_vid_path;
    string resolved_feat_path;
    int retval = boinc_resolve_filename_s(vidFileName.c_str(), resolved_vid_path);
    if (retval) {
        cerr << "Error, could not open file: '" << vidFileName << "'" << endl;
        cerr << "Resolved to: '" << resolved_vid_path << "'" << endl;
        return false;
    }
    vidFileName = resolved_vid_path;

    retval = boinc_resolve_filename_s(featFileName.c_str(), resolved_feat_path);
    if (retval) {
        cerr << "Error, could not open file: '" << featFileName << "'" << endl;
        cerr << "Resolved to : '" << resolved_feat_path << "'" << endl;
        return false;
    }
    featFileName = resolved_feat_path;
#endif

    CvCapture *capture = cvCaptureFromFile(vidFileName.c_str());

    Mat descriptors_file;
    FileStorage infile(featFileName, FileStorage::READ);
    if (infile.isOpened()) {
        read(infile["Descriptors"], descriptors_file);
        infile.release();
    } else {
        cout << "Feature file " << featFileName << " does not exist." << endl;
        exit(-1);
    }
    cout << "Descriptors: " << descriptors_file.size() << endl;

    int framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
    int total = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);

    double fps = cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);
    int framesInThreeMin = (int)fps*180;

#ifdef _BOINC_APP_  
    boinc_init();
#endif

    cerr << "Video File Name: " << vidFileName << endl;
    cerr << "Feature File Name: " << featFileName << endl; 
    cerr << "Frames Per Second: " << fps << endl;
    cerr << "Frame Count: " << total << endl;
    cerr << "Number of Frames in Three Minutes: " << framesInThreeMin << endl;
//    cerr << "<slice_probabilities>" << endl;

    checkpoint_filename = "checkpoint.txt";

    if(read_checkpoint()) {
        if(checkpointVidFileName.compare(vidFileName)!=0 || checkpointFeatFileName.compare(featFileName)!=0) {
            cerr << "Checkpointed video or feature filename was not the same as given video or feature filename... Restarting" << endl;
        } else {
            cerr << "Continuing from checkpoint..." << endl;
        }
    } else {
        cerr << "Unsuccessful checkpoint read" << endl << "Starting from beginning of video" << endl;
    }

    skipNFrames(capture, percentages.size() * framesInThreeMin);
    framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
    cerr << "Starting at Frame: " << framePos << endl;

    long start_time = time(NULL);

    while ((double)framePos/total < 1.0) {

        if (framePos % 10 == 0) {
            cout << "FPS: " << framePos/((double)time(NULL) - (double)start_time) << endl;
        }

        //cout << framePos/total << endl;
        Mat frame(cvarrToMat(cvQueryFrame(capture)));
        framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);

        SurfFeatureDetector detector(minHessian);

        vector<KeyPoint> keypoints_frame;

        detector.detect(frame, keypoints_frame);

        SurfDescriptorExtractor extractor;

        Mat descriptors_frame;

        extractor.compute(frame, keypoints_frame, descriptors_frame);

        // Find Matches
        FlannBasedMatcher matcher;
        vector<DMatch> matches;
        matcher.match(descriptors_frame, descriptors_file, matches);

        double max_dist = 0;
        double min_dist = 100;

        for (int i=0; i<matches.size(); i++) {
            double dist = matches[i].distance;
            if(dist < min_dist) min_dist = dist;
            if(dist > max_dist) max_dist = dist;
        }

        //cout << "Max dist: " << max_dist << endl;
        //cout << "Min dist: " << min_dist << endl;

        vector<DMatch> good_matches;

        for (int i=0; i<matches.size(); i++) {
            if (matches[i].distance <= 1.5*min_dist) {
                //if (matches[i].distance <= 0.18) {
                good_matches.push_back(matches[i]);
            }
            }

            // Localize object.
            vector<Point2f> matching_points;
            vector<KeyPoint> keypoints_matches;

            for (int i=0; i<good_matches.size(); i++) {
                keypoints_matches.push_back(keypoints_frame[good_matches[i].queryIdx]);
                matching_points.push_back(keypoints_frame[good_matches[i].queryIdx].pt);
            }

            // Code to draw the points.
            Mat frame_points;
#ifdef GUI
            drawKeypoints(frame, keypoints_matches, frame_points, Scalar::all(-1), DrawMatchesFlags::DEFAULT);
#endif

            //Get bounding rectangle.
            if (matching_points.size() != 0) {
                cv::Rect boundRect = boundingRect(matching_points);

#ifdef GUI
                color = Scalar(0, 0, 255); // Blue, Green, Red
                rectangle(frame_points, boundRect.tl(), boundRect.br(), color, 2, 8, 0);
#endif

                boundingRects.push_back(boundRect);
                tlPoints.push_back(boundRect.tl());
                brPoints.push_back(boundRect.br());
            }

            if (tlPoints.size() != 0) {
                Mat tlMean;
                Mat brMean;
                reduce(tlPoints, tlMean, CV_REDUCE_AVG, 1);
                reduce(brPoints, brMean, CV_REDUCE_AVG, 1);
                Point2f tlPoint(tlMean.at<float>(0,0), tlMean.at<float>(0,1));
                Point2f brPoint(brMean.at<float>(0,0), brMean.at<float>(0,1));

                cv::Rect averageRect(tlPoint, brPoint);

#ifdef GUI
                color = Scalar(255, 0, 0); // Blue, Green, Red
                rectangle(frame_points, averageRect.tl(), averageRect.br(), color, 2, 8, 0);
#endif

                finalRect = averageRect;
            }

            // Check for 1800 frame mark.
            if (framePos != 0 && framePos % 1800 == 0.0) {
                double frameDiameter = sqrt(pow((double)frame.cols, 2) * pow((double)frame.rows, 2));
                double roiDiameter = sqrt(pow((double)finalRect.width, 2) * pow((double)finalRect.height, 2));
                double probability = 1-(roiDiameter/frameDiameter);
                percentages.push_back(probability);
#ifndef _BOINC_APP_
                cout << probability << endl;
#endif
                boundingRects.clear();
                tlPoints.clear();
                brPoints.clear();
            }

            // Update percent completion and look for checkpointing request.
#ifdef _BOINC_APP_
            boinc_fraction_done((double)framePos/total);

            if ( boinc_time_to_checkpoint() ) {
                cerr << "checkpointing" << endl;
                write_checkpoint();
                boinc_checkpoint_completed();
            }
#endif

#ifdef GUI
            imshow("SURF", frame_points);
            if(cvWaitKey(15)==27) break;
#endif
        }

        cerr << "<slice_probabilities>" << endl;
        for (int i=0; i<percentages.size(); i++) cerr << percentages[i] << endl;
        cerr << "</slice_probabilities>" << endl;

#ifdef GUI
        cvDestroyWindow("SURF");
#endif

        cvReleaseCapture(&capture);

#ifdef _BOINC_APP_
        boinc_finish(0);
#endif
        return 0;
    }

void write_checkpoint() {
	#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if (retval) {
        cerr << "Couldn't resolve file name..." << endl;
        return;
    }
    checkpoint_filename = resolved_path;
    #endif

    ofstream checkpoint_file(checkpoint_filename.c_str());

    if (!checkpoint_file.is_open()) {
        cerr << "Checkpoint file not open..." << endl;
        return;
    }

    checkpoint_file << "VIDEO_FILE_NAME: " << vidFileName << endl;
    checkpoint_file << "FEAT_FILE_NAME: " << featFileName << endl;
    checkpoint_file << "PROBABILITIES: " << percentages.size() << endl;

    for (int i=0; i<percentages.size(); i++) {
        checkpoint_file << percentages[i] << endl;
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
    checkpoint_filename = resolved_path;
    #endif

    ifstream checkpoint_file(checkpoint_filename.c_str());
    if (!checkpoint_file.is_open()) return false;

    string s;
    checkpoint_file >> s >> checkpointVidFileName;
    if (s.compare("VIDEO_FILE_NAME:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'VIDEO_FILE_NAME'" << endl;

		#ifdef _BOINC_APP_
        boinc_finish(1);
		#endif
        exit(1);
    }
    
    checkpoint_file >> s >> checkpointFeatFileName;
    if (s.compare("FEAT_FILE_NAME:") != 0) {
        cerr << "ERROR: malformed checkpoint! could not read 'FEAT_FILE_NAME'" << endl;

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
    for(int i=0; i<intervals; i++) {
        checkpoint_file >> current;
        percentages.push_back(current);
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

int skipNFrames(CvCapture* capture, int n) {
    for(int i = 0; i < n; ++i) {
        if(cvQueryFrame(capture) == NULL) {
        	return i+1;
        }
    }
	return n;
}

void printUsage() {
    std::cout << "Usage: cv_surf <vid> <feats>" << std::endl;
}
