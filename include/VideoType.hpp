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
     VideoType(const cv::Size);

     int getWidth();
     int getHeight();
     cv::Rect getWatermarkRect();
     cv::Rect getTimestampRect();

     cv::Mat getMask();
     void drawZones(cv::Mat &frame, const cv::Scalar &color);

    private:
     void setWatermarkRect(const cv::Point, const cv::Point);
     void setTimestampRect(const cv::Point, const cv::Point);
     void fillRectOnMat(cv::Mat &mat, const cv::Rect rect);
     void loadType();
};

#endif
