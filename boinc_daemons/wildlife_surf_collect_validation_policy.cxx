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

using std::string;
using std::vector;
using std::ifstream;

using std::stringstream;
using std::string;
using std::endl;

struct EventType {
    string name;
    Mat descriptors;
};

int init_result(RESULT& result, void*& data) {
    FILE* f;
    OUTPUT_FILE_INFO fi;
    int retval;

    try {
        string events_str = parse_xml<string>(result.stderr_out, "event_names");

    } catch (string error_message) {
        log_messages.printf(MSG_CRITICAL, "wildlife_surf_collect_validation_policy get_data_from_result([RESULT#%d %s]) failed with error: %s\n", result.id, result.name, error_message.c_str());
        log_messages.printf(MSG_CRITICAL, "XML:\n%s\n", result.stderr_out);
        result.outcome = RESULT_OUTCOME_VALIDATE_ERROR;
        result.validate_state = VALIDATE_STATE_INVALID;

        exit(1);
        return ERR_XML_PARSE;
    }

    stringstream ss(events_str);
    vector<string> event_names;
    vector<EventType*> event_types;

    string temp;
    while(std::getline(ss, temp, '\n')) {
        event_names.push_back(temp);
    }

    retval = get_output_file_path(result, fi.path);
    if (retval) return retval;

    FileStorage fs(fi.path.c_str(), FileStorage::READ);
    for (int i=0; i<event_names.size(); i++) {
        EventType *temp = new EventType;
        temp->name = event_names[i];
        fs[event_names[i]] >> temp->descriptors;
        event_types.push_back(temp);
    }

    fs.release();

    DATA* dp = new DATA;
    dp->count = 0;
    // Create pointer for each descriptor.

    data = (void*)dp;
    exit(0);
    return 0;
}

int compare_results(
    RESULT & r1, void* data1,
    RESULT const& r2, void* data2,
    bool& match
) {
    char *probabilities1 = (char*)data1;
    char *probabilities2 = (char*)data2;

    vector<double> p1;
    istringstream iss1(probabilities1);
    copy(istream_iterator<double>(iss1), istream_iterator<double>(), back_inserter<vector<double> >(p1));
    
    vector<double> p2;
    istringstream iss2(probabilities2);
    copy(istream_iterator<double>(iss2), istream_iterator<double>(), back_inserter<vector<double> >(p2));

    if (p1.size() != p2.size()) {
        match = false;
        log_messages.printf(MSG_CRITICAL, "ERROR, number of probabilities is different. %d vs %d\n", (int)p1.size(), (int)p2.size());

        /*
        log_messages.printf(MSG_CRITICAL, "p1 string: '%s'\n", probabilities1);
        log_messages.printf(MSG_CRITICAL, "p2 string: '%s'\n", probabilities2);

        log_messages.printf(MSG_CRITICAL, "probabilities1:\n");
        for (uint32_t i = 0; i < p1.size(); i++) {
            log_messages.printf(MSG_CRITICAL, "\t%lf\n", p1[i]);
        }

        log_messages.printf(MSG_CRITICAL, "probabilities2:\n");
        for (uint32_t i = 0; i < p2.size(); i++) {
            log_messages.printf(MSG_CRITICAL, "\t%lf\n", p2[i]);
        }
        */

        match = false;

        return 0;
    }

    double threshold = 0.026;

    for (uint32_t i = 0; i < p1.size(); i++) {
        if (fabs(p1[i] - p2[i]) > threshold) {
            match = false;

            /*
            log_messages.printf(MSG_CRITICAL, "probabilities1:\n");
            for (uint32_t j = 0; j < p1.size(); j++) {
                log_messages.printf(MSG_CRITICAL, "\t%lf\n", p1[j]);
            }

            log_messages.printf(MSG_CRITICAL, "probabilities2:\n");
            for (uint32_t j = 0; j < p2.size(); j++) {
                log_messages.printf(MSG_CRITICAL, "\t%lf\n", p2[j]);
            }
            */

            log_messages.printf(MSG_CRITICAL, "ERROR, difference in probabilities (%lf) exceeded threshold (%lf):\n", fabs(p1[i]-p2[i]), threshold);
            log_messages.printf(MSG_CRITICAL, "probabilities1[%d]: %lf\n", i, p1[i]);
            log_messages.printf(MSG_CRITICAL, "probabilities2[%d]: %lf\n", i, p2[i]);
            exit(1);

            return 0;
        }
    }

    match = true;

    return 0;
}

int cleanup_result(RESULT const& /*result*/, void* data) {
    char* result = (char*)data;

    delete result;

    return 0;
}

const char *BOINC_RCSID_7ab2b7189c = "$Id: sample_bitwise_validator.cpp 21735 2010-06-12 22:08:15Z davea $";
