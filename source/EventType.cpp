#include <stdexcept>
#include <string>
#include <vector>

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

#include "EventType.hpp"

// Accessors

EventType::EventType(std::string id) {
    this->id = id;
}

void EventType::setId(std::string id) {
   this->id = id;
}

void EventType::setDescriptors(cv::Mat descriptors) {
    this->descriptors = descriptors;
}

void EventType::setKeypoints(vector<cv::KeyPoint> keypoints) {
    this->keypoints = keypoints;
}

std::string EventType::getId() {
    return this->id;
}

cv::Mat EventType::getDescriptors() {
    return this->descriptors;
}

vector<cv::KeyPoint> EventType::getKeypoints() {
    return this->keypoints;
}

// Functions

void EventType::addDescriptors(cv::Mat descriptors) {
    this->descriptors.push_back(descriptors);
}

void EventType::addKeypoints(vector<cv::KeyPoint> keypoints) {
    for(unsigned int i=0; i<keypoints.size(); i++) {
        this->keypoints.push_back(keypoints.at(i));
    }
}

void EventType::read(cv::FileStorage infile) throw(runtime_error) {
    cv::Mat descriptors;
    vector<cv::KeyPoint> keypoints;
    if(infile.isOpened()) {
        cv::read(infile[getId() + "_desc"], descriptors); // infile[getId()] >> descriptors;
        cv::read(infile[getId() + "_pts"], keypoints); // infile[getId()] >> keypoints;
        addDescriptors(descriptors);
        addKeypoints(keypoints);
    } else {
        throw runtime_error("File is not open for reading");
    }
}

void EventType::write(cv::FileStorage outfile) throw(runtime_error) {
    if(outfile.isOpened()) {
        outfile << getId() + "_desc" << getDescriptors();
        outfile << getId() + "_pts" << getKeypoints();
    } else {
        throw runtime_error("File is not open for writing");
    }
}
