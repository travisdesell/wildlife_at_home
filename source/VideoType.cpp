#include "VideoType.hpp"

using namespace std;

// Accessors
VideoType::VideoType(const cv::Size size) {
    this->size = size;

    // Cache Vars
    this->updateMask = true;

    loadType();
}


int VideoType::getWidth() {
    return this->size.width;
}

int VideoType::getHeight() {
    return this->size.height;
}

cv::Size VideoType::getSize() {
    return this->size;
}

cv::Rect VideoType::getWatermarkRect() {
    return this->watermarkRect;
}

cv::Rect VideoType::getTimestampRect() {
    return this->timestampRect;
}

// Functions

cv::Mat VideoType::getMask() {
    if(this->updateMask) {
        this->mask = cv::Mat(this->size.height, this->size.width, CV_8UC1, cv::Scalar(1));
        const static int CV_FILLED = -1;
        fillRectOnMat(this->mask, timestampRect);
        fillRectOnMat(this->mask, watermarkRect);
        this->updateMask = false;
    }
    return this->mask;
}

void VideoType::drawZones(cv::Mat &frame, const cv::Scalar &color) {
    rectangle(frame, timestampRect, color);
    rectangle(frame, watermarkRect, color);
}

// Private

void VideoType::setWatermarkRect(const cv::Point topLeft, const cv::Point bottomRight) {
    this->watermarkRect = cv::Rect(topLeft, bottomRight);
}

void VideoType::setTimestampRect(const cv::Point topLeft, const cv::Point bottomRight) {
    this->timestampRect = cv::Rect(topLeft, bottomRight);
}

void VideoType::fillRectOnMat(cv::Mat &mat, const cv::Rect rect) {
    cv::Point tl = rect.tl();
    cv::Point br = rect.br();
    for(int x=tl.x; x<br.x; x++) {
        for(int y=tl.y; y<br.y; y++) {
            mask.at<char>(y, x) = 0;
        }
    }
}

// TODO This needs to be fixed to load in from a config file.
void VideoType::loadType() {
    if(size.width == 704 && size.height == 480) {
        cv::Point watermarkTopLeft(12, 12);
        cv::Point watermarkBottomRight(90, 55);
        cv::Point timestampTopLeft(520, 415);
        cv::Point timestampBottomRight(680, 470);
        setWatermarkRect(watermarkTopLeft, watermarkBottomRight);
        setTimestampRect(timestampTopLeft, timestampBottomRight);
    } else if(size.width == 352 && size.height == 240) {
        cv::Point watermarkTopLeft(12, 12);
        cv::Point watermarkBottomRight(90, 55);
        cv::Point timestampTopLeft(240, 190);
        cv::Point timestampBottomRight(335, 230);
        setWatermarkRect(watermarkTopLeft, watermarkBottomRight);
        setTimestampRect(timestampTopLeft, timestampBottomRight);
    }
    this->updateMask = true;
}
