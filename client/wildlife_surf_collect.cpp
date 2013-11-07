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

#define GUI

using namespace std;
using namespace cv;

struct EventType {
	string id;
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
void write_events(string, vector<EventType*>);
void read_event_desc(string, vector<EventType*>);
Mat read_descriptors(string, string);
int skip_frames(VideoCapture, int);
void printUsage();
double standardDeviation(vector<DMatch>, double);
int timeToSeconds(string);
vector<Event*> readConfigFile(string, int*);
bool readParams(int, char**);

string checkpoint_filename;
string checkpoint_desc_filename;
string config_file_name;
string vid_file_name;
string desc_file_name;
int frame_pos;
int checkpoint_frame_pos = 0;
float total;
int vid_time;

int min_hessian = 400;
double flann_threshold = 3.5;
vector<EventType*> event_types;
vector<Event*> events;

int main(int argc, char **argv) {
	if(argc < 5) {
		printUsage();
		return -1;
	} else {
        if (!readParams(argc, argv)) {
            printUsage();
            return -1;
        }
    }

    cerr << "Vid file: " << vid_file_name.c_str() << endl;
    cerr << "Config file: " << config_file_name.c_str() << endl;
    cerr << "Min Hessian: " << min_hessian << endl;
    cerr << "Flann Threshold: " << flann_threshold << " * standard deviation" << endl;
    desc_file_name = "results.desc";

#ifdef _BOINC_APP_
    cout << "Boinc enabled." << endl;
    string resolved_config_path;
    string resolved_vid_path;
    string resolved_desc_path;
    cerr << "Resolving boinc file paths." << endl;
    int retval = boinc_resolve_filename_s(config_file_name.c_str(), resolved_config_path);
    if (retval) {
        cerr << "Error, could not open file: '" << config_file_name.c_str() << "'" << endl;
        cerr << "Resolved to: '" << resolved_config_path.c_str() << "'" << endl;
        return false;
    }
    config_file_name = resolved_config_path;

    retval = boinc_resolve_filename_s(vid_file_name.c_str(), resolved_vid_path);
    if (retval) {
        cerr << "Error, could not open file: '" << vid_file_name.c_str() << "'" << endl;
        cerr << "Resolved to: '" << resolved_vid_path.c_str() << "'" << endl;
        return false;
    }
    vid_file_name = resolved_vid_path;

    retval = boinc_resolve_filename_s(desc_file_name.c_str(), resolved_desc_path);
    if (retval) {
        cerr << "Error, could not open file: '" << desc_file_name.c_str() << "'" << endl;
        cerr << "Resolved to: '" << resolved_desc_path.c_str() << "'" << endl;
        return false;
    }
    desc_file_name = resolved_desc_path;
#endif

	events = readConfigFile(config_file_name, &vid_time);
	cerr << "Events: " << events.size() << endl;
	cerr << "Event Types: " << event_types.size() << endl;

	VideoCapture capture(vid_file_name.c_str());
    if(!capture.isOpened()) {
        cerr << "Failed to open " << vid_file_name.c_str() << endl;
        return false;
    }

#ifdef _BOINC_APP_
    boinc_init();
#endif

    checkpoint_filename = "checkpoint.txt";
    checkpoint_desc_filename = "checkpoint.desc";

    if(read_checkpoint()) {
        cerr << "Start from checkpoint..." << endl;
    } else {
        cerr << "Unseccessful checkpoint read." << endl << "Starting from beginning of video." << endl;
    }

    skip_frames(capture, checkpoint_frame_pos);

    frame_pos = capture.get(CV_CAP_PROP_POS_FRAMES);
    total = capture.get(CV_CAP_PROP_FRAME_COUNT);

    cerr << "Config File Name: " << config_file_name.c_str() << endl;
    cerr << "Vid File Name: " << vid_file_name.c_str() << endl;
    cerr << "Current Frame: " << frame_pos << endl;
    cerr << "Frame Count: " << total << endl;

	// Loop through all video frames.
	while(frame_pos/total < 1.0) {
		//cout << "Percent complete: " << framePos/total*100 << endl;
#ifdef _BOINC_APP_
        boinc_fraction_done(frame_pos/total);

        int key = waitKey(1);
        if(boinc_time_to_checkpoint() || key == 's') {
            cerr << "boinc_time_to_checkpoint encountered, checkpointing" << endl;
            write_checkpoint();
            boinc_checkpoint_completed();
        }
#endif
		Mat img;
        capture >> img;
		frame_pos = capture.get(CV_CAP_PROP_POS_FRAMES);

		// Increment video time every 10 frames.
		if(frame_pos % 10 == 0) vid_time++;
		//cout << "Video time: " << vidTime << endl;

    	Mat frame = img;

		SurfFeatureDetector detector(min_hessian);
		vector<KeyPoint> keypoints_frame;
		detector.detect(frame, keypoints_frame);

		SurfDescriptorExtractor extractor;
		Mat descriptors_frame;
		extractor.compute(frame, keypoints_frame, descriptors_frame);

		// Add distinct features to active events.
		int activeEvents = 0;
		for(vector<Event*>::iterator it = events.begin(); it != events.end(); ++it) {
			if(vid_time >= (*it)->start_time && vid_time <= (*it)->end_time) {
				activeEvents++;
				if ((*it)->type->descriptors.empty()) {
					(*it)->type->descriptors.push_back(descriptors_frame);
				} else {
					// Find Matches
					FlannBasedMatcher matcher;
					vector<DMatch> matches;
					matcher.match(descriptors_frame, (*it)->type->descriptors, matches);

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
					double std_dev = standardDeviation(matches, avg_dist);
					//cout << "Max dist: " << max_dist << endl;
					//cout << "Avg dist: " << avg_dist << endl;
					//cout << "Min dist: " << min_dist << endl;
					//cout << "Avg + 3.5*stdDev: " << avg_dist + 3.5*stdDev << endl;

					vector<DMatch> new_matches;

					for(int i=0; i<matches.size(); i++) {
						if(matches[i].distance > avg_dist+(flann_threshold*std_dev)) {
							new_matches.push_back(matches[i]);
						}
					}

					Mat new_descriptors;
					//cout << it->type->id << " descriptors found: " << descriptors_frame.rows << endl;
					for(int i=0; i<new_matches.size(); i++) {
						new_descriptors.push_back(descriptors_frame.row(new_matches[i].queryIdx));
					}

					//cout << it->type->id << " descriptors added: " << new_descriptors.rows << endl;
					if (new_descriptors.rows > 0) {
						(*it)->type->descriptors.push_back(new_descriptors);
					}
					//cout << it->type->id << " descriptors: " << it->type->descriptors.size() << endl;
                }
            }
        }
        if(activeEvents == 0)
            cerr << "[ERROR] There are no active events! (Problem with expert classification.)" << endl;

#ifdef GUI
        // Code to draw the points.
        Mat frame_points = frame;
        // Watermark Box
        cv::Point watermark_top_left(12, 12);
        cv::Point watermark_bottom_right(90, 55);
        rectangle(frame_points, watermark_top_left, watermark_bottom_right, Scalar(0, 0, 100));
        // Time box
        cv::Point time_top_left(520, 415);
        cv::Point time_bottom_right(680, 470);
        rectangle(frame_points, time_top_left, time_bottom_right, Scalar(0, 0, 100));
        drawKeypoints(frame, keypoints_frame, frame_points, Scalar::all(-1), DrawMatchesFlags::DEFAULT);

        // Display image.
        imshow("SURF", frame_points);
        if((cvWaitKey(10) & 255) == 27) break;
#endif
    }

    cerr << "<event_ids>" << endl;
    for (int i=0; i<event_types.size(); i++) {
        cerr << event_types[i]->id.c_str() << endl;
    }
    cerr << "</event_ids>" << endl;
    write_events(desc_file_name, event_types);

#ifdef GUI
    cvDestroyWindow("SURF");
#endif

    capture.release();

#ifdef _BOINC_APP_
    boinc_finish(0);
#endif
    cerr << "Finished!" << endl;
    return 0;
}

/** @function write_checkpoint **/
void write_checkpoint() {
#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if(retval) {
        cerr << "Couldn't resolve file name..." << endl;
        return;
    }
    checkpoint_filename = resolved_path;

    retval = boinc_resolve_filename_s(checkpoint_desc_filename.c_str(), resolved_path);
    if(retval) {
        cerr << "Couldn't resolve file name..." << endl;
        return;
    }
    checkpoint_desc_filename = resolved_path;
#endif

    ofstream checkpoint_file(checkpoint_filename.c_str());
    if(!checkpoint_file.is_open()) {
        cerr << "Checkpoint file not open..." << endl;
        return;
    }

    checkpoint_file << "CURRENT_FRAME: " << frame_pos << endl;

    write_events(checkpoint_desc_filename, event_types);

    checkpoint_file << endl;
    checkpoint_file.close();
}


/** @function read_checkpoint **/
bool read_checkpoint() {
    cerr << "Reading checkpoint..." << endl;
#ifdef _BOINC_APP_
    string resolved_path;
    int retval = boinc_resolve_filename_s(checkpoint_filename.c_str(), resolved_path);
    if(retval) {
        cerr << "Couldn't resolve file name..." << endl;
        return false;
    }
    checkpoint_filename = resolved_path;

    retval = boinc_resolve_filename_s(checkpoint_desc_filename.c_str(), resolved_path);
    if(retval) {
        cerr << "Couldn't resolve file name..." << endl;
        return false;
    }
    checkpoint_desc_filename = resolved_path;
#endif

    ifstream checkpoint_file(checkpoint_filename.c_str());
    if(!checkpoint_file.is_open()) return false;

    string s;
    checkpoint_file >> s >> checkpoint_frame_pos;
    cerr << s.c_str() << " " << checkpoint_frame_pos << endl;
    if(s.compare("CURRENT_FRAME:") != 0 ) {
        cerr << "ERROR: malformed checkpoint! could not read 'CURRENT_FRAME'" << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }

    read_event_desc(checkpoint_desc_filename, event_types);
    cerr << "Done reading checkpoint." << endl;
    return true;
}

/** @function write_events **/
void write_events(string filename, vector<EventType*> event_types) {
    FileStorage outfile(filename, FileStorage::WRITE);
	for(vector<EventType*>::iterator it = event_types.begin(); it != event_types.end(); ++it) {
        cerr << "Write: " << (*it)->id.c_str() << endl;
        outfile << (*it)->id << (*it)->descriptors;
	}
	outfile.release();
}

/** @function read_events **/
void read_event_desc(string filename, vector<EventType*> event_types) {
    cerr << "Opening file: " << filename.c_str() << endl;
    FileStorage infile(filename, FileStorage::READ);
    if(infile.isOpened()) {
        cerr << filename.c_str() << " is open." << endl;
        for(vector<EventType*>::iterator it = event_types.begin(); it != event_types.end(); ++it) {
            cerr << "Read: " << (*it)->id.c_str() << endl;
            infile[(*it)->id] >> (*it)->descriptors;
        }
        infile.release();
    } else {
        cerr << "ERROR: feature file '" << filename.c_str() << "' does does not exist." << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
}

/** @function read_descriptors **/
Mat read_descriptors(string filename, string desc_name) {
    Mat descriptors;
    FileStorage infile(filename, FileStorage::READ);
    if(infile.isOpened()) {
        read(infile[desc_name], descriptors);
        infile.release();
    } else {
        cerr << "ERROR: feature file '" << filename.c_str() << "' does not exists." << endl;
#ifdef _BOINC_APP_
        boinc_finish(1);
#endif
        exit(1);
    }
    return descriptors;
}

/** @function readConfigFile **/
vector<Event*> readConfigFile(string fileName, int *vidStartTime) {
	vector<Event*> events;

    cerr << "Reading config file: " << fileName.c_str() << endl;
	string line, event_id, start_time, end_time;
	ifstream infile;
	infile.open(fileName.c_str());
    getline(infile, line);
	*vidStartTime = timeToSeconds(line.c_str());
    while(getline(infile, event_id, ',')) {
		Event *newEvent = new Event();
		EventType *event_type = NULL;
		for(vector<EventType*>::iterator it = event_types.begin(); it != event_types.end(); ++it) {
            cerr << "Event name: " << (*it)->id.c_str() << endl;
			if((*it)->id.compare(event_id) == 0) {
				event_type = *it;
				break;
			}
		}
		if(event_type == NULL) {
			event_type = new EventType();
			event_type->id = event_id;
			event_types.push_back(event_type);
		}
        if(!getline(infile, start_time, ',') || !getline(infile, end_time)) {
            cerr << "Error: Malformed config file!" << endl;
#ifdef _BOINC_APP_
            boinc_finish(1);
#endif
            exit(1);
        }
        newEvent->type = event_type;
        newEvent->start_time = timeToSeconds(start_time);
        newEvent->end_time = timeToSeconds(end_time);
		events.push_back(newEvent);
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
int skip_frames(VideoCapture capture, int n) {
    Mat frame;
    for (int i=0; i<n; i++) {
        capture >> frame;
        // Check if at end of video.
        if (frame.empty()) {
            return i+1;
        }
    }
    return n;
}

/** @function readParams **/
bool readParams(int argc, char **argv) {
    for (int i=1; i<argc; i++) {
        if (i+1 < argc) {
            if (string(argv[i]) == "--video" || string(argv[i]) == "--v") {
                vid_file_name = argv[++i];
            } else if (string(argv[i]) == "--config" || string(argv[i]) == "--c") {
                config_file_name = argv[++i];
            } else if (string(argv[i]) == "--hessian" || string(argv[i]) == "--h") {
                min_hessian = atoi(argv[++i]);
            } else if (string(argv[i]) == "--threshold" || string(argv[i]) == "--t") {
                flann_threshold = atoi(argv[++i]);
            }
        } else {
            cout << "Parameter has no matching value." << endl;
            return false;
        }
    }
    if (vid_file_name.empty() || config_file_name.empty()) return false;
    else return true;
}

/** @function printUsage **/
void printUsage() {
	cout << "Usage: wildlife_collect --v <video> --c <config> [--h <min hessian>] [--t <feature match threshold>]" << endl;
}
