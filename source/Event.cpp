#include "Event.h"
#include "EventType.h"

void Event::Event(EventType *type, int startTime, int endTime) {
    this->type = type;
    this->startTime = startTime;
    this->endTime = endTime;
}

void Event::setStartTime(int seconds) {
    this->startTime = seconds;
}

void Event::setEndTime(int seconds) {
    this->endTime = seconds;
}

int Event::get_start_time() {
    return startTime;
}

int Event::getEndTime() {
    return endTime;
}
