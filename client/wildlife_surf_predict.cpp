#define GUI
//#define SVM

#include <stdexcept>
#include <vector>
#include <iostream>
#include <fstream>

#ifdef _WIN32
#include <time.h>
#else
#include <sys/time.h>
#endif

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/calib3d/calib3d.hpp>

#include <svm.h>

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
#include "graphics2.h"
#endif

#include "Event.hpp"
#include "EventType.hpp"
#include "VideoType.hpp"
#include "utils.hpp"
#include "boinc_utils.hpp"

using namespace std;
using namespace cv;

/****** PROTOTYPES ******/

void printUsage();
bool readParams(int argc, char** argv);
void calculateFPS();
void updateSHMEM();
bool readConfig(string filename, vector<EventType*> *eventTypes, vector<Event*> *events, int *vidTime, string *species);
void writeMatrix(FileStorage outfile, string id, Mat matrix);
Mat readMatrix(FileStorage infile, string id);
void writeEventsToFile(string filename, vector<EventType*> eventTypes);
vector<KeyPoint> readKeypoints(FileStorage infile, string id);
void writeKeypoints(FileStorage outfile, string id, vector<KeyPoint> keypoints);
void readEventsFromFile(string filename, vector<EventType*> *eventTypes);
bool readCheckpoint(int *checkpointFramePos, vector<EventType*> *eventTypes);
void writeCheckpoint(int framePos, vector<EventType*> eventTypes) throw(runtime_error);

/****** END PROTOTYPES ******/

static const char *EXTRACTORS[] = {"Opponent"};
static const char *DETECOTRS[] = {"Grid", "Pyramid", "Dynamic", "HARRIS"};
static const char *MATCHERS[] = {"FlannBased", "BruteForce", "BruteForce-SL2", "BruteForce-L1", "BruteForce-Hamming", "BruteForce-HammingLUT", "BruteForce-Hamming(2)"};
static int  minHessian = 400;
static double flannThreshold = 3.5;
static bool removeWatermark = true;
static bool removeTimestamp = true;
static int descMatcher = 1;
static string vidFilename, configFilename, modelFilename, descFilename;
static string vidName;
static string species;

// SHMEM
WILDLIFE_SHMEM* shmem = NULL;
unsigned int currentTime = 0;
unsigned int previousTime = 0;
unsigned int frameCount = 0;
unsigned int framePos;
unsigned int totalFrames;
unsigned int featuresCollected = 0;
float averageFeatures = 0;
double fps = 10;

int main(int argc, char **argv) {
    if(!(numeric_limits<float>::is_iec559 || numeric_limits<double>::is_iec559)) {
        cerr << "WARNING: Architecture is not compatible with IEEE floating point standard!" << endl;
    }

    // Local Variables
    descFilename = "descriptors.dat";

    if(!readParams(argc, argv)) {
        printUsage();
        return -1;
    }

    unsigned found = vidFilename.find_last_of("/\\");
    vidName = vidFilename.substr(found+1);

    cerr << "Vid file: " << vidFilename.c_str() << endl;
    cerr << "Vid name: " << vidName.c_str() << endl;
    cerr << "Config file: " << configFilename.c_str() << endl;
    cerr << "Matcher: " << MATCHERS[descMatcher] << endl;
    cerr << "Min Hessian: " << minHessian << endl;
    cerr << "Threshold: " << flannThreshold << " * standard deviation" << endl;

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
    if(!readConfig(configFilename, &eventTypes, &events, &vidTime, &species)) {
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
    if(readCheckpoint(&checkpointFramePos, &eventTypes)) {
        cerr << "Start from checkpoint on frame " << checkpointFramePos << endl;
    } else {
        cerr << "Unsuccessful checkpoint read." << endl << "Starting from beginning of video." << endl;
    }

    capture.set(CV_CAP_PROP_POS_FRAMES, checkpointFramePos);

    framePos = capture.get(CV_CAP_PROP_POS_FRAMES);
    totalFrames = capture.get(CV_CAP_PROP_FRAME_COUNT);

    int frameWidth = capture.get(CV_CAP_PROP_FRAME_WIDTH);
    int frameHeight = capture.get(CV_CAP_PROP_FRAME_HEIGHT);

    VideoType vidType(frameWidth, frameHeight);

    cerr << "Config Filename: '" << configFilename.c_str() << "'" << endl;
    cerr << "Vid Filename: '" << vidFilename.c_str() << "'" << endl;
    cerr << "Current Frame: '" << framePos << "'" << endl;
    cerr << "Frame Count: '" << totalFrames << "'" << endl;

    cerr << "Open SHMEM: " << endl;
    shmem = (WILDLIFE_SHMEM*)boinc_graphics_make_shmem("wildlife_surf_predict", sizeof(WILDLIFE_SHMEM));
    fill(shmem->filename, shmem->filename + sizeof(shmem->filename), 0);
    memcpy(shmem->filename, vidFilename.c_str(), vidFilename.size());
    fill(shmem->species, shmem->species + sizeof(shmem->species), 0);
    memcpy(shmem->species, species.c_str(), species.size());
    updateSHMEM();
    boinc_register_timer_callback(updateSHMEM);
    cerr << "SHMEM opened." << endl;

    Mat globalDescriptors;
    vector<KeyPoint> globalKeypoints;

    while(framePos/totalFrames < 1.0) {
        double fraction_done = (double)framePos/totalFrames;
        cerr << "Fraction done: " << fraction_done << endl;
#ifdef _BOINC_APP_
        boinc_fraction_done(fraction_done);
#ifdef GUI
        int key = waitKey(1);
#endif
        if(boinc_time_to_checkpoint()) {
            cerr << "boinc_time_to_checkpoint encountered, checkpointing at frame " << framePos << endl;
            writeCheckpoint(framePos, eventTypes);
            boinc_checkpoint_completed();
        }
#endif
        Mat frame;
        capture >> frame;
        framePos = capture.get(CV_CAP_PROP_POS_FRAMES);
        calculateFPS();
        //cout << "FPS: " << fps << endl;

        // TODO This should be in a setting file to allow for differnet frame
        // rates.
        if(framePos % 10 == 0) {
            vidTime++; //Increment video time every 10 frames.
        }

        //Ptr<FeatureDetector> detector = new SurfFeatureDetector(minHessian);
        SurfFeatureDetector *detector = new SurfFeatureDetector(minHessian);
        vector<KeyPoint> frameKeypoints;
        Mat mask = vidType.getMask();
        detector->detect(frame, frameKeypoints, mask);
        delete(detector);

        //Ptr<DescriptorExtractor> extractor = new SurfDescriptorExtractor();
        SurfDescriptorExtractor *extractor = new SurfDescriptorExtractor();
        Mat frameDescriptors;
        extractor->compute(frame, frameKeypoints, frameDescriptors);
        delete(extractor);

        globalDescriptors.push_back(frameDescriptors);
        globalKeypoints.insert(globalKeypoints.end(), frameKeypoints.begin(), frameKeypoints.end());
        cout << globalDescriptors.size() << endl;

        //Run points through SVM
        /*
        svm_node *nodes = new svm_node[frameDescriptors.cols];
        for(int i=0; i < frameDescriptors.rows; i++) {
            for (int j=0; j < frameDescriptors.cols; j++) {
                nodes[j].index = j;
                nodes[j].value = double(frameDescriptors.at<float>(i, j));
            }
            double val = -1;
            //double val = svm_predict(model, nodes); // Slow line!
            if(val == -1) {
                negativeKeypoints.push_back(frameKeypoints[i]);
            } else {
                cout << "Val: " << val << endl;
                positiveKeypoints.push_back(frameKeypoints[i]);
            }
        }
        delete(nodes);
        */

#ifdef GUI
        // Draw points on frame.
        Mat pointsFrame = frame;
        vidType.drawZones(pointsFrame, Scalar(0, 0, 100));
        drawKeypoints(frame, frameKeypoints, pointsFrame, Scalar::all(-1), DrawMatchesFlags::DEFAULT); // Draw random colors
        //drawKeypoints(frame, positiveKeypoints, pointsFrame, Scalar(0, 255, 0), DrawMatchesFlags::DEFAULT);
        //drawKeypoints(frame, negativeKeypoints, pointsFrame, Scalar(0, 0, 255), DrawMatchesFlags::DEFAULT);

        // Display image.
        imshow("Wildlife@Home", pointsFrame);
        if((cvWaitKey(10) & 255) == 27) break;
#endif
    }


    vector<KeyPoint> positiveKeypoints;

    //Run points through SVM
    svm_model *model = svm_load_model(modelFilename.c_str());
    svm_node *nodes = new svm_node[globalDescriptors.cols];
    for(int i=0; i < globalDescriptors.rows; i++) {
        for (int j=0; j < globalDescriptors.cols; j++) {
            nodes[j].index = j;
            nodes[j].value = double(globalDescriptors.at<float>(i, j));
        }
        double val = svm_predict(model, nodes); // Slow line!
        cout << "Val: " << val << endl;
        if(val == -1) {
            //negativeKeypoints.push_back(globalKeypoints[i]);
        } else {
            cout << "Positive Val: " << val << endl;
            positiveKeypoints.push_back(globalKeypoints[i]);
        }
    }
    delete(nodes);
    svm_free_and_destroy_model(&model);

#ifdef GUI
        // Draw points on frame.
        Mat frame;
        capture >> frame;
        Mat pointsFrame = frame;
        vidType.drawZones(pointsFrame, Scalar(0, 0, 100));
        //drawKeypoints(frame, frameKeypoints, pointsFrame, Scalar::all(-1), DrawMatchesFlags::DEFAULT); // Draw random colors
        drawKeypoints(frame, positiveKeypoints, pointsFrame, Scalar(0, 255, 0), DrawMatchesFlags::DEFAULT);
        //drawKeypoints(frame, negativeKeypoints, pointsFrame, Scalar(0, 0, 255), DrawMatchesFlags::DEFAULT);

        // Display image.
        imshow("Wildlife@Home", pointsFrame);
        cvWaitKey(10);
#endif

    capture.release();

    cerr << "<event_ids>" << endl;
    for(int i=0; i<eventTypes.size(); i++) {
        cerr << eventTypes[i]->getId().c_str() << endl;
    }
    cerr << "</event_ids>" << endl;
    writeEventsToFile(descFilename, eventTypes);

#ifdef GUI
    cvDestroyWindow("Wildlife@Home");
#endif

#ifdef _BOINC_APP_
    boinc_finish(0);
#endif
    cerr << "Finished!" << endl;
    return 0;
}

void writeCheckpoint(int framePos, vector<EventType*> eventTypes) throw(runtime_error) {
    string checkpointFilename = getBoincFilename(vidName + ".checkpoint");
    writeEventsToFile(checkpointFilename, eventTypes);
    FileStorage outfile(checkpointFilename, FileStorage::APPEND);
    if(!outfile.isOpened()) {
        throw runtime_error("Checkpoint file did not open");
    }

    outfile << "CURRENT_FRAME" << framePos;
    outfile.release();
}

bool readCheckpoint(int *checkpointFramePos, vector<EventType*> *eventTypes) {
    cerr << "Reading checkpoint..." << endl;
    string checkpointFilename = getBoincFilename(vidName + ".checkpoint");
    FileStorage infile(checkpointFilename, FileStorage::READ);
    if(!infile.isOpened()) return false;
    infile["CURRENT_FRAME"] >> *checkpointFramePos;
    cerr << "CURRENT_FRAME:" << " " << *checkpointFramePos << endl;
    infile.release();

    readEventsFromFile(checkpointFilename, eventTypes);
    cerr << "Done reading checkpoint." << endl;
    return true;
}

void readEventsFromFile(string filename, vector<EventType*> *eventTypes) {
    FileStorage infile(filename, FileStorage::READ);
    try {
        for(vector<EventType*>::iterator it = eventTypes->begin(); it != eventTypes->end(); ++it) {
            (*it)->read(infile);
        }
    } catch(const exception &ex) {
        cerr << "[ERROR CODE 0] readEventsFromFile: " << ex.what() << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
    infile.release();
}

void writeEventsToFile(string filename, vector<EventType*> eventTypes) {
    FileStorage outfile(filename, FileStorage::WRITE);
#ifdef SVM
    ofstream svmfile("svm.dat");
#endif
    try {
        for(vector<EventType*>::iterator it = eventTypes.begin(); it != eventTypes.end(); ++it) {
            (*it)->writeDescriptors(outfile);
            (*it)->writeKeypoints(outfile);
#ifdef SVM
            (*it)->writeForSVM(svmfile, (*it)->getId(), true);
#endif
        }
    } catch(const exception ex) {
        cerr << "[ERROR CODE 1] writeEventsToFile: " << ex.what() << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
    outfile.release();
}

bool readConfig(string filename, vector<EventType*> *eventTypes, vector<Event*> *events, int *vidTime, string *species) {
    cerr << "Reading config file: " << filename.c_str() << endl;
    string line, eventId, startTime, endTime;
    ifstream infile;
    infile.open(filename.c_str());
    getline(infile, line);
    *species = line;
    getline(infile, line);
    *vidTime = timeToSeconds(line);
    while(getline(infile, eventId, ',')) {
        Event *newEvent = new Event();
        EventType *eventType = NULL;
        for(vector<EventType*>::iterator it = eventTypes->begin(); it != eventTypes->end(); ++it) {
            cerr << "Event name: '" <<  (*it)->getId().c_str() << endl;
            if((*it)->getId().compare(eventId) == 0) {
                eventType = *it;
                break;
            }
        }
        if(eventType == NULL) {
            eventType = new EventType(eventId);
            eventTypes->push_back(eventType);
        }
        if(!getline(infile, startTime, ',') || !getline(infile, endTime)) {
            cerr << "Error: Malformed config file!" << endl;
            return false;
        }
        newEvent->setType(eventType);
        newEvent->setStartTime(timeToSeconds(startTime));
        newEvent->setEndTime(timeToSeconds(endTime));
        events->push_back(newEvent);
    }
    infile.close();
    return true;
}

void updateSHMEM() {
    if(shmem == NULL) return;
    //cout << fixed << "Time: " << getTimeInSeconds() << endl;
    shmem->update_time = getTimeInSeconds();
    shmem->fraction_done = boinc_get_fraction_done();
    shmem->cpu_time = boinc_worker_thread_cpu_time();
    boinc_get_status(&shmem->status);
    shmem->fps = fps;
    shmem->feature_count = featuresCollected;
    shmem->feature_average = featuresCollected/(float)framePos;;
    shmem->frame = framePos;
}

void calculateFPS() {
    frameCount++;

#ifdef WIN32
    SYSTEMTIME st;
    GetSystemTime(&st);
    currentTime = st.wSecond * 1000000 + st.wMilliseconds * 1000;
#else
    struct timeval time;
    gettimeofday(&time, NULL);
    currentTime = time.tv_sec * 1000000 + time.tv_usec;
#endif

    if(previousTime == 0) previousTime = currentTime;
    unsigned int timeInterval = currentTime - previousTime;

    if(timeInterval > 1000000) {
        fps = frameCount/(timeInterval/1000000.0);
        previousTime = currentTime;
        frameCount = 0;
    }
}

bool readParams(int argc, char** argv) {
    for(int i=1; i<argc; i++) {
        if(i < argc) {
            if(string(argv[i]) == "--video" || string(argv[i]) == "-v") {
                if(i+1 < argc) vidFilename = argv[++i];
            } else if(string(argv[i]) == "--config" || string(argv[i]) == "-c") {
                if(i+1 < argc) configFilename = argv[++i];
            } else if(string(argv[i]) == "--svm_model" || string(argv[i]) == "-s") {
                if(i+1 < argc) modelFilename = argv[++i];
            } else if(string(argv[i]) == "--desc" || string(argv[i]) == "-d") {
                if(i+1 < argc) descFilename = argv[++i];
            } else if(string(argv[i]) == "--matcher" || string(argv[i]) == "-m") {
                if(i+1 < argc) descMatcher = atoi(argv[++i]);
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
    if(vidFilename.empty() || configFilename.empty() || modelFilename.empty()) return false;
    else return true;
}

// TODO This should be genereated automatically somehow... probably from the
// readParams function.
void printUsage() {
	cout << "Usage: wildlife_predict -v <video> -c <config> -s <model> [-d <descriptor output>] [-f <feature output>] [-m <matcher>] [-h <min hessian>] [-t <feature match threshold>] [-watermark] [-timestamp]" << endl;

}
