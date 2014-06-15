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
    vector<cv::Point2f> keypoints;

    public:
    EventType(const std::string);
    void setId(const std::string);
    void setDescriptors(const cv::Mat);
    void setKeypoints(const vector<cv::Point2f> keypoints);
    std::string getId();
    cv::Mat getDescriptors();
    vector<cv::Point2f> getKeypoints();

    void addDescriptors(const cv::Mat descriptors);
    void addKeypoints(const vector<cv::Point2f> keypoints);
    void read(cv::FileStorage infile, const cv::Rect_<float> bounds = cv::Rect_<float>(cv::Point2f(0,0), cv::Point2f(1,1))) throw(runtime_error);
    void writeDescriptors(cv::FileStorage outfile) throw(runtime_error);
    void writeKeypoints(cv::FileStorage outfile) throw(runtime_error);
    void writeForSVM(ofstream &outfile, string label, bool add_keypoints) throw(runtime_error);
};

#endif
