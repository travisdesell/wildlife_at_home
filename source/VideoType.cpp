#include <vector>

#include <opencv2/core/core.hpp>

#include "VideoType.hpp"

using namespace std;

// Accessors

VideoType::VideoType(int width, int height) {
    this->width = width;
    this->height = height;
    // Create rects?
}

void VideoType::setWidth(int pixels) {
    this->width = pixels;
}

void VideoType::setHeight(int pixels) {
    this->height = pixels;
}

void VideoType::setWatermarkRect(cv::Point topLeft, cv::Point bottomRight) {
    this->watermarkRect = cv::Rect(topLeft, bottomRight);
}

void VideoType::setTimestampRect(cv::Point topLeft, cv::Point bottomRight) {
    this->timestampRect = cv::Rect(topLeft, bottomRight);
}

int VideoType::getWidth() {
    return this->width;
}

int VideoType::getHeight() {
    return this->height;
}

cv::Rect VideoType::getWatermarkRect() {
    return this->watermarkRect;
}

cv::Rect VideoType::getTimestampRect() {
    return this->timestampRect;
}

// Functions

vector<cv::KeyPoint> VideoType::getCleanKeypoints(vector<cv::KeyPoint> keypoints) {
    vector<cv::KeyPoint> cleanPoints;
    for(int i=0; i<keypoints.size(); i++) {
        cv::Point pt = keypoints.at(i).pt;
        bool watermark = true;
        bool timestamp = true;
        if(!watermarkRect.contains(pt)) watermark = false;
        if(!timestampRect.contains(pt)) timestamp = false;
        if(!watermark && !timestamp) cleanPoints.push_back(keypoints.at(i));
    }
    return cleanPoints;
}

// This needs to be fixed to load in from a config file.
void VideoType::loadType() {
    if(width == 704 && height ==480) {
        cv::Point watermarkTopLeft(12, 12);
        cv::Point watermarkBottomRight(90, 55);
        cv::Point timestampTopLeft(520, 415);
        cv::Point timestampBottomRight(680, 470);
        setWatermarkRect(watermarkTopLeft, watermarkBottomRight);
        setTimestampRect(timestampTopLeft, timestampBottomRight);
    } else if(width == 352 && height == 240) {
        cv::Point watermarkTopLeft(12, 12);
        cv::Point watermarkBottomRight(90, 55);
        cv::Point timestampTopLeft(240, 190);
        cv::Point timestampBottomRight(335, 230);
        setWatermarkRect(watermarkTopLeft, watermarkBottomRight);
        setTimestampRect(timestampTopLeft, timestampBottomRight);
    }
}
