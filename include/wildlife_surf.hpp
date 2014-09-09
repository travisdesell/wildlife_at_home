#ifndef WILDLIFE_SURF_H
#define WILDLIFE_SURF_H

#include <string>
#include <opencv2/core/core.hpp>

struct EventType {
    std::string id;
    cv::Mat descriptors;
};

#endif
