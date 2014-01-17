#include "VideoType.hpp"

void VideoType::VideoType(int width, int height) {
    this->width = width;
    this->height = height;
    // Create rects?
}

void VideoType::setWidth(int pixels) {
    this->width = pixels;
}

void VideoType::setHeight(int pixels) {
    this->height = pixels;
}

void VideoType::setWatermarkRect(cv::Point topLeft, cv::Point bottomRight) {
    this->watermarkRect = new cv::Rect(topLeft, bottomRight);
}

void VideoType::setTimestampRect(cv::Point topLeft, cv::Point bottomRight) {
    this->timestampRect = new cv::Rect(topLeft, bottomRight);
}

int VideoType::getWidth() {
    return this->width;
}

int VideoType::getHeight() {
    return this->height;
}

cv::Rect VideoType::getWatermarkRect() {
    return ehis->watermarkRect;
}

cv::Rect VideoType::getTimestampRect() {
    return this->timestampRect;
}

// This needs to be fixed to load in from a config file.
void loadType() {
    if(width == 704 && height ==480) {
        cv::Point watermarkTopLeft(12, 12);
        cv::Point watermarkBottomTight(90, 55);
        cv::Point timestampTopLeft(520, 415);
        cv::Point timestampBottomRight(680, 470);
        setWatermarkRect(watermarkTopLeft, watermarkBottomRight);
        setTimestampRect(timestampTopLeft, timestampBottomRight);
    } else if (width == 352 && height == 240) {
        cv::Point watermarkTopLeft(12, 12);
        cv::Point watermarkBottomRight(90, 55);
        cv::Point timestampTopLeft(240, 190);
        cv::Point timestampBottomRight(335, 230);
        b.watermark_rect = new cv::Rect(watermarkTopLeft, watermarkBottomRight);
        b.timestamp_rect = new cv::Rect(timestampTopLeft, timestampBottomRight);
    }
}
