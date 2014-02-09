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

// sample_work_generator.cpp: an example BOINC work generator.
// This work generator has the following properties
// (you may need to change some or all of these):
//
// - Runs as a daemon, and creates an unbounded supply of work.
//   It attempts to maintain a "cushion" of 100 unsent job instances.
//   (your app may not work this way; e.g. you might create work in batches)
// - Creates work for the application "example_app".
// - Creates a new input file for each job;
//   the file (and the workunit names) contain a timestamp
//   and sequence number, so they're unique.


#include <iostream>
#include <unistd.h>
#include <cstdlib>
#include <string>
#include <cstring>
#include <sstream>
#include <cmath>
#include <fstream>
#include <ostream>
#include <iomanip>

#include <unordered_map>

#include "boinc_db.h"
#include "error_numbers.h"
#include "backend_lib.h"
#include "parse.h"
#include "util.h"
#include "svn_version.h"

#include "sched_config.h"
#include "sched_util.h"
#include "sched_msgs.h"
#include "str_util.h"

#include "mysql.h"

#include "undvc_common/arguments.hxx"
#include "undvc_common/file_io.hxx"


const char* app_name = NULL;
const char* out_template_file = "wildlife_out.xml";

DB_APP app;
int start_time;
int seqno;

static const int MAX_REQUIRED_VIEWS = 5;
static const int TIME_CUTOFF = 10;

using namespace std;

unordered_map<int, string> observation_types_map;



/**
 *  This wrapper makes for much more informative error messages when doing MYSQL queries
 */
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

/**
 *  The following opens a database connection to the remote database on wildlife.und.edu
 */
MYSQL *wildlife_db_conn = NULL;
MYSQL *boinc_db_conn = NULL;

void initialize_wildlife_database() {
    wildlife_db_conn = mysql_init(NULL);

    //shoud get database info from a file
    string db_host, db_name, db_password, db_user;
    ifstream db_info_file("../wildlife_db_info");

    db_info_file >> db_host >> db_name >> db_user >> db_password;
    db_info_file.close();

    /*
       cout << "parsed db info:" << endl;
       cout << "\thost: " << db_host << endl;
       cout << "\tname: " << db_name << endl;
       cout << "\tuser: " << db_user << endl;
       cout << "\tpass: " << db_password << endl;
       */

    if (mysql_real_connect(wildlife_db_conn, db_host.c_str(), db_user.c_str(), db_password.c_str(), db_name.c_str(), 0, NULL, 0) == NULL) {
        log_messages.printf(MSG_CRITICAL, "Error connecting to database: %d, '%s'\n", mysql_errno(wildlife_db_conn), mysql_error(wildlife_db_conn));
        exit(1);
    }   
}

void initialize_boinc_database() {
    int retval = config.parse_file();
    if (retval) {
        log_messages.printf(MSG_CRITICAL, "Can't parse config.xml: %s\n", boincerror(retval));
        exit(1);
    }

    retval = boinc_db.open( config.db_name, config.db_host, config.db_user, config.db_passwd );
    if (retval) {
        log_messages.printf(MSG_CRITICAL, "can't open db\n");
        exit(1);
    }

    boinc_db_conn = boinc_db.mysql;
}


void usage(char *name) {
    fprintf(stderr, "This is an example BOINC work generator.\n"
            "This work generator has the following properties\n"
            "(you may need to change some or all of these):\n"
            "  It attempts to maintain a \"cushion\" of 100 unsent job instances.\n"
            "  (your app may not work this way; e.g. you might create work in batches)\n"
            "- Creates work for the application \"example_app\".\n"
            "- Creates a new input file for each job;\n"
            "  the file (and the workunit names) contain a timestamp\n"
            "  and sequence number, so that they're unique.\n\n"
            "Usage: %s [OPTION]...\n\n"
            "Options:\n"
            "  --app X                      Application name (default: example_app)\n"
            "  --out_template_file          Output template (default: example_app_out)\n"
            "  [ -d X ]                     Sets debug level to X.\n"
            "  [ -h | --help ]              Shows this help text.\n"
            "  [ -v | --version ]           Shows version information.\n",
            name
           );
}

class DBVideo {
    public:
        int id;
        int duration_s;
        int species_id;
        int location_id;

        DBVideo(MYSQL_ROW &video_row) {
            id = atoi(video_row[0]);
            duration_s = atoi(video_row[1]);
            species_id = atoi(video_row[2]);
            location_id = atoi(video_row[3]);
        }

        string to_string() {
            ostringstream oss;
            oss << "[VIDEO - video_id: " << setw(8) << id
                << ", duration_s: " << setw(6) << duration_s
                << ", species_id: " << setw(3) << species_id
                << ", location_id: " << setw(3) << location_id << "]";
            return oss.str();
        }
};

//            timed_observations_query << "SELECT id, event_id, user_id, expert, start_time_s, end_time_s, start_time, end_time, tags, comments FROM timed_observations WHERE video_id = " << video.id << " ORDER BY user_id, start_time_s";

class DBObservation {
    public:
        int id;
        int event_id;
        int user_id;
        int expert;
        int start_time_s;
        int end_time_s;
        string start_time;
        string end_time;
        string tags;
        string comments;

        bool valid;

        DBObservation(MYSQL_ROW &observation_row) {
            id = atoi(observation_row[0]);
            event_id = atoi(observation_row[1]);
            user_id = atoi(observation_row[2]);
            expert  = atoi(observation_row[3]);
            start_time_s = atoi(observation_row[4]);
            end_time_s = atoi(observation_row[5]);
            start_time = string(observation_row[6]);
            end_time = string(observation_row[7]);

            if (observation_row[8] == NULL) {
                tags = "";
            } else {
                tags = string(observation_row[8]);
            }

            if (observation_row[9] == NULL) {
                comments = "";
            } else {
                comments = string(observation_row[9]);
            }

            valid = false;
        }

        string to_string() {
            ostringstream oss;
            oss  << "[OBSERVATION ";

            if (valid) oss << "-   VALID ";
            else       oss << "- INVALID ";
                 
            oss  << "- id: " << setw(6) << id
                 << ", event_id: " << setw(8) << event_id
                 << ", event: " << setw(20) << observation_types_map[event_id]
                 << ", user_id: " << setw(8) << user_id
                 << ", start_time_s: " << setw(12) << start_time_s 
                 << ", end_time_s: " << setw(12) << end_time_s
                 << ", expert: " << setw(2) << expert
//                 << ", tags: '" << tags << "'"
//                 << ", comments: '" << comments << "'"
                 << "]";
            return oss.str();
        }
};



int main(int argc, char** argv) {
    for (int i = 1; i < argc; i++) {
        if (is_arg(argv[i], "d")) {
            if (!argv[++i]) {
                log_messages.printf(MSG_CRITICAL, "%s requires an argument\n\n", argv[--i]);
                usage(argv[0]);
                exit(1);
            }
            int dl = atoi(argv[i]);
            log_messages.set_debug_level(dl);
            if (dl == 4) g_print_queries = true;
        }
    }

    vector<string> arguments(argv, argv + argc);

    bool no_db_update = argument_exists(arguments, "--no_db_update");

    initialize_boinc_database();
    initialize_wildlife_database();


    //Get the observation types
    mysql_query_check(wildlife_db_conn, "SELECT id, name FROM observation_types");
    MYSQL_RES *observation_types_result = mysql_store_result(wildlife_db_conn);

    MYSQL_ROW observation_types_row;
    while ((observation_types_row = mysql_fetch_row(observation_types_result)) != NULL) {
        int observation_id = atoi(observation_types_row[0]);
        string observation_name = string(observation_types_row[1]);

        observation_types_map[observation_id] = observation_name;
    }


    while (true) { //loop forever
        //This checks to see if there is a stop in place, if there is it will exit the work generator.
        check_stop_daemons();

        ostringstream unvalidated_video_query;
        unvalidated_video_query << "SELECT id, duration_s, species_id, location_id FROM video_2 WHERE (watch_count >= required_views) AND crowd_status = 'WATCHED'";

        mysql_query_check(wildlife_db_conn, unvalidated_video_query.str());
        MYSQL_RES *video_result = mysql_store_result(wildlife_db_conn);

        int count = 0;

        bool progress_updated = false;

        MYSQL_ROW video_row;
        while ((video_row = mysql_fetch_row(video_result)) != NULL) {
            DBVideo video(video_row);
            cout << video.to_string() << endl;

            ostringstream timed_observations_query;
            timed_observations_query << "SELECT id, event_id, user_id, expert, start_time_s, end_time_s, start_time, end_time, tags, comments FROM timed_observations WHERE video_id = " << video.id << " AND completed = true ORDER BY user_id, start_time_s";

            mysql_query_check(wildlife_db_conn, timed_observations_query.str());
            MYSQL_RES *timed_observations_result = mysql_store_result(wildlife_db_conn);

            MYSQL_ROW timed_observations_row;

            unordered_map<int, vector<DBObservation>> user_observations_map;    //a map from user ids to a list of their observations

            while ((timed_observations_row = mysql_fetch_row(timed_observations_result)) != NULL) {
                DBObservation observation(timed_observations_row);
//                cout << "    " << observation.to_string() << endl;

                user_observations_map[observation.user_id].push_back(observation);
            }

            if (user_observations_map.size() <= 1) {
                cout << "    Insufficent users (" << user_observations_map.size() << ")." << endl;

                //Users had watched the video but did not enter any events. Increase the required views.
                ostringstream increment_required_views_query;
                increment_required_views_query << "UPDATE video_2 SET required_views = LEAST(" << MAX_REQUIRED_VIEWS << ", required_views + 1) WHERE id = " << video.id << endl;

                mysql_query_check(wildlife_db_conn, increment_required_views_query.str());
//                MYSQL_RES *video_result = mysql_store_result(wildlife_db_conn);


            } else {
                vector<int> user_ids;
                vector< vector<DBObservation> > user_observations;
                for (unordered_map<int, vector<DBObservation> >::iterator it = user_observations_map.begin(); it != user_observations_map.end(); it++) {
                    user_ids.push_back( it->first );
                    user_observations.push_back( it->second );
                }

                for (unsigned int i = 0; i < user_ids.size(); i++) {
                    cout << "    user: " << user_ids.at(i) << endl;

                    for (unsigned int j = 0; j < user_ids.size(); j++) {
                        if (j == i) continue;

                        for (unsigned int k = 0; k < user_observations[i].size(); k++) {
                            for (unsigned int l = 0; l < user_observations[j].size(); l++) {
                                if (user_observations[i][k].event_id == user_observations[j][l].event_id
                                    && fabs(user_observations[i][k].start_time_s - user_observations[j][l].start_time_s) < TIME_CUTOFF
                                    && fabs(user_observations[i][k].end_time_s   - user_observations[j][l].end_time_s)   < TIME_CUTOFF) {

                                    user_observations[i][k].valid = true;
                                }
                            }
                        }
                    }
                }

                //Handle the easy case first, users which have all events marked as valid
                bool award_credit = true;
                for (unsigned int i = 0; i < user_ids.size(); i++) {
                    bool user_has_invalids = false;
                    int user_event_count = user_observations[i].size();
                    for (unsigned int k = 0; k < user_observations[i].size(); k++) {
                        cout << "        " << user_observations[i][k].to_string() << endl;
                        if (!user_observations[i][k].valid) user_has_invalids = true;
                    }
                    cout << "    " << user_ids[i] << " has invalids: " << user_has_invalids << ", event_count: " << user_event_count << endl;

                    if (!user_has_invalids && user_event_count > 0) {
                        cout << "    SHOULD AWARD " << video.duration_s << " CREDIT AND " << user_event_count << " VALID EVENTS TO USER " << user_ids[i] << endl;
                    } else {
                        award_credit = false;
                    }
                }

                if (award_credit) {
                    //update credit to users
                    for (unsigned int i = 0; i < user_ids.size(); i++) {
                        ostringstream update_credit_query;
                        update_credit_query << "UPDATE user SET bossa_credit_v2 = bossa_credit_v2 + " << video.duration_s << ", valid_events = valid_events + " << user_observations[i].size() << " WHERE id = " << user_ids[i];
                        mysql_query_check(boinc_db_conn, update_credit_query.str());

                        for (unsigned int j = 0; j < user_observations[i].size(); j++) {
                            //set status of timed_observation
                            ostringstream update_observation_query;
                            update_observation_query << "UPDATE timed_observations SET status = 'VALID' WHERE user_id = " << user_ids[i] << " AND id = " << user_observations[i][j].id << endl;
                            mysql_query_check(wildlife_db_conn, update_observation_query.str());
                        }
                    }

                    //update video_2
                    ostringstream update_video_query;
                    update_video_query << "UPDATE video_2 SET crowd_status = 'VALIDATED' WHERE id = " << video.id;
                    mysql_query_check(wildlife_db_conn, update_video_query.str());
                }
            }

            count++;
        }
        cout << "processed " << count << " videos." << endl;

        /**
         *  Update the progess table with new amounts of validated video
         */

        /**
         *  Update the progress table with new available video times.
         */

        log_messages.printf(MSG_DEBUG, "Sleeping...\n"); 
        sleep(300);
    }
}


