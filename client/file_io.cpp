#include <iostream>
using std::cout;
using std::endl;

#include <string>
using std::string;

#include <vector>
using std::vector;


#include <opencv2/core/core.hpp>
using cv::FileStorage;

#include <opencv2/features2d/features2d.hpp>
using cv::KeyPoint;

#include <opencv2/core/mat.hpp>
using cv::Mat;

#include "file_io.hpp"


void read_descriptors_and_keypoints(string filename, Mat &descriptors, vector<KeyPoint> &keypoints) {
    FileStorage infile(filename, FileStorage::READ);

    if (infile.isOpened()) {
        read(infile["descriptors"], descriptors);
        read(infile["keypoints"], keypoints);
        infile.release();
    } else {
        cout << "Could not open '" << filename << "' for reading." << endl;
        exit(-1);
    }   
}

