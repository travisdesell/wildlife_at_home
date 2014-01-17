#ifndef WILDLIFE_SURF_H
#define WILDLIFE_SURF_H

#include <string>
#include <opencv2/core/core.hpp>

class EventType {
    std::string id;
    cv::Mat descriptors;
    cv::Mat points;
    public:
    void EventType(std::string);
    void setId(std::string);
    void setDescriptors(cv::Mat);
    void setPoints(cv::Mat);
    std::string getId();
    cv::Mat getDescriptors;
    cv::Mat getPoints;
};

#endif
