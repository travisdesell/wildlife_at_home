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

#include <iostream>
#include <fstream>
#include <sstream>
#include <string>
#include <algorithm>

#include "undvc_common/parse_xml.hxx"
#include "undvc_common/file_io.hxx"

using std::string;
using std::vector;
using std::ifstream;

using std::string;
using std::endl;

struct SSS_RESULT {
    uint32_t checksum;
    vector<uint64_t> failed_sets;
};

int init_result(RESULT& result, void*& data) {
    char *probabilities;

    try {
        string prob_str = parse_xml<string>(result.stderr_out, "slice_probabilities");

        char chars[] = "\n\r";
        for (unsigned int i = 0; i < strlen(chars); ++i) {
            // you need include <algorithm> to use general algorithms like std::remove()
            prob_str.erase (std::remove(prob_str.begin(), prob_str.end(), chars[i]), prob_str.end());
        }

        probabilities = (char*)malloc(sizeof(char) * (strlen(prob_str.c_str()) + 1));
        strcpy(probabilities, prob_str.c_str());
        probabilities[strlen(prob_str.c_str())] = '\0';

        cout << "probabilities: " << probabilities << endl;
    } catch (string error_message) {
        log_messages.printf(MSG_CRITICAL, "sss_validation_policy get_data_from_result([RESULT#%d %s]) failed with error: %s\n", result.id, result.name, error_message.c_str());
        log_messages.printf(MSG_CRITICAL, "XML:\n%s\n", result.stderr_out);
//        result.outcome = RESULT_OUTCOME_VALIDATE_ERROR;
//        result.validate_state = VALIDATE_STATE_INVALID;

        if (strstr(result.stderr_out, "frame") != NULL) {
            probabilities = (char*)malloc(sizeof(char) * 6);
            probabilities[0] = 'f';
            probabilities[1] = 'r';
            probabilities[2] = 'a';
            probabilities[3] = 'm';
            probabilities[4] = 'e';
            probabilities[5] = '\0';

            data = (void*)probabilities;
            return 0;
        }

        exit(1);
        return ERR_XML_PARSE;
    }

    data = (void*)probabilities;

    return 0;
}

int compare_results(
    RESULT & r1, void* data1,
    RESULT const& r2, void* data2,
    bool& match
) {
    char *probabilities1 = (char*)data1;
    char *probabilities2 = (char*)data2;

    match = (strcmp(probabilities1, probabilities2) == 0);

    if (!match) {
        cout << "ERROR, probabilities do not match:" << endl;
        cout << "probabilities1: " << endl;
        cout << probabilities1 << endl << endl;
        cout << "probabilities2: " << endl;
        cout << probabilities2 << endl << endl;
        exit(1);
    }

    return 0;
}

int cleanup_result(RESULT const& /*result*/, void* data) {
    char* result = (char*)data;

    delete result;

    return 0;
}

const char *BOINC_RCSID_7ab2b7189c = "$Id: sample_bitwise_validator.cpp 21735 2010-06-12 22:08:15Z davea $";
