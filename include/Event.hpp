#ifndef EVENT_H
#define EVENT_H

#include "EventType.hpp"

class Event {
	 EventType *type;
	 int startTime;
	 int endTime;
    public:
     void Event();
     void Event(EventType*, int, int);
     void setType(EventType*);
     void setStartTime(int);
     void setEndTime(int);
     EventType* getType();
     int getStartTime();
     int getEndTime();

     void addDescriptors(cv::Mat descriptors);
     void addKeypoints(cv::Mat keypoints);
     cv::Mat getDescriptors();
     cv::Mat getKeypoints();
     std::string getTypeId();
};

#endif
