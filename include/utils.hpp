#ifndef UTILS_HEADER
#define UTILS_HEADER

#include <vector>
#include <iostream>
#include <ctime>

#include <opencv2/nonfree/features2d.hpp>

double standardDeviation(std::vector<cv::DMatch> arr, const double mean);
int timeToSeconds(const std::string time);
double getTimeInSeconds();

#endif
