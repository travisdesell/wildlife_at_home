// This file is part of BOINC.
// http://boinc.berkeley.edu
// Copyright (C) 2008 University of California
//
// BOINC is free software; you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation,
// either version 3 of the License, or (at your option) any later version.
//
// BOINC is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with BOINC.  If not, see <http://www.gnu.org/licenses/>.

// A sample validator that requires a majority of results to be
// bitwise identical.
// This is useful only if either
// 1) your application does no floating-point math, or
// 2) you use homogeneous redundancy

#include "config.h"
#include "util.h"
#include "sched_util.h"
#include "sched_msgs.h"
#include "validate_util.h"
#include "md5_file.h"
#include "error_numbers.h"
#include "stdint.h"

#include <cmath>
#include <iostream>
#include <fstream>
#include <sstream>
#include <string>
#include <algorithm>
#include <iterator>

#include <opencv2/opencv.hpp>
#include <opencv2/core/core.hpp>

#include "undvc_common/parse_xml.hxx"
#include "undvc_common/file_io.hxx"

#include "wildlife_surf.hpp"

using std::string;
using std::vector;
using std::ifstream;

using std::stringstream;
using std::string;
using std::endl;
using namespace cv;

int init_result(RESULT& result, void*& data) {
    OUTPUT_FILE_INFO fi;
    int retval;

    try {
        string events_str = parse_xml<string>(result.stderr_out, "event_ids");
        stringstream ss(events_str);
        vector<string> event_names;
        vector<EventType*> *event_types = new vector<EventType*>();

        string temp;
        std::getline(ss, temp, '\n');
        while(std::getline(ss, temp, '\n')) {
            log_messages.printf(MSG_DEBUG, "Event id: %s\n", temp.c_str());
            event_names.push_back(temp);
        }

        retval = get_output_file_path(result, fi.path);
        if (retval) {
            log_messages.printf(MSG_CRITICAL, "wildlife_surf_collect_validation_policy: Failed to get output file path: %d %s\n", result.id, result.name);
            exit(0);
            return retval;
        }

        FileStorage fs(fi.path.c_str(), FileStorage::READ);
        log_messages.printf(MSG_DEBUG, "Adding events to data structure.\n");
        for (unsigned int i=0; i<event_names.size(); i++) {
            EventType *temp = new EventType;
            temp->id = event_names[i];
            fs[event_names[i]] >> temp->descriptors;
            event_types->push_back(temp);
        }

        fs.release();
        data = (void*)event_types;
    } catch (string error_message) {
        log_messages.printf(MSG_CRITICAL, "wildlife_surf_collect_validation_policy get_data_from_result([RESULT#%d %s]) failed with error: %s\n", result.id, result.name, error_message.c_str());
        log_messages.printf(MSG_CRITICAL, "XML:\n%s\n", result.stderr_out);
        result.outcome = RESULT_OUTCOME_VALIDATE_ERROR;
        result.validate_state = VALIDATE_STATE_INVALID;

        log_messages.printf(MSG_DEBUG, "Returning XML Error for %s\n", result.name);
        exit(0);
        return ERR_XML_PARSE;
    }

    // Check for any null pointer errors
    log_messages.printf(MSG_DEBUG, "Checking for null pointer errors.\n");
    vector<EventType*> *desc = (vector<EventType*>*)data;
    for (unsigned int i=0; i<desc->size(); i++) {
        if (desc->at(i) == NULL) {
            log_messages.printf(MSG_CRITICAL, "wildlife_surf_validation_policy: Null pointer to descriptor found in result %d %s\n", result.id, result.name);
            exit(0);
            return 1;
        }
    }

    log_messages.printf(MSG_DEBUG, "Successful.\n");
    //exit(0);
    return 0;
}

int compare_results(
    RESULT & r1, void *data1,
    RESULT const& r2, void *data2,
    bool& match
) {
    double threshold = 0.00006;
    vector<EventType*> *desc1 = (vector<EventType*>*)data1;
    vector<EventType*> *desc2 = (vector<EventType*>*)data2;

    log_messages.printf(MSG_DEBUG, "Check number of events.\n");
    if(desc1->size() != desc2->size()) {
        match = false;
        log_messages.printf(MSG_CRITICAL, "ERROR, number of event types is different. %d vs %d\n", (int)desc1->size(), (int)desc2->size());
        exit(0);
        return 0;
    }

    for (unsigned int i=0; i<desc1->size(); i++) {
        log_messages.printf(MSG_DEBUG, "Check event names.\n");
        if (desc1->at(i)->id != desc2->at(i)->id) {
            match = false;
            log_messages.printf(MSG_CRITICAL, "ERROR, event names do not match. %s vs %s\n", desc1->at(i)->id.c_str(), desc2->at(i)->id.c_str());
            exit(0);
            return 0;
        }

        int matches = 0;

        log_messages.printf(MSG_DEBUG, "Check number of descriptors.\n");
        if (desc1->at(i)->descriptors.rows != desc2->at(i)->descriptors.rows) {
            match = false;
            log_messages.printf(MSG_CRITICAL, "ERROR, number of descriptors is different. %d vs %d\n", (int)desc1->at(i)->descriptors.rows, (int)desc2->at(i)->descriptors.rows);
            //exit(0);
            return 1;
        }
        Mat temp = desc1->at(i)->descriptors - desc2->at(i)->descriptors;
        log_messages.printf(MSG_DEBUG, "Check descriptors.\n");
        for (int x=0; x<temp.rows; x++) {
            bool sub_match = true;
            for (int y=0; y<temp.cols; y++) {
                if (temp.at<double>(x,y) > threshold) {
                    sub_match = false;
                    log_messages.printf(MSG_DEBUG, "Descriptors at (%d, %d) are not the same. Difference of %f\n", x, y, (float)temp.at<double>(x,y));
                }
            }
            if (sub_match) matches++;
        }
        if (matches < temp.rows) {
            log_messages.printf(MSG_CRITICAL, "%d/%d (%f) of descriptors match for results %d and %d \n", matches, temp.rows, (float)matches/temp.rows*100, r1.id, r2.id);
            //exit(0);
            return 1;
            return ERR_OPENDIR;
        }
    }

    match = true;
    log_messages.printf(MSG_DEBUG, "Everything seems to match.\n");
    return 0;
}

int cleanup_result(RESULT const& /*result*/, void* data) {
    vector<EventType*> *result = (vector<EventType*>*)data;

    if (result) {
        for (unsigned int i=0; i<result->size(); i++) {
            delete result->at(i);
        }
    }
    return 0;
}

const char *BOINC_RCSID_7ab2b7189c = "$Id: sample_bitwise_validator.cpp 21735 2010-06-12 22:08:15Z davea $";
