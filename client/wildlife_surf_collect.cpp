#include <vector>
#include <set>
#include <sstream>
#include <cstdio>
#include <fstream>
#include <iostream>
#include <cstring>
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

using namespace std;
using namespace cv;

struct EventType {
	string name;
	Mat descriptors;
};

struct Event {
	 EventType *type;
	 int start_time;
	 int end_time;
};

void write_checkpoint();
bool read_checkpoint();
void write_descriptors(string, Mat);
void write_events(string, vector<Event>);
Mat read_descriptors(string);
int skip_frames(CvCapture*, int);
void printUsage();
double standardDeviation(vector<DMatch>, double);
int timeToSeconds(string);
vector<Event> readConfigFile(string, int*);

string checkpoint_filename;
string configFileName;
string checkpointConfigFileName;
string vidFileName;
string checkpointVidFileName;
int framePos;
int checkpointFramePos = 0;
float total;
int vidTime;

int minHessian = 400;
vector<EventType> event_types;
vector<Event> events;

int main(int argc, char **argv) {
	if(argc != 3) {
		printUsage();
		return -1;
	}

    configFileName = argv[1];
    vidFileName = argv[2];

#ifdef _BOINC_APP_
    cout << "Boinc enabled." << endl;
    string resolved_config_path;
    string resolved_vid_path;
    int retval = boinc_resolve_filename_s(configFileName.c_str(), resolved_config_path);
    if (retval) {
        cerr << "Error, could not open file: '" << configFileName << "'" << endl;
        cerr << "Resolved to: '" << resolved_config_path << "'" << endl;
        return false;
    }
    configFileName = resolved_config_path;

    retval = boinc_resolve_filename_s(vidFileName.c_str(), resolved_vid_path);
    if (retval) {
        cerr << "Error, could not open file: '" << vidFileName << "'" << endl;
        cerr << "Resolved to: '" << resolved_vid_path << "'" << endl;
        return false;
    }
    vidFileName = resolved_vid_path;
#endif

	events = readConfigFile(configFileName, &vidTime);
	cout << events.size() << endl;
	cout << event_types.size() << endl;

	CvCapture *capture = cvCaptureFromFile(vidFileName.c_str());

#ifdef _BOINC_APP_
    boinc_init();
#endif

    checkpoint_filename = "checkpoint.txt";

    if(read_checkpoint()) {
        cout << "This shouldn't print!" << endl;
        if(checkpointConfigFileName.compare(configFileName)!=0 || checkpointVidFileName.compare(vidFileName)!=0) {
            cerr << "Checkpointed video or feature filename was not the same as given video or feature filename... Restarting" << endl;
        } else {
            cerr << "Contuning from checkpoint..." << endl;
        }
    } else {
        cerr << "Unseccessful checkpoint read." << endl << "Starting from beginning of video." << endl;
    }

    skip_frames(capture, checkpointFramePos);

    framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);
    total = cvGetCaptureProperty(capture, CV_CAP_PROP_FRAME_COUNT);

    cerr << "Config File Name: " << configFileName << endl;
    cerr << "Vid File Name: " << vidFileName << endl;
    cerr << "Current Frame: " << framePos << endl;
    cerr << "Frame Count: " << total << endl;

    checkpoint_filename = "checkpoint.txt";

	// Loop through all video frames.
	while(framePos/total < 1.0) {
		cout << "Percent complete: " << framePos/total*100 << endl;
#ifdef _BONC_APP_
        boinc_fraction_done(framePos/total);
#endif
		Mat img(cvQueryFrame(capture), true);
		framePos = cvGetCaptureProperty(capture, CV_CAP_PROP_POS_FRAMES);

		// Increment video time every 10 frames.
		if(framePos % 10 == 0) vidTime++;
		cout << "Video time: " << vidTime << endl;

    	Mat frame = img;

		SurfFeatureDetector detector(minHessian);
		vector<KeyPoint> keypoints_frame;
		detector.detect(frame, keypoints_frame);

		SurfDescriptorExtractor extractor;
		Mat descriptors_frame;
		extractor.compute(frame, keypoints_frame, descriptors_frame);

		// Add distinct features to active events.
		int activeEvents = 0;
		for(vector<Event>::iterator it = events.begin(); it != events.end(); ++it) {
			if(vidTime >= it->start_time && vidTime <= it->end_time) {
				activeEvents++;
				if (it->type->descriptors.empty()) {
					it->type->descriptors.push_back(descriptors_frame);
				} else {
					// Find Matches
					FlannBasedMatcher matcher;
					vector<DMatch> matches;
					matcher.match(descriptors_frame, it->type->descriptors, matches);

					double total_dist = 0;
					double max_dist = 0;
					double min_dist = 100;

					for(int i=0; i<matches.size(); i++) {
						double dist = matches[i].distance;
						total_dist += dist;
						if(dist < min_dist) min_dist = dist;
						if(dist > max_dist) max_dist = dist;
					}

					double avg_dist = total_dist/matches.size();
					double stdDev = standardDeviation(matches, avg_dist);
					cout << "Max dist: " << max_dist << endl;
					cout << "Avg dist: " << avg_dist << endl;
					cout << "Min dist: " << min_dist << endl;
					cout << "Avg + 3.5*stdDev: " << avg_dist + 3.5*stdDev << endl;

					vector<DMatch> new_matches;

					for(int i=0; i<matches.size(); i++) {
						if(matches[i].distance > avg_dist+(3.5*stdDev)) {
							new_matches.push_back(matches[i]);
						}
					}

					Mat new_descriptors;
					cout << it->type->name << " descriptors found: " << descriptors_frame.rows << endl;
					for(int i=0; i<new_matches.size(); i++) {
						new_descriptors.push_back(descriptors_frame.row(new_matches[i].queryIdx));
					}

					cout << it->type->name << " descriptors added: " << new_descriptors.rows << endl;
					if (new_descriptors.rows > 0) {
						it->type->descriptors.push_back(new_descriptors);
					}
					cout << it->type->name << " descriptors: " << it->type->descriptors.size() << endl;
                }
            }
        }
        cout << "Active Events: " << activeEvents << "/" << events.size() << endl;

#ifdef GUI
        // Code to draw the points.
        Mat frame_points;
        drawKeypoints(frame, keypoints_frame, frame_points, Scalar::all(-1), DrawMatchesFlags::DEFAULT);

        // Display image.
        imshow("SURF", frame_points);
        if((cvWaitKey(10) & 255) == 27) break;
#endif
    }

    write_events("events.desc", events);

#ifdef GUI
    cvDestroyWindow("SURF");
#endif
    cvReleaseCapture(&capture);

#ifdef CHART
    // create google chart
    unsigned int total_descriptors = descriptors_good.rows;
    unsigned int first_frame_descriptors = feature_counts[0];
    ostringstream oss;
    vector<unsigned int>::iterator it = feature_counts.begin();
    while(it != feature_counts.end()) {
    	oss << 100*((float)*it-first_frame_descriptors)/(total_descriptors-first_frame_descriptors) << ",";
    	it++;
    }

    cout << "http://chart.googleapis.com/chart?"
    << "cht=lc"
    << "&"
    << "chxt=x,y"
    << "&"
    << "chs=700x400"
    << "&"
    << "chdl=Feature Count"
    << "&"
    << "chls=1"
    << "&"
    << "chtt=Features+Over+Time"
    << "&"
    << "chxr=1,"
    << first_frame_descriptors
    << ","
    << total_descriptors
    << "&"
    << "chd=t:"
    << oss.str()
    << endl;
#endif

#ifdef _BOINC_APP_
    boinc_finish(0);
#endif
    cout << "Finished!" << endl;
    return 0;
}

/** @function write_checkpoint **/
void write_checkpoint() {
#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if(retval) {
        cerr << "Couldn't resolved file name..." << endl;
        return;
    }
    checkpoint_filename = resolved_path;
#endif

    ofstream checkpoint_file(checkpoint_filename.c_str());
    if(!checkpoint_file.is_open()) {
        cerr << "Checkpoint file not open..." << endl;
        return;
    }

    checkpoint_file << "CONFIG_FILE_NAME: " << configFileName << endl;
    checkpoint_file << "VIDEO_FILE_NAME: " << vidFileName << endl;
    checkpoint_file << "DESCRIPTOR_FILE: " << event_types.size() << endl;

    string filename = "events.desc";
    write_events(filename, events);

    checkpoint_file << endl;
    checkpoint_file.close();
}


/** @function read_checkpoint **/
bool read_checkpoint() {
#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if(retval) {
        return false;
    }
    checkpoint_filename = resolved_path;
#endif

    ifstream checkpoint_file(checkpoint_filename.c_str());
    if(!checkpoint_file.is_open()) return false;

    string s;
    checkpoint_file >> s >> checkpointConfigFileName;
    if(s.compare("CONFIG_FILE_NAME:") != 0 ) {
        cerr << "ERROR: malformed checkpoint! could not read 'CONFIG_FILE_NAME'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    checkpoint_file >> s >> checkpointVidFileName;
    if(s.compare("VID_FILE_NAME:") != 0 ) {
        cerr << "ERROR: malformed checkpoint! could not read 'VID_FILE_NAME'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    checkpoint_file >> s >> checkpointFramePos;
    if(s.compare("CURRENT_FRAME:") != 0 ) {
        cerr << "ERROR: malformed checkpoint! could not read 'CURRENT_FRAME'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    int descriptor_files;
    checkpoint_file >> s >> descriptor_files;
    if(s.compare("DESCRIPTOR_FILES:") != 0 ) {
        cerr << "ERROR: malformed checkpoint! could not read 'DESCRIPTOR_FILES:'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    string current;
    for(int i=0; i<descriptor_files; i++) {
        checkpoint_file >> current;
        string filename = checkpointVidFileName + "." + current + ".desc";
        EventType newType;
        newType.name = current;
        newType.descriptors = read_descriptors(filename);
        event_types.push_back(newType);
        if(!checkpoint_file.good()) {
            cerr << "ERROR: malformed checkpoint! not enough event types present" << endl;
#ifdef _BOINC_APP_
            boinc_finish(1);
#endif
            exit(1);
        }
    }
    return true;
}

/** @function write_descriptors **/
void write_descriptors(string filename, Mat descriptors) {
	FileStorage outfile(filename, FileStorage::WRITE);
	write(outfile, "Descriptors", descriptors);
	outfile.release();
}

/** @function write_events **/
void write_events(string filename, vector<Event> events) {
    FileStorage outfile(filename, FileStorage::WRITE);
	for(vector<Event>::iterator it = events.begin(); it != events.end(); ++it) {
        write(outfile, it->type->name, it->type->descriptors);
	}
	outfile.release();
}

/** @function read_descriptors **/
Mat read_descriptors(string filename) {
    Mat descriptors;
    FileStorage infile(filename, FileStorage::READ);
    if(infile.isOpened()) {
        read(infile["Descriptors"], descriptors);
        infile.release();
    } else {
        cerr << "ERROR: feature file '" << filename << "' does not exists." << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
    return descriptors;
}

/** @function readConfigFile **/
vector<Event> readConfigFile(string fileName, int *vidStartTime) {
	vector<Event> events;

	string line, event_name, start_time, end_time;
	ifstream infile;
	infile.open(fileName.c_str());
    getline(infile, line);
	*vidStartTime = timeToSeconds(line.c_str());
    while(getline(infile, event_name, ',')) {
		Event *newEvent = new Event();
		EventType *event_type = NULL;
		for(vector<EventType>::iterator it = event_types.begin(); it != event_types.end(); ++it) {
			if(it->name.compare(event_name) == 0) {
				event_type = &*it;
				break;
			}
		}
		if(event_type == NULL) {
			event_type = new EventType();
			event_type->name = event_name;
			event_types.push_back(*event_type);
		}
        getline(infile, start_time, ',');
        getline(infile, end_time);
		newEvent->type = event_type;
		newEvent->start_time = timeToSeconds(start_time);
		newEvent->end_time = timeToSeconds(end_time);
		events.push_back(*newEvent);
	}
	infile.close();
	return events;
}

/** @function standardDeviation **/
double standardDeviation(vector<DMatch> arr, double mean) {
    double dev=0;
    double inverse = 1.0 / static_cast<double>(arr.size());
    for(unsigned int i=0; i<arr.size(); i++) {
        dev += pow((double)arr[i].distance - mean, 2);
    }
    return sqrt(inverse * dev);
}

/** @function timeToSeconds **/
int timeToSeconds(string time) {
	vector<string> temp;
	istringstream iss(time);
	while(getline(iss, time, ':')) {
		temp.push_back(time);
	}
	int seconds = 0;
	seconds += atoi(temp[0].c_str())*3600;
	seconds += atoi(temp[1].c_str())*60;
	seconds += atoi(temp[2].c_str());
	return seconds;
}

/** @function skip_frames **/
int skip_frames(CvCapture *capture, int n) {
    for (int i=0; i<n; i++) {
        // Check if at end of video.
        if (cvQueryFrame(capture) == NULL) {
            return i+1;
        }
    }
    return n;
}

/** @function printUsage **/
void printUsage() {
	cout << "Usage: wildlife_collect <config> <vid>" << endl;
}
