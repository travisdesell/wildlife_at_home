#include <vector>
#include <iostream>
#include <fstream>

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/calib3d/calib3d.hpp>

#ifdef _BOINC_APP_
#ifdef _WIN32
#include "boinc_win.h"
#include "str_util.h"
#endif

#include "diagnostics.h"
#include "util.h"
#include "filesys.h"
#include "boinc_api.h"
#include "mfile.h"
#endif

#include "Event.hpp"
#include "EventType.hpp"
#include "VideoType.hpp"

using namespace std;
using namespace cv;

/****** PROTOTYPES ******/

void printUsage();
bool readParams(int argc, char** argv);
int skipFrames(VideoCapture capture, int numFrames);
string getBoincFilename(string filename);
int timeToSeconds(string time);
double standardDeviation(vector<DMatch> arr, double mean);
bool readConfig(string filename, vector<EventType*> eventTypes, vector<Event*> events, int *vidTime);
void writeMatrix(FileStorage outfile, string id, Mat matrix);
Mat readMatrix(FileStorage infile, string id);
void writeEventsToFile(string filename, vector<EventType*> eventTypes);
void readEventsFromFile(string filename, vector<EventType*> &eventTypes);
bool readCheckpoint(int *checkpointFramePos, vector<EventType*> eventTypes);
void writeCheckpoint(int framePos, vector<EventType*> eventTypes);

/****** END PROTOTYPES ******/

static int  minHessian = 400;
static double flannThreshold = 3.5;
static bool removeWatermark = true;
static bool removeTimestamp = true;
static string vidFilename, configFilename, descFilename, featFilename;

int main(int argc, char **argv) {
    // Local Variables
    string configFilename;
    string vidFilename;
    string descFilename = "descriptors.dat";
    string featFilename = "features.dat";

    if(!readParams(argc, argv)) {
        printUsage();
        return -1;
    }

    cerr << "Vid file: " << vidFilename.c_str() << endl;
    cerr << "Config file: " << configFilename.c_str() << endl;
    cerr << "Min Hessian: " << minHessian << endl;
    cerr << "Flann Threshold: " << flannThreshold << " * standard deviation" << endl;

#ifdef _BOINC_APP_
    cout << "Boinc enabled." << endl;
    cerr << "Resolving boinc file paths." << endl;
    configFilename = getBoincFilename(configFilename);
    vidFilename = getBoincFilename(vidFilename);
    descFilename = getBoincFilename(descFilename);
#endif

    int vidTime;
    vector<EventType*> eventTypes;
    vector<Event*> events;
    if(!readConfig(configFilename, eventTypes, events, &vidTime)) {
        return false; //Error occurred.
    }
    cerr << "Events: " << events.size() << endl;
    cerr << "Event Types: " << eventTypes.size() << endl;

    VideoCapture capture(vidFilename.c_str());
    if(!capture.isOpened()) {
        cerr << "Failed to open '" << vidFilename.c_str() << "'" << endl;
        return false;
    }

#ifdef _BOINC_APP_
    boinc_init();
#endif

    int checkpointFramePos;
    if(readCheckpoint(&checkpointFramePos, eventTypes)) {
        cerr << "Start from checkpoint..." << endl;
    } else {
        cerr << "Unsuccessful checkpoint read." << endl << "Starting from beginning of video." << endl;
    }

    skipFrames(capture, checkpointFramePos);

    int framePos = capture.get(CV_CAP_PROP_POS_FRAMES);
    int total = capture.get(CV_CAP_PROP_FRAME_COUNT);

    int frameWidth = capture.get(CV_CAP_PROP_FRAME_WIDTH);
    int frameHeight = capture.get(CV_CAP_PROP_FRAME_HEIGHT);

    cerr << "Config Filename: '" << configFilename.c_str() << "'" << endl;
    cerr << "Vid Filename: '" << vidFilename.c_str() << "'" << endl;
    cerr << "Current Frame: '" << framePos << "'" << endl;
    cerr << "Frame Count: '" << total << "'" << endl;

    while(framePos/total < 1.0) {
#ifdef _BOINC_APP_
        boinc_fraction_done(framePos/total);
#ifdef GUI
        int key = waitKey(1);
#endif
        if(boinc_time_to_checkpoint()) {
            cerr << "boinc_time_to_checkpoint encountered, checkpointing" << endl;
            writeCheckpoint(framePos, eventTypes);
            boinc_checkpoint_completed();
        }
#endif
        Mat frame;
        capture >> frame;
        framePos = capture.get(CV_CAP_PROP_POS_FRAMES);

        // TODO This should be in a setting file to allow for differnet frame
        // rates.
        if(framePos % 10 == 0) vidTime++; //Increment video time every 10 frames.

        SurfFeatureDetector detector(minHessian);
        vector<KeyPoint> frameKeypoints;
        detector.detect(frame, frameKeypoints);

        // Remove keypoints from selected areas of video. (Watermark and
        // Timestamp)
        VideoType type(frameWidth, frameHeight);
        frameKeypoints = type.getCleanKeypoints(frameKeypoints);

        SurfDescriptorExtractor extractor;
        Mat frameDescriptors;
        extractor.compute(frame, frameKeypoints, frameDescriptors);

        // Add distinct descriptors and keypoints to active events.
        int activeEvents = 0;
        for(vector<Event*>::iterator it = events.begin(); it != events.end(); ++it) {
            if(vidTime >= (*it)->getStartTime() && vidTime <= (*it)->getEndTime()) {
                activeEvents++;
                if((*it)->getDescriptors().empty()) {
                    (*it)->addDescriptors(frameDescriptors);
                    (*it)->addKeypoints(frameKeypoints);
                } else {
                    // Find Matches
                    FlannBasedMatcher matcher;
                    vector<DMatch> matches;
                    matcher.match(frameDescriptors, (*it)->getDescriptors(), matches);

                    double totalDist = 0;
                    double maxDist = 0;
                    double minDist = 100;

                    for(int i=0; i<matches.size(); i++) {
                        double dist = matches[i].distance;
                        totalDist += dist;
                        if(dist < minDist) minDist = dist;
                        if(dist > maxDist) maxDist = dist;
                    }

                    double avgDist = totalDist/matches.size();
                    double stdDev = standardDeviation(matches, avgDist);

                    cerr << "Max dist: " << maxDist << endl;
                    cerr << "Min dist: " << minDist << endl;
                    cerr << "Avg dist: " << avgDist << endl;
                    cerr << "Avg + " << flannThreshold << " * stdDev: " << avgDist + flannThreshold * stdDev << endl;

                    vector<DMatch> newMatches;
                    for(int i=0; i<matches.size(); i++) {
                        if(matches[i].distance > avgDist + (flannThreshold * stdDev)) {
                            newMatches.push_back(matches[i]);
                        }
                    }

                    Mat newDescriptors;
                    Mat newKeypoints;
                    cerr << (*it)->getTypeId().c_str() << " descriptors found: " << frameDescriptors.rows << endl;
                    for(int i=0; i<newMatches.size(); i++) {
                        newDescriptors.push_back(frameDescriptors.row(newMatches[i].queryIdx));
                        newKeypoints.push_back(frameKeypoints.at(newMatches[i].queryIdx));
                    }
                    cerr << (*it)->getTypeId().c_str() << " descriptors added: " << newDescriptors.rows << endl;
                    if(newDescriptors.rows > 0) {
                        (*it)->addDescriptors(newDescriptors);
                        (*it)->addKeypoints(newKeypoints);
                    }
                    cerr << (*it)->getTypeId().c_str() << " descriptors: " << (*it)->getDescriptors().size() << endl;
                }
            }
        }
        if(activeEvents == 0) {
            cerr << "[ERROR] There are no active events! (Problem with expert classification.)" << endl;
#ifdef _BOINC_APP_
            boinc_finish(1);
#endif
            exit(1);
        }

#ifdef GUI
        // Draw points on frame.
        Mat pointsFrame = frame;
        // TODO Add code to draw rectangles around removed points.
        drawKeypoints(frame, frameKeypoints, pointsFrame, Scalar::all(-1), DrawMatchesFlags::DEFAULT);

        // Display image.
        imshow("SURF", pointsFrame);
        if((cvWaitKey(10) & 255) == 27) break;
#endif
    }
    capture.release();

    cerr << "<event_ids>" << endl;
    for(int i=0; i<eventTypes.size(); i++) {
        cerr << eventTypes[i]->getId().c_str() << endl;
    }
    cerr << "</event_ids>" << endl;
    writeEventsToFile(descFilename, eventTypes);

#ifdef GUI
    cvDestroyWindow("SURF");
#endif

#ifdef _BOINC_APP_
    boinc_finish(0);
#endif
    cerr << "Finished!" << endl;
    return 0;
}

// TODO Check if desc can also be read from checkpoint file.
void writeCheckpoint(int framePos, vector<EventType*> eventTypes) {
#ifdef _BOINC_APP_
    string checkpointFilename = getBoincFilename("checkpoint.dat");
    string checkpointDescFilename = getBoincFilename("checkpoint.desc");
#endif
    ofstream checkpointFile(checkpointFilename.c_str());
    if(!checkpointFile.is_open()) {
        cerr << "ERROR: Checkpoint file did not open." << endl;
        return;
    }

    checkpointFile << "CURRENT_FRAME: " << framePos << endl;
    checkpointFile.close();

    writeEventsToFile(checkpointDescFilename, eventTypes);
}

// TODO Check if desc can also be read from checkpoint file.
bool readCheckpoint(int *checkpointFramePos, vector<EventType*> eventTypes) {
    cerr << "Reading checkpoint..." << endl;
#ifdef _BOINC_APP_
    string checkpointFilename = getBoincFilename("checkpoint.dat");
    string checkpointDescFilename = getBoincFilename("checkpoint.desc");
#endif
    ifstream checkpointFile(checkpointFilename.c_str());
    if(!checkpointFile.is_open()) return false;

    string s;
    checkpointFile >> s >> *checkpointFramePos;
    cerr << s.c_str() << " " << checkpointFramePos << endl;
    if(s.compare("CURRENT_FRAME:") != 0) {
        cerr << "ERROR: malformed checkpoint file! Could not read 'CURRENT_FRAME'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    readEventsFromFile(checkpointDescFilename, eventTypes);
    cerr << "Done reading checkpoint." << endl;
    return true;
}

// TODO Add a way to read keypoints out as well as descriptors.
void readEventsFromFile(string filename, vector<EventType*> &eventTypes) {
    FileStorage infile(filename, FileStorage::READ);
    for(vector<EventType*>::iterator it = eventTypes.begin(); it != eventTypes.end(); ++it) {
        (*it)->addDescriptors(readMatrix(infile, (*it)->getId()));
    }
    infile.release();
}

// TODO Add a way to write keypoints out as well as descriptors.
void writeEventsToFile(string filename, vector<EventType*> eventTypes) {
    FileStorage outfile(filename, FileStorage::WRITE);
    for(vector<EventType*>::iterator it = eventTypes.begin(); it != eventTypes.end(); ++it) {
        writeMatrix(outfile, (*it)->getId(), (*it)->getDescriptors());
    }
    outfile.release();
}

Mat readMatrix(FileStorage infile, string id) {
    Mat matrix;
    if(infile.isOpened()) {
        read(infile[id], matrix);
    } else {
        cerr << "ERROR: File is not open." << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
    return matrix;
}

void writeMatrix(FileStorage outfile, string id, Mat matrix) {
    if(outfile.isOpened()) {
        outfile << id << matrix;
    } else {
        cerr << "ERROR: File is not open." << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
}

bool readConfig(string filename, vector<EventType*> eventTypes, vector<Event*> events, int *vidTime) {
    cerr << "Reading config file: " << filename.c_str() << endl;
    string line, eventId, startTime, endTime;
    ifstream infile;
    infile.open(filename.c_str());
    getline(infile, line);
    *vidTime = timeToSeconds(line);
    while(getline(infile, eventId, ',')) {
        Event *newEvent = new Event();
        EventType *eventType = NULL;
        for(vector<EventType*>::iterator it = eventTypes.begin(); it != eventTypes.end(); ++it) {
            cerr << "Event name: '" <<  (*it)->getId().c_str() << endl;
            if((*it)->getId().compare(eventId) == 0) {
                eventType = *it;
                break;
            }
        }
        if(eventType == NULL) {
            eventType = new EventType(eventId);
            eventTypes.push_back(eventType);
        }
        if(!getline(infile, startTime, ',') || getline(infile, endTime)) {
            cerr << "Error: Malformed config file!" << endl;
            return false;
        }
        newEvent->setType(eventType);
        newEvent->setStartTime(timeToSeconds(startTime));
        newEvent->setEndTime(timeToSeconds(endTime));
        events.push_back(newEvent);
    }
    infile.close();
    return true;
}

double standardDeviation(vector<DMatch> arr, double mean) {
    double dev=0;
    double inverse = 1.0 / static_cast<double>(arr.size());
    for(unsigned int i=0; i<arr.size(); i++) {
        dev += pow((double)arr[i].distance - mean, 2);
    }
    return sqrt(inverse * dev);
}

int timeToSeconds(string time) {
    vector<string> temp;
    istringstream iss;
    while(getline(iss, time, ':')) {
        temp.push_back(time);
    }
    int seconds = 0;
    seconds += atoi(temp[0].c_str())*3600;
    seconds += atoi(temp[1].c_str())*60;
    seconds += atoi(temp[2].c_str());
    return seconds;
}

// TODO Throw error..
string getBoincFilename(string filename) {
    string resolvedPath;
    if(boinc_resolve_filename_s(filename.c_str(), resolvedPath)) {
        cerr << "Error, could not open file: '" << filename.c_str() << "'" << endl;
        cerr << "Resolved to: '" << filename.c_str() << "'" << endl;
        return NULL; //Throw error here.
    }
    return resolvedPath;
}

//TODO Check for a faster way to skip frames.
int skipFrames(VideoCapture capture, int n) {
    Mat frame;
    for(int i=0; i<n; i++) {
        capture >> frame;
        if(frame.empty()) { // Check for end of file.
            return i+1;
        }
    }
    return n;
}

bool readParams(int argc, char** argv) {
    for(int i=1; i<argc; i++) {
        if(i < argc) {
            if(string(argv[i]) == "--video" || string(argv[i]) == "-v") {
                if(i+1 < argc) vidFilename = argv[++i];
            } else if(string(argv[i]) == "--config" || string(argv[i]) == "-c") {
                if(i+1 < argc) configFilename = argv[++i];
            } else if(string(argv[i]) == "--desc" || string(argv[i]) == "-d") {
                if(i+1 < argc) descFilename = argv[++i];
            } else if(string(argv[i]) == "--feat" || string(argv[i]) == "-f") {
                if(i+1 < argc) featFilename = argv[++i];
            } else if(string(argv[i]) == "--hessian" || string(argv[i]) == "-h") {
                if(i+1 < argc) minHessian = atoi(argv[++i]);
            } else if(string(argv[i]) == "--threshold" || string(argv[i]) == "-t") {
                if(i+1 < argc) flannThreshold = atoi(argv[++i]);
            } else if(string(argv[i]) == "--watermark") {
                removeWatermark = false;
            } else if(string(argv[i]) == "--timestamp") {
                removeTimestamp = false;
            }
        } else {
            cout << "Parameter has no matching value." << endl;
            return false;
        }
    }
    if(vidFilename.empty() || configFilename.empty()) return false;
    else return true;
}

// TODO This should be genereated automatically somehow... probably from the
// readParams function.
void printUsage() {
	cout << "Usage: wildlife_collect -v <video> -c <config> [-d <descriptor output>] [-f <feature output>] [-h <min hessian>] [-t <feature match threshold>] [-watermark] [-timestamp]" << endl;
}
