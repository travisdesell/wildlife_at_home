#ifndef BOINC_UTILS_HEADER
#define BOINC_UTILS_HEADER

#include <iostream>
#include <stdexcept>

#ifdef _BOINC_APP_
#ifdef _WIN32
#include "boinc_win.h"
#include "str_util.h"
#endif

#include "diagnostics.h"
#include "util.h"
#include "filesys.h"
#include "boinc_api.h"
#include "mfile.h"
#endif

struct WILDLIFE_SHMEM {
    double update_time;
    double fraction_done;
    double cpu_time;
    double fps;
    BOINC_STATUS status;
    unsigned int frame;
    unsigned int feature_count;
    float feature_average;
    char species[256];
    char filename[256];
};

std::string getBoincFilename(std::string filename) throw(std::runtime_error);

#endif
