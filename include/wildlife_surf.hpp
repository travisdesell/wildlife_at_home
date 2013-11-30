#ifndef WILDLIFE_SURF_HEADER
#define WILDLIFE_SURF_HEADER

#include <string>
#include <opencv2/core/core.hpp>

struct EventType {
    std::string id;
    cv::Mat descriptors;
};

#endif
