#ifndef VIDEO_TYPE_H
#define VIDEO_TYPE_H

#include <opencv2/core/core.hpp>

class VideoType {
	 int width;
	 int height;
     cv::Rect watermarkedRect;
     cv::Rect timestampRect;

     void setWatermarkRect(cv::Point, cv::Point);
     void setTimestampRect(cv::Point, cv::Point);

    public:
     void VideoType(int, int);
     void setWidth(int);
     void setHeight(int);
     int getWidth();
     int getHeight();
     cv::Rect getWatermarkRect();
     cv::Rect getTimestampRect();
};

#endif
