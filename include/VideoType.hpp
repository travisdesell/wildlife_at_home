#ifndef VIDEO_TYPE_HEADER
#define VIDEO_TYPE_HEADER

#include <vector>
#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

using namespace std;

class VideoType {
	 int width;
	 int height;
     cv::Mat mask;
     cv::Rect watermarkRect;
     cv::Rect timestampRect;

     // Cache Vars
     bool updateMask;

    public:
     VideoType(int width, int height);

     int getWidth();
     int getHeight();
     cv::Rect getWatermarkRect();
     cv::Rect getTimestampRect();

     cv::Mat getMask();
     void drawZones(cv::Mat &frame, const cv::Scalar &color);

    private:
     void setWatermarkRect(cv::Point, cv::Point);
     void setTimestampRect(cv::Point, cv::Point);
     void fillRectOnMat(cv::Mat &mat, cv::Rect rect);
     void loadType();
};

#endif
