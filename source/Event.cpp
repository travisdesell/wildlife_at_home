#include "Event.h"
#include "EventType.h"

void Event::Event() {
}

void Event::Event(EventType *type, int startTime, int endTime) {
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

EventType* getType() {
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

void Event::addKeypoints(cv::Mat keypoints) {
    this->type->addKeypoints(keypoints);
}

int Event::getDescriptors() {
    return this->type->getDescriptors();
}

int Event::getKeypoints() {
    return this->type->getKeypoints();
}
