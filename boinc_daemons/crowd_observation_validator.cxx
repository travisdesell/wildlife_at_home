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

bool no_db_update;


/**
 *  This wrapper makes for much more informative error messages when doing MYSQL queries
 */
#define mysql_query_check(conn, query) __mysql_check (conn, query, __FILE__, __LINE__)

void __mysql_check(MYSQL *conn, string query, const char *file, const int line) {
    if (no_db_update && query.substr(0, 6).compare("SELECT") != 0 ) {   //if no_db_update specified don't execute non-select queries
        cout << "NOT QUERYING DATABASE (no_db_update specfied): '" << query << "'" << endl;
        return;
    } else {
        cout << "QUERY: '" << query << "'" << endl;
    }

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
        int watch_count;
        int required_views;

        DBVideo(MYSQL_ROW &video_row) {
            id = atoi(video_row[0]);
            duration_s = atoi(video_row[1]);
            species_id = atoi(video_row[2]);
            location_id = atoi(video_row[3]);
            watch_count = atoi(video_row[4]);
            required_views = atoi(video_row[5]);
        }

        string to_string() {
            ostringstream oss;
            oss << "[VIDEO - video_id: " << setw(8) << id
                << ", duration_s: " << setw(6) << duration_s
                << ", species_id: " << setw(3) << species_id
                << ", location_id: " << setw(3) << location_id
                << ", watch_count: " << setw(3) << watch_count 
                << ", required_views: " << setw(3) << required_views << "]";
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


void require_another_view(int video_id, int n_user_ids) {
    ostringstream update_video_query;
    update_video_query << "UPDATE video_2 SET required_views = LEAST(" << MAX_REQUIRED_VIEWS << ", required_views + 1)";
    //if required_views == MAX_REQUIRED_VIEWS then we could not get a consensus
    if (n_user_ids >= MAX_REQUIRED_VIEWS) {
        cout << "        Users viewing video (" << n_user_ids << ") >= MAX_REQUIRED_VIEWS(" << MAX_REQUIRED_VIEWS << ")" << endl;
        cout << "        SOMETHING WEIRD HAS GONE ON." << endl;
        update_video_query << ", crowd_status = 'NO_CONSENSUS'";
    }
    update_video_query << " WHERE id = " << video_id;
    mysql_query_check(wildlife_db_conn, update_video_query.str());
}

void award_credit(const DBVideo &video, const vector<int> &user_ids, const vector<int> &user_valid_count, const vector< vector<DBObservation> > &user_observations) {
    for (unsigned int i = 0; i < user_ids.size(); i++) {
        ostringstream update_credit_query;

        //only award video credit if there's at least one valid event
        int video_credit_s = 0;
        if (user_valid_count[i] > 0) video_credit_s = video.duration_s;
       
        update_credit_query << "UPDATE user SET bossa_total_credit = bossa_total_credit + " << video_credit_s << ", valid_events = valid_events + " << user_valid_count[i] << " WHERE id = " << user_ids[i];
        cout << update_credit_query.str() << endl;

        mysql_query_check(boinc_db_conn, update_credit_query.str());

        //should probably check to see if the user has a team...

        ostringstream team_query;
        team_query << "SELECT teamid FROM user WHERE id = " << user_ids[i];
        cout << team_query.str() << endl;

        mysql_query_check(boinc_db_conn, team_query.str());
        MYSQL_RES *team_res = mysql_store_result(boinc_db_conn);
        MYSQL_ROW team_row = mysql_fetch_row(team_res);

        if (atoi(team_row[0]) > 0) {
            ostringstream team_credit_query;
            team_credit_query << "UPDATE team SET bossa_total_credit = bossa_total_credit + " << video_credit_s << " WHERE id = " << team_row[0];
            cout << team_credit_query.str() << endl;

            mysql_query_check(boinc_db_conn, team_credit_query.str());
        }


        for (unsigned int j = 0; j < user_observations[i].size(); j++) {
            //set status of timed_observation

            ostringstream update_observation_query;
            if (user_observations[i][j].valid) {
                update_observation_query << "UPDATE timed_observations SET status = 'VALID' WHERE user_id = " << user_ids[i] << " AND id = " << user_observations[i][j].id;
            } else {
                update_observation_query << "UPDATE timed_observations SET status = 'INVALID' WHERE user_id = " << user_ids[i] << " AND id = " << user_observations[i][j].id;
            }
            mysql_query_check(wildlife_db_conn, update_observation_query.str());
        }
    }

    //update video_2
    ostringstream update_video_query;
    update_video_query << "UPDATE video_2 SET crowd_status = 'VALIDATED' WHERE id = " << video.id;
    mysql_query_check(wildlife_db_conn, update_video_query.str());
}

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

    no_db_update = argument_exists(arguments, "--no_db_update");

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
        unvalidated_video_query << "SELECT id, duration_s, species_id, location_id, watch_count, required_views FROM video_2 WHERE (watch_count >= required_views) AND crowd_status = 'WATCHED'";

        mysql_query_check(wildlife_db_conn, unvalidated_video_query.str());
        MYSQL_RES *video_result = mysql_store_result(wildlife_db_conn);

        int count = 0;

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

            int total_observations = 0;
            //Create a map of user ids to a vector of their events.
            while ((timed_observations_row = mysql_fetch_row(timed_observations_result)) != NULL) {
                DBObservation observation(timed_observations_row);
                cout << "    " << observation.to_string() << endl;

                user_observations_map[observation.user_id].push_back(observation);
                total_observations++;
            }
            cout << "    total observations: " << total_observations << endl;

            //get a vector of user ids, a vector of observations for each user
            vector<int> user_ids;
            vector< vector<DBObservation> > user_observations;
            for (unordered_map<int, vector<DBObservation> >::iterator it = user_observations_map.begin(); it != user_observations_map.end(); it++) {
                user_ids.push_back( it->first );
                user_observations.push_back( it->second );
            }

            //get vectors for the total, valid and invalid event counts, while marking the
            //observations valid or invalid
            vector<int> user_valid_count(user_ids.size(), 0);
            vector<int> user_invalid_count(user_ids.size(), 0);
            vector<int> user_total_count(user_ids.size(), 0);
            bool has_invalid = false;
            int max_events_user = 0;
            int max_total_count = 0;

            for (unsigned int i = 0; i < user_ids.size(); i++) {
                for (unsigned int j = 0; j < user_observations[i].size(); j++) {
                    cout << "user_observations[" << i << "][" << j << "]: INITIALIZED TO FALSE" << endl;
                    user_observations[i][j].valid = false;

                    //uncompleted events are invalid
                    if (user_observations[i][j].event_id == 0 || user_observations[i][j].start_time_s < 0 || user_observations[i][j].end_time_s < 0) {
                        cout << "       " << user_observations[i][j].to_string() << " INVALID because of unentered values." << endl;
                        break;
                    }

                    for (unsigned int k = 0; k < user_ids.size(); k++) {
                        if (k == i) continue;   //don't compare a user to itself

                        for (unsigned int l = 0; l < user_observations[k].size(); l++) {
                            //an event is valid if at least one other user's event matches it
                            //within the time cutoff
                            if (user_observations[i][j].event_id == user_observations[k][l].event_id
                                    && fabs(user_observations[i][j].start_time_s - user_observations[k][l].start_time_s) < TIME_CUTOFF
                                    && fabs(user_observations[i][j].end_time_s   - user_observations[k][l].end_time_s)   < TIME_CUTOFF) {

                                user_observations[i][j].valid = true;
                                cout << "       MATCH: " << user_observations[i][j].to_string() << endl;
                                cout << "          TO: " << user_observations[k][l].to_string() << endl;
                                break;  //found a match for this event so we can quit comparing it
                            }
                        }
                    }

                    if (user_observations[i][j].valid) user_valid_count[i]++;
                    else {
                        user_invalid_count[i]++;
                        has_invalid = true;
                    }
                }

                user_total_count[i] = user_observations[i].size();
                if (user_total_count[i] > max_total_count) {
                    max_events_user = i;
                    max_total_count = user_total_count[i];
                }

                cout << "    user: " << setw(8) << user_ids.at(i) << " - valid: " << setw(3) << user_valid_count[i] << ", invalid: " << setw(3) << user_invalid_count[i] << ", total: " << setw(3) << user_total_count[i] << endl;
            }

            /*
             * Given MAX_REQUIRED_VIEWS = 5
             *
             * 1 VIEW:
             *      PROBLEM - EXIT AND REPORT ERROR

             * 2 VIEWS: 
             *      if ALL OBSERVATIONS VALID
             *          MARK VALID AND CONTINUE
             *      else // SOME OBSERVATIONS INVALID
             *          GET ANOTHER VIEW

             * 3-4 VIEWS:
             *      if ALL OBSERVATIONS VALID
             *          MARK VALID AND CONTINUE
             *      else if USER WITH MOST EVENTS HAS ALL VALID EVENTS
             *          MARK VALIDS VALID
             *          MARK INVALIDS INVALID
             *          MARK VIDEO FINISHED
             *      else if USER WITH MOST EVENTS HAS INVALID EVENTS
             *          //potentially some events missed
             *          GET ANOTHER VIEW

             * 5 VIEWS:
             *      if ALL OBSERVATIONS VALID
             *          MARK VALID AND CONTINUE
             *      else if USER WITH MOST EVENTS HAS ALL VALID EVENTS
             *          MARK VALIDS VALID
             *          MARK INVALIDS INVALID
             *          MARK VIDEO FINISHED
             *      else if USER WITH MOST EVENTS HAS INVALID EVENTS
             *          MARK VALIDS VALID
             *          MARK INVALIDS INVALID
             *          MARK VIDEO AS NO CONSENSUS
             */

            int n_user_observations = user_observations_map.size();

            if (n_user_observations <= 1) {
                //0 or 1 views is a problem (shouldn't happen), print info about the video and observations then exit.
                //should be handled manually.
                cout << "    Insufficent users (" << user_observations_map.size() << ")." << endl;
                cout << "    There was only one user id in the user observations map, this means it was only viewed by one user." << endl;
                cout << "    This needs to be handled manually." << endl;
//                require_another_view(video.id, user_ids.size());
                exit(1);
            } else if (n_user_observations == 2) {
                if (!has_invalid && total_observations > 0) {
                    award_credit(video, user_ids, user_valid_count, user_observations);
                } else {
                    require_another_view(video.id, user_ids.size());
                }
            } else {
                if (!has_invalid && total_observations > 0) {   //no invalid observations
                    award_credit(video, user_ids, user_valid_count, user_observations);
                } else if (user_valid_count[max_events_user] == user_total_count[max_events_user]) {    //user with most observations has all valid ones
                    award_credit(video, user_ids, user_valid_count, user_observations);
                } else {
                    if (user_observations_map.size() == 5) award_credit(video, user_ids, user_valid_count, user_observations);
                    require_another_view(video.id, user_ids.size());    // will set to NO_CONSENSUS for case 5
                    //award credit for what we have if we've hit the max views views
                }
            }

            count++;
        }
        cout << "processed " << count << " videos." << endl;

        log_messages.printf(MSG_DEBUG, "Sleeping...\n"); 
        sleep(300);
    }
}


