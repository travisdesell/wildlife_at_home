#ifndef WILDLIFE_GRAPHICS_HEADER
#define WILDLIFE_GRAPHICS_HEADER

#include <opencv2/core/core.hpp>

struct UC_SHMEM {
    double update_time;
    double fraction_done;
    double cpu_time;
    BOINC_STATUS status;
    cv::Mat image;
};

#endif
