#include <string>
#include <opencv2/core/core.hpp>

#include "EventType.hpp"

// Accessors

void EventType::EventType(std::string id) {
    this->id = id;
}

void EventType::setId(std::string id) {
    this->id = id;
}

void EventType::setDescriptors(cv::Mat descriptors) {
    this->descriptors = descriptors;
}

void EventType::setKeypoints(cv::Mat keypoints) {
    this->keypoints = ketpoints;
}

std::string EventType::getId() {
    return id;
}

cv::Mat EventType::getDescriptors() {
    return descriptors;
}

cv::Mat EventType::getKeypoints() {
    return keypoints;
}

// Functions

void EventType::addDescriptors(cv::Mat descriptors) {
    this->descriptors.push_back(descriptors);
}

void EventType::addKeypoints(cv::Mat keypoints) {
    this->keypoints.push_back(keypoints);
}
