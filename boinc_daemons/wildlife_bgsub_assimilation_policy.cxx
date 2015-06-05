/*
 * Copyright 2012, 2009 Travis Desell and the University of North Dakota.
 *
 * This file is part of the Toolkit for Asynchronous Optimization (TAO).
 *
 * TAO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TAO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TAO.  If not, see <http://www.gnu.org/licenses/>.
 * */

#include <vector>
#include <cstdlib>
#include <string>
#include <fstream>
#include <algorithm>
#include <cmath>

#include "config.h"
#include "util.h"
#include "sched_util.h"
#include "sched_msgs.h"
#include "md5_file.h"
#include "error_numbers.h"
#include "validate_util.h"

#include "stdint.h"
#include "mysql.h"
#include "boinc_db.h"

//from UNDVC_COMMON
#include "file_io.hxx"
#include "parse_xml.hxx"

using namespace std;

/** Default Values **/
static const string MOG_ID = "1";
static const string VIBE_ID = "2";
static const string PBAS_ID = "3";
// Specify all events as unclassified
static const string EVENT_ID = "32";

/** Function Headers **/
double calcMean(vector<double> vals);
double calcVariance(vector<double> vals, const double mean);
vector<pair<size_t,size_t>> calculateEventTimes(vector<double> &vals, double dist, double fps);
void addEvents(const string &video_id, const string &algorithm_id, const string &event_id, const string &version_id, const vector<pair<size_t,size_t>> &events);

#define mysql_query_check(conn, query) __mysql_check (conn, query, __FILE__, __LINE__)

void __mysql_check(MYSQL *conn, string query, const char *file, const int line) {
    mysql_query(conn, query.c_str());

    if (mysql_errno(conn) != 0) {
        ostringstream ex_msg;
        ex_msg << "ERROR in MySQL query: '" << query.c_str() << "'. Error: " << mysql_errno(conn) << " -- '" << mysql_error(conn) << "'. Thrown on " << file << ":" << line;
        cerr << ex_msg.str() << endl;
        exit(1);
    }
}

MYSQL *wildlife_db_conn = NULL;

void initialize_database() {
    wildlife_db_conn = mysql_init(NULL);

    //shoud get database info from a file
    string db_host, db_name, db_password, db_user;
    ifstream db_info_file("../wildlife_db_info");

    db_info_file >> db_host >> db_name >> db_user >> db_password;
    db_info_file.close();

    cout << "parsed db info:" << endl;
    cout << "\thost: " << db_host << endl;
    cout << "\tname: " << db_name << endl;
    cout << "\tuser: " << db_user << endl;
    cout << "\tpass: " << db_password << endl;

    if (mysql_real_connect(wildlife_db_conn, db_host.c_str(), db_user.c_str(), db_password.c_str(), db_name.c_str(), 0, NULL, 0) == NULL) {
        cerr << "Error connecting to database: " << mysql_errno(wildlife_db_conn) << ", '" << mysql_error(wildlife_db_conn) << "'" << endl;
        exit(1);
    }
}

//returns 0 on sucess
int assimilate_handler(WORKUNIT& wu, vector<RESULT>& results, RESULT& canonical_result) {
    OUTPUT_FILE_INFO fi;

    if (wildlife_db_conn == NULL) initialize_database();

    //need to read wu.xml_doc
    string xml_doc;

    ostringstream oss;
    oss << "SELECT xml_doc FROM workunit WHERE id = " << wu.id;
    string query = oss.str();

    mysql_query_check(boinc_db.mysql, query.c_str());

    MYSQL_RES *my_result = mysql_store_result(boinc_db.mysql);
    if (mysql_errno(boinc_db.mysql) == 0) {
        MYSQL_ROW row = mysql_fetch_row(my_result);

        if (row == NULL) {
            log_messages.printf(MSG_CRITICAL, "Could not get row from workunit with query '%s'. Error: %d -- '%s'\n", xml_doc.c_str(), mysql_errno(boinc_db.mysql), mysql_error(boinc_db.mysql));
            return 1;
        }

        xml_doc = row[0];
    } else {
        log_messages.printf(MSG_CRITICAL, "Could execute query '%s'. Error: %d -- '%s'\n", xml_doc.c_str(), mysql_errno(boinc_db.mysql), mysql_error(boinc_db.mysql));
        return 1;
    }
    mysql_free_result(my_result);

    /*
     * Now that the workunit xml has been collected, we can parse it for the appropriate information
     */
    string tag_str;

    try {
        tag_str = parse_xml<string>(xml_doc, "tag");
    } catch (string error_message) {
        log_messages.printf(MSG_CRITICAL, "wildlife_bgsub_assimilation_policy assimilate_handler([RESULT#%d %s]) failed with error: %s\n", canonical_result.id, canonical_result.name, error_message.c_str());
        log_messages.printf(MSG_CRITICAL, "XML:\n'%s'\n", xml_doc.c_str());
        return 1;
        return 0;
    }

    int retval = get_output_file_path(canonical_result, fi.path);
    if (retval) {
        log_messages.printf(MSG_CRITICAL, "wildlife_bgsub_validation_policy: Failed to get output file path: %d %s\n", canonical_result.id, canonical_result.name);
        //return retval;
    }

    log_messages.printf(MSG_CRITICAL, "Result file path: '%s'\n", fi.path.c_str());

    ifstream infile(fi.path);

    vector<double> vibe_vals;
    vector<double> pbas_vals;

    string temp;
    while(getline(infile, temp)) {
        istringstream iss(temp);
        string vibe_val, pbas_val;
        if(!(iss >> vibe_val >> pbas_val)) {
            break;
        }
        //log_messages.printf(MSG_DEBUG, "Event data: ViBe='%s' PBAS='%s'\n", vibe_val.c_str(), pbas_val.c_str());
        vibe_vals.push_back(atof(vibe_val.c_str()));
        pbas_vals.push_back(atof(pbas_val.c_str()));
    }
    infile.close();

    log_messages.printf(MSG_DEBUG, "num vals: %d, %d\n", (int)vibe_vals.size(), (int)pbas_vals.size());

    // Calculate event times
    //TODO Get actual FPS
    double fps = 10;
    vector<pair<size_t,size_t>> vibe_events = calculateEventTimes(vibe_vals, 3, fps);
    vector<pair<size_t,size_t>> pbas_events = calculateEventTimes(pbas_vals, 3, fps);

    log_messages.printf(MSG_DEBUG, "num events: %d, %d\n", (int)vibe_events.size(), (int)pbas_events.size());
    log_messages.printf(MSG_DEBUG, "tag: '%s'\n", tag_str.c_str());
    log_messages.printf(MSG_DEBUG, "result name: '%s'\n", canonical_result.name);

    //get video id
    //result name is video_<video_id>_<time>_<result number>
    string result_name = results[0].name;
/*    string result_name = canonical_result.name;     SHOULD BE THIS */
    size_t number_scores = count(result_name.begin(), result_name.end(), '_');
    size_t start_pos = result_name.find('_', 0) + 1;
    size_t end_pos = result_name.find('_', start_pos);
    for (size_t i = 0; i < number_scores - 3; i++) {
        start_pos = end_pos + 1;
        end_pos = result_name.find('_', start_pos);
    }

    if (start_pos == string::npos || end_pos == string::npos) {
        log_messages.printf(MSG_CRITICAL, "wildlife_surf_collect_assimilation_policy assimilate_handler failed with 'malformed result name error', result name: %s\n", result_name.c_str());
        return 1;
    }

    string video_id = result_name.substr(start_pos, (end_pos - start_pos));

    log_messages.printf(MSG_DEBUG, "parsed video id: '%s'\n", video_id.c_str());

    //TODO Add all results with the given video ID and algorithm version.
    //Possibly check if this video already has results for the given version
    //number, if so error out.
    addEvents(video_id, VIBE_ID, EVENT_ID, "100", vibe_events);
    addEvents(video_id, PBAS_ID, EVENT_ID, "100", pbas_events);

    return 0;
}

double calcMean(vector<double> vals) {
    double val = 0;
    for (size_t i = 0; i < vals.size(); i++) {
        val += vals[i];
    }
    return val/vals.size();
}

double calcVariance(vector<double> vals, const double mean) {
    double var = 0;
    for (size_t i = 0; i < vals.size(); i++) {
        double diff = vals[i] - mean;
        var = diff * diff;
    }
    return var/vals.size();
}

vector<pair<size_t,size_t>> calculateEventTimes(vector<double> &vals, double dist, double fps) {
    vector<pair<size_t,size_t>> output;

    double mean = calcMean(vals);
    double stdev = sqrt(calcVariance(vals, mean));

    bool event_happening = false;
    size_t start_time = 0;

    for (size_t time = 0; time < vals.size(); time++) {
        double thresh = mean + (dist * stdev);
        if (vals[time] >= thresh) {
            event_happening = true;
            start_time = time;
        } else if (event_happening && vals[time] < thresh) {
            event_happening = false;
            output.push_back(pair<size_t,size_t>(start_time/fps, time/fps));
        }
    }
    return output;
}

void addEvents(const string &video_id, const string &algorithm_id, const string &event_id, const string &version_id, const vector<pair<size_t,size_t>> &events) {
    for (unsigned int i = 0; i < events.size(); i++) {
        ostringstream insert_event_query;
        insert_event_query << "INSERT INTO computed_events VALUES (NULL, ";
        insert_event_query << algorithm_id << ", ";
        insert_event_query << event_id << ", ";
        insert_event_query << video_id << ", ";
        insert_event_query << version_id << ", ";
        insert_event_query << events.at(i).first << ", ";
        insert_event_query << events.at(i).second << ");";

        mysql_query_check(wildlife_db_conn, insert_event_query.str());
    }
}
