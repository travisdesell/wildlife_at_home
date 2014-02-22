#include <cmath>
#include <cstdio>
#include <fstream>
#include <iostream>
#include <algorithm>
#include <iomanip>

#include <opencv2/core/core.hpp>
#include <opencv2/features2d/features2d.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <opencv2/imgproc/imgproc.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/calib3d/calib3d.hpp>
#include <opencv2/legacy/legacy.hpp>

#include <opencv2/core/types_c.h>
#include <opencv2/imgproc/imgproc_c.h>
#include <opencv2/core/mat.hpp>
#include <opencv2/core/core_c.h>
#include <opencv2/highgui/highgui_c.h>

using namespace std;
using namespace cv;

int minHessian = 400;
int currentFrame = 0;

SurfFeatureDetector detector(minHessian);
SurfDescriptorExtractor extractor;

void printUsage(char *binary_name) {
    cerr << "Usage:" << endl;
    cerr << "\t" << binary_name << " <feature detection algorithm = [SURF | SIFT]> <species = [tern | plover | grouse]> <video file>" << endl;
}


void get_keypoints_and_descriptors(const Mat &frame, Mat &descriptors, vector<KeyPoint> &keypoints) {
    detector.detect(frame, keypoints);

    extractor.compute(frame, keypoints, descriptors);

    /*
    cout << "keypoints detected: " << keypoints.size() << endl;
    for (int i = 0; i < keypoints.size(); i++) {
        cout << "\t" << keypoints[i].pt.x << ", " << keypoints[i].pt.y << " -- " << keypoints[i].angle << " : " << keypoints[i].size << " -- " << keypoints[i].response << endl;
        cout << "\t\t(" << descriptors.rows << ", " << descriptors.cols << ") ";
        for (int j = 0; j < descriptors.cols; j++) {
            cout << " " << descriptors.at<float>(i,j);
        }
        cout << endl;
    }
    cout << endl;
    */

}

int main(int argc, char **argv) {
    if (argc != 5) {
        printUsage(argv[0]);
        return 1;
    }

    Rect finalRect;
    vector<cv::Rect> boundingRects;
    vector<Point2f> tlPoints;
    vector<Point2f> brPoints;

    string species = string(argv[2]);
    string video_filename = string(argv[3]);
    string output_filename = string(argv[4]);

    int remove_rect_1_x1, remove_rect_1_x2;
    int remove_rect_1_y1, remove_rect_1_y2;

    int remove_rect_2_x1, remove_rect_2_x2;
    int remove_rect_2_y1, remove_rect_2_y2;

    if (0 == species.compare("plover")) {
        remove_rect_1_x1 = 15;
        remove_rect_1_x2 = 85;
        remove_rect_1_y1 = 15;
        remove_rect_1_y2 = 55;

        remove_rect_2_x1 = 525;
        remove_rect_2_x2 = 675;
        remove_rect_2_y1 = 420;
        remove_rect_2_y2 = 465;
    } else if (0 == species.compare("tern")) {
        remove_rect_1_x1 = 15;
        remove_rect_1_x2 = 85;
        remove_rect_1_y1 = 15;
        remove_rect_1_y2 = 55;

        remove_rect_2_x1 = 245;
        remove_rect_2_x2 = 330;
        remove_rect_2_y1 = 195;
        remove_rect_2_y2 = 225;
    } else if (0 == species.compare("grouse")) {
    } else if (0 == species.compare("robot")) {
        remove_rect_1_x1 = 0;
        remove_rect_1_x2 = 0;
        remove_rect_1_y1 = 0;
        remove_rect_1_y2 = 0;

        remove_rect_2_x1 = 0;
        remove_rect_2_x2 = 0;
        remove_rect_2_y1 = 0;
        remove_rect_2_y2 = 0;
    } else {
        cerr << "Error, unknown species '" << species << "'" << endl;
        exit(1);
    }


    CvCapture *capture = cvCaptureFromFile(video_filename.c_str());

    int current_frame = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
    int total_frames = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);
    int frame_width = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_WIDTH);
    int frame_height = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_HEIGHT);


    double fps = cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);

    cerr << "Video File Name: " << video_filename << endl;
    cerr << "Frames Per Second: " << fps << endl;
    cerr << "Frame Count: " << total_frames << endl;
    cerr << "Video Dimensions: " << frame_width << "x" << frame_height << endl;


    long start_time = time(NULL);

    vector<KeyPoint> common_keypoints, common_keypoints_temp;
    Mat common_descriptors, common_descriptors_temp;

    Mat frame(cvarrToMat(cvQueryFrame(capture)));
    current_frame = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);

    get_keypoints_and_descriptors(frame, common_descriptors, common_keypoints);

    /**
     *  Ignore all keypoints in the rectangle around the changing date/time
     */
    for (int i = 0; i < common_keypoints.size(); i++) {
        int x = common_keypoints[i].pt.x;
        int y = common_keypoints[i].pt.y;

        if ( !(x >= remove_rect_1_x1 && x <= remove_rect_1_x2 && y >= remove_rect_1_y1 && y <= remove_rect_1_y2) &&
             !(x >= remove_rect_2_x1 && x <= remove_rect_2_x2 && y >= remove_rect_2_y1 && y <= remove_rect_2_y2)
           ) {
            common_keypoints_temp.push_back( common_keypoints[i]);
            common_descriptors_temp.push_back( common_descriptors.row(i) );
        } else {
            //            cout << "rejected: " << x << ", " << y << endl;
        }
    }

    common_keypoints = common_keypoints_temp;
    common_descriptors = common_descriptors_temp;

    while (current_frame < total_frames) {
        if (current_frame % 100 == 0) {
            cout << "FPS: " << current_frame/((double)time(NULL) - (double)start_time) << endl;
            break;
        }

        Mat frame(cvarrToMat(cvQueryFrame(capture)));
        current_frame = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);

        vector<KeyPoint> frame_keypoints;
        Mat frame_descriptors;

        get_keypoints_and_descriptors(frame, frame_descriptors, frame_keypoints);

        float min_desc_val = frame_descriptors.at<float>(0, 0);
        float max_desc_val = frame_descriptors.at<float>(0, 0);

        for (int i = 0; i < frame_descriptors.rows; i++) {
            //cout << "\t\tframe_descriptors[" << i << "]: ";

            for (int j = 0; j < frame_descriptors.cols; j++) {
                if (frame_descriptors.at<float>(i, j) < min_desc_val) min_desc_val = frame_descriptors.at<float>(i, j);
                if (frame_descriptors.at<float>(i, j) > max_desc_val) max_desc_val = frame_descriptors.at<float>(i, j);

                //cout << " " << frame_descriptors.at<float>(i,j);
            }

            //cout << endl;
        }
        cout << "\tframe_descriptors(" << frame_descriptors.rows << ", " << frame_descriptors.cols << "): min val: " << min_desc_val << ", max_val: " << max_desc_val << endl;
//        cout << "\tframe_keypoints: " << frame_keypoints.size() << endl;

        FlannBasedMatcher matcher;
        vector<DMatch> matches;
        matcher.match(frame_descriptors, common_descriptors, matches);

        double max_dist = 0;
        double min_dist = 100;
        double avg_dist = 0;

        for (int i = 0; i < matches.size(); i++) {
            double dist = matches[i].distance;
            if (dist < min_dist) min_dist = dist;
            if (dist > max_dist) max_dist = dist;
            avg_dist += dist;
        }
        avg_dist /= matches.size();

        cout << setw(5) << current_frame << "/" << setw(5) << total_frames;
        cout << " - min dist: " << setw(10) << min_dist << ", max dist: " << setw(10) << max_dist << ", avg dist: " << setw(10) << avg_dist;

        for (int i=0; i<matches.size(); i++) {
            if ( matches[i].distance > 0.18 ) {
                //If not a match, add it to the common keypoints
                int x = frame_keypoints[ matches[i].queryIdx ].pt.x;
                int y = frame_keypoints[ matches[i].queryIdx ].pt.y;

                //Filter out keypoints in the rectangle of the date/time
                if ( !(x >= remove_rect_1_x1 && x <= remove_rect_1_x2 && y >= remove_rect_1_y1 && y <= remove_rect_1_y2) &&
                     !(x >= remove_rect_2_x1 && x <= remove_rect_2_x2 && y >= remove_rect_2_y1 && y <= remove_rect_2_y2) ) {
                    common_keypoints.push_back( frame_keypoints[ matches[i].queryIdx ] );
                    common_descriptors.push_back( frame_descriptors.row( matches[i].queryIdx ) );
                } else {
//                    cout << "rejected: " << x << ", " << y << endl;
                }
            }
        }

        cout << ", common keypoints: " << setw(10) << common_keypoints.size() << ", common descriptors: " << setw(10) << common_descriptors.rows << " , frame keypoints: " << setw(10) << frame_keypoints.size() << endl;

		// Code to draw the points.
        Mat frame_with_keypoints_common;
        Mat frame_with_keypoints_frame;
        drawKeypoints(frame, common_keypoints, frame_with_keypoints_common, Scalar::all(-1), DrawMatchesFlags::DEFAULT);
        drawKeypoints(frame, frame_keypoints, frame_with_keypoints_frame, Scalar::all(-1), DrawMatchesFlags::DEFAULT);

        rectangle(frame_with_keypoints_common, Point(remove_rect_1_x1, remove_rect_1_y1), Point(remove_rect_1_x2, remove_rect_1_y2), Scalar(0, 0, 255), 1, 8, 0);
        rectangle(frame_with_keypoints_common, Point(remove_rect_2_x1, remove_rect_2_y1), Point(remove_rect_2_x2, remove_rect_2_y2), Scalar(0, 0, 255), 1, 8, 0);

        rectangle(frame_with_keypoints_frame,  Point(remove_rect_1_x1, remove_rect_1_y1), Point(remove_rect_1_x2, remove_rect_1_y2), Scalar(0, 0, 255), 1, 8, 0);
        rectangle(frame_with_keypoints_frame,  Point(remove_rect_2_x1, remove_rect_2_y1), Point(remove_rect_2_x2, remove_rect_2_y2), Scalar(0, 0, 255), 1, 8, 0);


		imshow("SURF - Common", frame_with_keypoints_common);
		imshow("SURF - Frame", frame_with_keypoints_frame);
		if(cvWaitKey(15)==27) break;
	}

    cvDestroyWindow("SURF");

    cvReleaseCapture(&capture);

    FileStorage outfile(output_filename, FileStorage::WRITE);
    if (outfile.isOpened()) {
        outfile << "common_descriptors" << common_descriptors;
        outfile << "common_keypoints" << common_keypoints;
        outfile.release();
    } else {
        cout << "Could not open '" << output_filename << "' for writing." << endl;
        exit(-1);
    }

    cout << "common descriptors: " << common_descriptors.size() << endl;
    cout << "common keypoints: " << common_keypoints.size() << endl;

    return 0;
}
