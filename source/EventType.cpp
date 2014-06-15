#include "EventType.hpp"

// Accessors

EventType::EventType(const std::string id) {
    this->id = id;
}

void EventType::setId(const std::string id) {
   this->id = id;
}

void EventType::setDescriptors(const cv::Mat descriptors) {
    this->descriptors = descriptors;
}

void EventType::setKeypoints(const vector<cv::Point2f> keypoints) {
    this->keypoints = keypoints;
}

std::string EventType::getId() {
    return this->id;
}

cv::Mat EventType::getDescriptors() {
    return this->descriptors;
}

vector<cv::Point2f> EventType::getKeypoints() {
    return this->keypoints;
}

// Functions

void EventType::addDescriptors(const cv::Mat descriptors) {
    this->descriptors.push_back(descriptors);
}

void EventType::addKeypoints(const vector<cv::Point2f> keypoints) {
    for(unsigned int i=0; i<keypoints.size(); i++) {
        this->keypoints.push_back(keypoints.at(i));
    }
}

void EventType::read(cv::FileStorage infile, cv::Rect_<float> bounds) throw(runtime_error) {
    cv::Mat descriptors, new_descriptors;
    vector<cv::Point2f> keypoints, new_keypoints;
    if(infile.isOpened()) {
        cv::read(infile[getId() + "_desc"], descriptors); // infile[getId()] >> descriptors;
        cv::read(infile[getId() + "_pts"], keypoints); // infile[getId()] >> keypoints;
        if(keypoints.size() > 0) {
            for(int i=0; i<keypoints.size(); i++) {
                if(bounds.contains(keypoints[i])) {
                    new_descriptors.push_back(descriptors.row(i));
                    new_keypoints.push_back(keypoints.at(i));
                }
            }
            addDescriptors(new_descriptors);
            addKeypoints(new_keypoints);
        } else {
            addDescriptors(descriptors);
            addKeypoints(keypoints);
        }
    } else {
        throw runtime_error("File is not open for reading");
    }
}

void EventType::writeDescriptors(cv::FileStorage outfile) throw(runtime_error) {
    if(outfile.isOpened()) {
        outfile << getId() + "_desc" << getDescriptors();
    } else {
        throw runtime_error("File is not open for writing");
    }
}

void EventType::writeKeypoints(cv::FileStorage outfile) throw(runtime_error) {
    if(outfile.isOpened()) {
        outfile << getId() + "_pts" << getKeypoints();
    } else {
        throw runtime_error("File is not open for writing");
    }
}

void EventType::writeForSVM(ofstream &outfile, string label, bool add_keypoints) throw(runtime_error) {
    cv::Mat desc = getDescriptors();
    vector<cv::Point2f> points = getKeypoints();
    if(outfile.is_open()) {
        for(int i=0; i<desc.rows; i++) {
            outfile << label << " ";
            for(int j=0; j<desc.cols; j++) {
                outfile << j+1 << ":" << desc.at<float>(i, j) << " ";
            }
            if(add_keypoints) {
                outfile << desc.cols+1 << ":" << points.at(i).x << " ";
                outfile << desc.cols+2 << ":" << points.at(i).y << " ";
            }
            outfile << endl;
        }
    } else {
        throw runtime_error("File is not open for writing");
    }
}
