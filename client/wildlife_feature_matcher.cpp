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
    cerr << "\t" << binary_name << " <species = [tern | plover | grouse]> <video file> <features file>" << endl;
}

void read_descriptors_and_keypoints(FileStorage &infile, string filename, Mat &descriptors, vector<KeyPoint> &keypoints) {
    if (infile.isOpened()) {
        read(infile["unmatched_descriptors"], descriptors);
        read(infile["unmatched_keypoints"], keypoints);
        infile.release();
    } else {
        cout << "Could not open '" << filename << "' for reading." << endl;
        exit(-1);
    }   
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
    if (argc != 4) {
        printUsage(argv[0]);
        return 1;
    }

    vector<Point2f> tlPoints;
    vector<Point2f> brPoints;

    string species = string(argv[1]);
    string video_filename = string(argv[2]);
    string feature_filename = string(argv[3]);

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

    vector<KeyPoint> target_keypoints;
    Mat target_descriptors;

    FileStorage feature_file(feature_filename, FileStorage::READ);
    read_descriptors_and_keypoints(feature_file, feature_filename, target_descriptors, target_keypoints);

    cout << "target_keypoints.size(): " << target_keypoints.size() << endl;

    current_frame = 0;

    while (current_frame < total_frames) {
        if (current_frame % 100 == 0) {
            cout << "FPS: " << current_frame/((double)time(NULL) - (double)start_time) << ", " << current_frame << "/" << total_frames << " frames. " << endl;
//            break;
        }

        Mat frame(cvarrToMat(cvQueryFrame(capture)));
        current_frame = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);

        vector<KeyPoint> frame_keypoints, matched_keypoints;
        Mat frame_descriptors, matched_descriptors;

        get_keypoints_and_descriptors(frame, frame_descriptors, frame_keypoints);

        float min_min_distance = 128.0;
        float max_min_distance = 0.0;
        float min_max_distance = 128.0;
        float max_max_distance = 0.0;

        for (int i = 0; i < target_descriptors.rows; i++) {
            //get the minimum euclidian distance of each descriptor in the current frame from each common_descriptor
            float min_euclidian_distance = 128.0;
            float max_euclidian_distance = 0.0;

            for (int j = 0; j < frame_descriptors.rows; j++) {
                float euclidian_distance = 0;

                for (int k = 0; k < target_descriptors.cols; k++) {
                    float tmp = target_descriptors.at<float>(i,k) - frame_descriptors.at<float>(j,k);
                    min_euclidian_distance += tmp * tmp;
                }   
                euclidian_distance = sqrt(min_euclidian_distance);

                if (euclidian_distance < min_euclidian_distance) min_euclidian_distance = euclidian_distance;
                if (euclidian_distance > max_euclidian_distance) max_euclidian_distance = euclidian_distance;
            }   

            cout << "\tmin_euclidian_distance[" << i << "]: " << min_euclidian_distance << ", max_euclidian_distance: " << max_euclidian_distance << endl;

            if (min_min_distance > min_euclidian_distance) min_min_distance = min_euclidian_distance;
            if (max_min_distance < min_euclidian_distance) max_min_distance = min_euclidian_distance;
            if (min_max_distance > max_euclidian_distance) min_max_distance = max_euclidian_distance;
            if (max_max_distance < max_euclidian_distance) max_max_distance = max_euclidian_distance;

            if (min_euclidian_distance <= 1.6) {
                int x = target_keypoints[i].pt.x;
                int y = target_keypoints[i].pt.y;

                matched_keypoints.push_back( target_keypoints[i] );
                matched_descriptors.push_back( target_descriptors.row(i) );

                cout << "matched keypoint with x: " << x << ", y: " << y << endl;
            }   
        }   

		// Code to draw the points.
        Mat frame_with_target_keypoints;
        drawKeypoints(frame, matched_keypoints, frame_with_target_keypoints, Scalar::all(-1), DrawMatchesFlags::DEFAULT);

        rectangle(frame_with_target_keypoints,  Point(remove_rect_1_x1, remove_rect_1_y1), Point(remove_rect_1_x2, remove_rect_1_y2), Scalar(0, 0, 255), 1, 8, 0);
        rectangle(frame_with_target_keypoints,  Point(remove_rect_2_x1, remove_rect_2_y1), Point(remove_rect_2_x2, remove_rect_2_y2), Scalar(0, 0, 255), 1, 8, 0);

		imshow("SURF - Frame", frame_with_target_keypoints);
		if(cvWaitKey(15) == 27) break;
	}

    cvDestroyWindow("SURF");

    cvReleaseCapture(&capture);

    return 0;
}
