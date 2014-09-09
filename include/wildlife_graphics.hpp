#ifndef WILDLIFE_GRAPHICS_H
#define WILDLIFE_GRAPHICS_H

#include <opencv2/core/core.hpp>
#include <boinc_api.h>

struct UC_SHMEM {
    double update_time;
    double fraction_done;
    double cpu_time;
    BOINC_STATUS status;
    cv::Mat image;
};

#endif
