#ifndef EVENT_TYPE_HEADER
#define EVENT_TYPE_HEADER

#include <stdexcept>
#include <string>
#include <vector>
#include <fstream>

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

using namespace std;

class EventType {
    std::string id;
    cv::Mat descriptors;
    vector<cv::KeyPoint> keypoints;
    public:
    EventType(std::string);
    void setId(std::string);
    void setDescriptors(cv::Mat);
    void setKeypoints(vector<cv::KeyPoint> keypoints);
    std::string getId();
    cv::Mat getDescriptors();
    vector<cv::KeyPoint> getKeypoints();

    void addDescriptors(cv::Mat descriptors);
    void addKeypoints(vector<cv::KeyPoint> keypoints);
    void read(cv::FileStorage infile) throw(runtime_error);
    void write(cv::FileStorage outfile) throw(runtime_error);
    void writeForSVM(ofstream &outfile, string label) throw(runtime_error);
};

#endif
