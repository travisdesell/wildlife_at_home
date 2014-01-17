#ifndef EVENT_H
#define EVENT_H

#include "EventType.hpp"

class Event {
	 EventType *type;
	 int startTime;
	 int endTime;
    public:
     void Event(EventType *type, int startTime, int endTime);
     void setStartTime(int seconds);
     void setEndTime(int seconds);
     int getStartTime();
     int getEndTime();
};

#endif
