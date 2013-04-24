#include <cstdio>
#include <iostream>
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

void printUsage();

int minHessian = 400;
Scalar color;

int main(int argc, char **argv) {
    if (argc != 3) {
        printUsage();
        return -1;
    }

    Rect finalRect;
    std::vector<Rect> boundingRects;
    std::vector<Point2f> tlPoints;
    std::vector<Point2f> brPoints;

    string vidFileName(argv[1]);
    string featFileName(argv[2]);

    CvCapture *capture = cvCaptureFromFile(vidFileName.c_str());

    Mat descriptors_file;
    FileStorage infile(featFileName, FileStorage::READ);
    if (infile.isOpened()) {
        read(infile["Descriptors"], descriptors_file);
        infile.release();
    } else {
        std::cout << "Feature file " << featFileName << " does not exist." << std::endl;
        exit(-1);
    }
    std::cout << "Descriptors: " << descriptors_file.size() << std::endl;

    int framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
    int total = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);

    double fps = cvGetCaptureProperty(capture, CV_CAP_PROP_FPS);
    std::cout << "FSP: " << fps << std::endl;
    std::cout << "Total Frames: " << total << std::endl;
    std::cerr << "<slice_probabilities>" << std::endl;
    while ((double)framePos/total < 1.0) {
        std::cout << framePos << " / " << total << std::endl;
//        Mat frame(cvarrToMat(cvQueryFrame(capture)), true);
//        Mat frame(cvarrToMat(cvQueryFrame(capture)));
        Mat frame(cvarrToMat(cvQueryFrame(capture)));
        framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);

        // Check for 1800 frame mark.
        if (framePos != 0 && framePos % 1800 == 0.0) {
            double frameDiameter = sqrt(frame.cols^2 * frame.rows^2);
            double roiDiameter = sqrt(finalRect.width^2 * finalRect.height^2);
            double probability = 1/(roiDiameter/frameDiameter);
            std::cerr << probability << std::endl;
            boundingRects.clear();
            tlPoints.clear();
            brPoints.clear();
        }

        SurfFeatureDetector detector(minHessian);

        std::vector<KeyPoint> keypoints_frame;

        detector.detect(frame, keypoints_frame);

        SurfDescriptorExtractor extractor;

        Mat descriptors_frame;

        extractor.compute(frame, keypoints_frame, descriptors_frame);

        // Find Matches
        FlannBasedMatcher matcher;
        std::vector<DMatch> matches;
        matcher.match(descriptors_frame, descriptors_file, matches);

        double max_dist = 0;
        double min_dist = 100;

        for (int i=0; i<matches.size(); i++) {
            double dist = matches[i].distance;
            if(dist < min_dist) min_dist = dist;
            if(dist > max_dist) max_dist = dist;
        }

        //std::cout << "Max dist: " << max_dist << std::endl;
        //std::cout << "Min dist: " << min_dist << std::endl;

        std::vector<DMatch> good_matches;

        for (int i=0; i<matches.size(); i++) {
            if (matches[i].distance <= 1.5*min_dist) {
                //if (matches[i].distance <= 0.18) {
                good_matches.push_back(matches[i]);
            }
            }

            // Localize object.
            std::vector<Point2f> matching_points;
            std::vector<KeyPoint> keypoints_matches;

            for (int i=0; i<good_matches.size(); i++) {
                keypoints_matches.push_back(keypoints_frame[good_matches[i].queryIdx]);
                matching_points.push_back(keypoints_frame[good_matches[i].queryIdx].pt);
            }

            // Code to draw the points.
            Mat frame_points;
            drawKeypoints(frame, keypoints_matches, frame_points, Scalar::all(-1), DrawMatchesFlags::DEFAULT);

            //Get bounding rectangle.
            if (matching_points.size() != 0) {
                Rect boundRect = boundingRect(matching_points);
                color = Scalar(0, 0, 255); // Blue, Green, Red
                rectangle(frame_points, boundRect.tl(), boundRect.br(), color, 2, 8, 0);

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

                Rect averageRect(tlPoint, brPoint);
                color = Scalar(255, 0, 0); // Blue, Green, Red
                rectangle(frame_points, averageRect.tl(), averageRect.br(), color, 2, 8, 0);
                finalRect = averageRect;
            }

            imshow("SURF", frame_points);
            if(cvWaitKey(15)==27) break;
        }

        std::cerr << "</slice_probabilities>" << std::endl;

        cvDestroyWindow("SURF");
        cvReleaseCapture(&capture);
        return 0;
    }

    /** @function printUsage */
    void printUsage() {
        std::cout << "Usage: cv_surf <vid> <feats>" << std::endl;
    }
