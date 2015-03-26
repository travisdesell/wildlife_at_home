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

#include "undvc_common/parse_xml.hxx"
#include "undvc_common/file_io.hxx"

using namespace std;

static inline std::string &ltrim(std::string &s) {
    s.erase(s.begin(), std::find_if(s.begin(), s.end(), std::not1(std::ptr_fun<int, int>(std::isspace))));
    return s;
}

static inline std::string &rtrim(std::string &s) {
    s.erase(std::find_if(s.rbegin(), s.rend(),
    std::not1(std::ptr_fun<int, int>(std::isspace))).base(), s.end());
    return s;
}

static inline std::string &trim(std::string &s) {
    return ltrim(rtrim(s));
}

int init_result(RESULT& result, void*& data) {
    OUTPUT_FILE_INFO fi;

    try {
        string eventString = parse_xml<string>(result.stderr_out, "error");
        stringstream ss(eventString);

        string temp;
        getline(ss, temp, '\n');
        while(getline(ss, temp, '\n')) {
            trim(temp);
            log_messages.printf(MSG_DEBUG, "Error: '%s'\n", temp.c_str());
        }
        exit(0);
        return 1;
    } catch(string error_message) {
        log_messages.printf(MSG_DEBUG, "wildlife_bgsub_validation_policy get_error_from_result([RESULT#%d %s]) failed with error: %s\n", result.id, result.name, error_message.c_str());

    } catch(const exception &ex) {
        log_messages.printf(MSG_CRITICAL, "wildlife_bgsub_validation_policy get_data_from_result([RESULT#%d %s]) failed with error %s\n", result.id, result.name, ex.what());
        exit(0);
        return 1;
    }

    int retval = get_output_file_path(result, fi.path);
    if (retval) {
        log_messages.printf(MSG_CRITICAL, "wildlife_bgsub_validation_policy: Failed to get output file path: %d %s\n", result.id, result.name);
        return retval;
    }

    log_messages.printf(MSG_CRITICAL, "Result file path: '%s'\n", fi.path.c_str());

    ifstream infile(fi.path);

    vector<vector<double>*> *alg_vals = new vector<vector<double>*>();
    vector<double> *vibe_vals = new vector<double>();
    vector<double> *pbas_vals = new vector<double>();
    alg_vals->push_back(vibe_vals);
    alg_vals->push_back(pbas_vals);

    string temp;
    while(getline(infile, temp)) {
        //trim(temp);
        istringstream iss(temp);
        string vibe_val, pbas_val;
        if(!(iss >> vibe_val >> pbas_val)) {
            break;
        }
        log_messages.printf(MSG_DEBUG, "Event data: ViBe='%s' PBAS='%s'\n", vibe_val.c_str(), pbas_val.c_str());
        vibe_vals->push_back(atof(vibe_val.c_str()));
        pbas_vals->push_back(atof(pbas_val.c_str()));
    }
    infile.close();

    data = (void*)alg_vals;

    log_messages.printf(MSG_DEBUG, "Successful.\n");
    return 0;
}

int compare_results(
    RESULT & r1, void *data1,
    RESULT const& r2, void *data2,
    bool& match
) {
    float threshold = 0.00006;
    //float threshold = 0.02;
    vector<vector<double>*> *alg_vals1 = (vector<vector<double>*>*)data1;
    vector<vector<double>*> *alg_vals2 = (vector<vector<double>*>*)data2;

    log_messages.printf(MSG_DEBUG, "Check number of algorithms.\n");
    if(alg_vals1->size() != alg_vals2->size()) {
        match = false;
        log_messages.printf(MSG_CRITICAL, "ERROR, number of algorithms is different. %d vs %d\n", (int)alg_vals1->size(), (int)alg_vals2->size());
        exit(0);
        return 0;
    }

    log_messages.printf(MSG_DEBUG, "Check number of results.\n");
    for (unsigned int i = 0; i < alg_vals1->size(); i++) {
        if (alg_vals1->at(i)->size() != alg_vals2->at(i)->size()) {
            match = false;
            log_messages.printf(MSG_CRITICAL, "ERROR, number of results is different. %d vs %d\n", (int)alg_vals1->at(i)->size(), (int)alg_vals2->at(i)->size());
            return 0;
        }
    }

    for (unsigned int i = 0; i < alg_vals1->size(); i++) {
        for (unsigned int j = 0; j < alg_vals1->at(i)->size(); j++) {
            double val1 = alg_vals1->at(i)->at(j);
            double val2 = alg_vals2->at(i)->at(j);
            double dist = sqrt(val1*val1 + val2*val2);
            if (dist > threshold) {
                match = false;
                log_messages.printf(MSG_CRITICAL, "ERROR, pixel counts are different. %f vs %f\n", (double)(alg_vals1->at(i)->at(j)), (double)(alg_vals2->at(i)->at(j)));
                exit(0);
                return 0;
            }
        }
    }

    match = true;
    log_messages.printf(MSG_DEBUG, "Everything seems to match.\n");
    return 0;
}

int cleanup_result(RESULT const& /*result*/, void* data) {
    vector<vector<double>*> *result = (vector<vector<double>*>*)data;

    if (result) {
        for (unsigned int i = 0; i < result->size(); i++) {
            delete result->at(i);
        }
    }
    return 0;
}

const char *BOINC_RCSID_7ab2b7189c = "$Id: sample_bitwise_validator.cpp 21735 2010-06-12 22:08:15Z davea $";
