#ifndef FILE_IO_HPP
#define FILE_IO_HPP

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

void read_descriptors_and_keypoints(string filename, Mat &descriptors, vector<KeyPoint> &keypoints);

#endif
