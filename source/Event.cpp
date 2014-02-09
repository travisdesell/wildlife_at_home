#include "Event.hpp"
#include "EventType.hpp"

Event::Event() {
}

Event::Event(EventType *type, int startTime, int endTime) {
    this->type = type;
    this->startTime = startTime;
    this->endTime = endTime;
}

// Accessors

void Event::setType(EventType *type) {
    this->type = type;
}

void Event::setStartTime(int seconds) {
    this->startTime = seconds;
}

void Event::setEndTime(int seconds) {
    this->endTime = seconds;
}

EventType* Event::getType() {
    return this->type;
}

int Event::getStartTime() {
    return this->startTime;
}

int Event::getEndTime() {
    return this->endTime;
}

// Functions

void Event::addDescriptors(cv::Mat descriptors) {
    this->type->addDescriptors(descriptors);
}

void Event::addKeypoints(vector<cv::KeyPoint> keypoints) {
    this->type->addKeypoints(keypoints);
}

cv::Mat Event::getDescriptors() {
    return this->type->getDescriptors();
}

vector<cv::KeyPoint> Event::getKeypoints() {
    return this->type->getKeypoints();
}

string Event::getTypeId() {
    return this->type->getId();
}
