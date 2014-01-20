#ifndef VIDEO_TYPE_HEADER
#define VIDEO_TYPE_HEADER

#include <vector>
#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

using namespace std;

class VideoType {
	 int width;
	 int height;
     cv::Rect watermarkRect;
     cv::Rect timestampRect;

     void setWatermarkRect(cv::Point, cv::Point);
     void setTimestampRect(cv::Point, cv::Point);

    public:
     VideoType(int width, int height);

     void setWidth(int);
     void setHeight(int);
     int getWidth();
     int getHeight();
     cv::Rect getWatermarkRect();
     cv::Rect getTimestampRect();

     vector<cv::KeyPoint> getCleanKeypoints(vector<cv::KeyPoint> keypoints);

    private:
     void loadType();
};

#endif
