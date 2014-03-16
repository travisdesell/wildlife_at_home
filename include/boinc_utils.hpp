#ifndef BOINC_UTILS_HEADER
#define BOINC_UTILS_HEADER

#include <vector>
#include <iostream>
#include <stdexcept>
#include <ctime>

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

#include <opencv2/nonfree/features2d.hpp>

using namespace std;
using namespace cv;

struct WILDLIFE_SHMEM {
    double update_time;
    double fraction_done;
    double cpu_time;
    double fps;
    BOINC_STATUS status;
    unsigned int frame;
    char filename[256];
};

string getBoincFilename(string filename) throw(runtime_error);
double standardDeviation(vector<DMatch> arr, const double mean);
int timeToSeconds(const string time);
double getTimeInSeconds();

#endif
