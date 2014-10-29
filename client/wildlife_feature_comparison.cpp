#include <iostream>
using std::cerr;
using std::cout;
using std::endl;

#include <vector>
using std::vector;

#include <opencv2/core/core.hpp>
using cv::Mat;

#include <opencv2/features2d/features2d.hpp>
using cv::KeyPoint;

#include "file_io.hpp"

using namespace std;
using namespace cv;

int minHessian = 400;
int currentFrame = 0;

void printUsage(char *binary_name) {
    cerr << "Usage:" << endl;
    cerr << "\t" << binary_name << " <feature file 1> <feature file 2> <outfile>" << endl;
}

int main(int argc, char **argv) {
    if (argc != 4) {
        printUsage(argv[0]);
        return 1;
    }

    vector<KeyPoint> absence_keypoints, presence_keypoints;
    Mat absence_descriptors, presence_descriptors;

    string absence_filename = string(argv[1]);
    string presence_filename = string(argv[2]);
    string output_filename = string(argv[3]);

    read_descriptors_and_keypoints(absence_filename, absence_descriptors, absence_keypoints);
    cout << "absence_descriptors: " << absence_descriptors.size() << endl;
    cout << "absence_keypoints: " << absence_keypoints.size() << endl;

    read_descriptors_and_keypoints(presence_filename, presence_descriptors, presence_keypoints);
    cout << "presence_descriptors: " << presence_descriptors.size() << endl;
    cout << "presence_keypoints: " << presence_keypoints.size() << endl;

    vector<KeyPoint> unmatched_presence_keypoints;
    Mat unmatched_presence_descriptors;

    float min_min_distance = 128.0;
    float max_min_distance = 0.0;
    float min_max_distance = 128.0;
    float max_max_distance = 0.0;

    for (int i = 0; i < presence_descriptors.rows; i++) {
        //get the minimum euclidian distance of each descriptor in the current frame from each common_descriptor
        float min_euclidian_distance = 128.0;
        float max_euclidian_distance = 0.0;

        for (int j = 0; j < absence_descriptors.rows; j++) {
            float euclidian_distance = 0;

            for (int k = 0; k < presence_descriptors.cols; k++) {
                float tmp = presence_descriptors.at<float>(i,k) - absence_descriptors.at<float>(j,k);
                min_euclidian_distance += tmp * tmp;
            }   
            euclidian_distance = sqrt(min_euclidian_distance);

            if (euclidian_distance < min_euclidian_distance) min_euclidian_distance = euclidian_distance;
            if (euclidian_distance > max_euclidian_distance) max_euclidian_distance = euclidian_distance;
        }   

//        cout << "\tmin_euclidian_distance[" << i << "]: " << min_euclidian_distance << ", max_euclidian_distance: " << max_euclidian_distance << endl;

        if (min_min_distance > min_euclidian_distance) min_min_distance = min_euclidian_distance;
        if (max_min_distance < min_euclidian_distance) max_min_distance = min_euclidian_distance;
        if (min_max_distance > max_euclidian_distance) min_max_distance = max_euclidian_distance;
        if (max_max_distance < max_euclidian_distance) max_max_distance = max_euclidian_distance;

        if (min_euclidian_distance > 1.6) {
            int x = presence_keypoints[i].pt.x;
            int y = presence_keypoints[i].pt.y;

            unmatched_presence_keypoints.push_back( presence_keypoints[i] );
            unmatched_presence_descriptors.push_back( presence_descriptors.row(i) );

            cout << "added keypoint with x: " << x << ", y: " << y << endl;
        }   
    }   
    cout << "size: " << unmatched_presence_keypoints.size() << ", min_min_distance: " << min_min_distance << ", max_min_distance: " << max_min_distance << ", min_max_distance: " << min_max_distance << ", max_max_distance: " << max_max_distance << endl;


    FileStorage outfile(output_filename, FileStorage::WRITE);
    if (outfile.isOpened()) {
        outfile << "unmatched_descriptors" << unmatched_presence_descriptors;
        outfile << "unmatched_keypoints" << unmatched_presence_keypoints;
        outfile.release();
    } else {
        cout << "Could not open '" << output_filename << "' for writing." << endl;
        exit(-1);
    }

    return 0;
}
