#include <string>
#include <vector>

#include <opencv2/core/core.hpp>

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
    for(int i=0; i<keypoints.size(); i++) {
        this->keypoints.push_back(keypoints.at(i));
    }
}
