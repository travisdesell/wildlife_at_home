#include "utils.hpp"

using namespace std;
using namespace cv;

double standardDeviation(vector<DMatch> arr, const double mean) {
    double dev=0;
    double inverse = 1.0 / static_cast<double>(arr.size());
    for(unsigned int i=0; i<arr.size(); i++) {
        dev += pow((double)arr[i].distance - mean, 2);
    }
    return sqrt(inverse * dev);
}

int timeToSeconds(const string time) {
    string line;
    vector<string> temp;
    istringstream iss(time);
    while(getline(iss, line, ':')) {
        temp.push_back(line);
    }
    int seconds = 0;
    seconds += atoi(temp[0].c_str())*3600;
    seconds += atoi(temp[1].c_str())*60;
    seconds += atoi(temp[2].c_str());
    return seconds;
}

double getTimeInSeconds() {
    time_t timer;
    struct tm y2k = {0};
    y2k.tm_hour = 0;   y2k.tm_min = 0; y2k.tm_sec = 0;
    y2k.tm_year = 100; y2k.tm_mon = 0; y2k.tm_mday = 1;
    time(&timer);
    return difftime(timer,mktime(&y2k));
}
