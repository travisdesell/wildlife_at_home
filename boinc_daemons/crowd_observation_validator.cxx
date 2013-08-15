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

#define CUSHION 2000
    // maintain at least this many unsent results
#define REPLICATION_FACTOR  1

const char* app_name = NULL;
const char* out_template_file = "wildlife_out.xml";

DB_APP app;
int start_time;
int seqno;

using namespace std;

class Observation {
     public:
        const int id;
        const int bird_leave;
        const int bird_return;
        const int bird_presence;
        const int bird_absence;
        const int predator_presence;
        const int nest_defense;
        const int nest_success;
        const int corrupt;
        const int too_dark;
        const int chick_presence;
        const int interesting;
        const string status;
        string new_status;
        const int user_id;
        const int video_segment_id;

        Observation(int id,
                    int bird_leave,
                    int bird_return,
                    int bird_presence,
                    int bird_absence,
                    int predator_presence,
                    int nest_defense,
                    int nest_success,
                    int corrupt,
                    int too_dark,
                    int chick_presence,
                    int interesting,
                    string status,
                    int user_id,
                    int video_segment_id) : id(id), bird_leave(bird_leave), bird_return(bird_return), bird_presence(bird_presence), bird_absence(bird_absence),
                                            predator_presence(predator_presence), nest_defense(nest_defense), nest_success(nest_success),
                                            corrupt(corrupt), too_dark(too_dark), chick_presence(chick_presence), interesting(interesting),
                                            status(status), user_id(user_id), video_segment_id(video_segment_id) {

        }

        string to_string() {
//            log_messages.printf(MSG_DEBUG, "id: %d, bird_leave: %d, bird_return: %d, bird_presence: %d, bird_absence: %d, predator_presence: %d, nest_defense: %d, nest_success: %d, corrupt: %d, too_dark: %d, chick_presence: %d, interesting: %d, status: %s, video_segment_id: %d\n", id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, nest_defense, nest_success, corrupt, too_dark, chick_presence, interesting, status.c_str(), video_segment_id);
            ostringstream oss;

            oss << "id: "                  << id
                << ", bird_leave: "        << bird_leave
                << ", bird_return: "       << bird_return
                << ", bird_presence: "     << bird_presence
                << ", bird_absence: "      << bird_absence
                << ", predator_presence: " << predator_presence
                << ", nest_defense: "      << nest_defense
                << ", nest_success: "      << nest_success
                << ", corrupt: "           << corrupt
                << ", too_dark: "          << too_dark
                << ", chick_presence: "    << chick_presence
                << ", interesting: "       << interesting
                << ", status: "            << status
                << ", video_segment_id: "  << video_segment_id;

            return oss.str();
        }

        bool possible_canonical() {
            if (too_dark == 1) return true;
            if (corrupt == 1) return true;

            if (bird_leave == 0) return false;
            if (bird_return == 0) return false;
            if (bird_presence == 0) return false;
            if (bird_absence == 0) return false;
            if (predator_presence == 0) return false;
            if (nest_defense == 0) return false;
            if (nest_success == 0) return false;
            if (chick_presence == 0) return false;

            return true;
        }

        bool matches(Observation *other) {
            if (corrupt == 1) {
                if (other->corrupt == 1) return true;
                else return false;
            } else if (corrupt == 0 && other->corrupt == 1) return false;

            if (too_dark == 1) {
                if (other->too_dark == 1) return true;
                else return false;
            } else if (too_dark == 0 && other->too_dark == 1) return false;

            if (bird_leave != 0                 && other->bird_leave != 0          && bird_leave              != other->bird_leave) return false;
            if (bird_return != 0                && other->bird_return != 0         && bird_return             != other->bird_return) return false;
            if (bird_presence != 0              && other->bird_presence != 0       && bird_presence           != other->bird_presence) return false;
            if (bird_absence != 0               && other->bird_absence != 0        && bird_absence            != other->bird_absence) return false;
            if (predator_presence != 0          && other->predator_presence != 0   && predator_presence       != other->predator_presence) return false;
            if (nest_defense != 0               && other->nest_defense != 0        && nest_defense            != other->nest_defense) return false;
            if (nest_success != 0               && other->nest_success != 0        && nest_success            != other->nest_success) return false;
            if (chick_presence != 0             && other->chick_presence != 0      && chick_presence          != other->chick_presence) return false;

            return true;
        }
};


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

    initialize_boinc_database();
    initialize_wildlife_database();

    while (true) { //loop forever
        //This checks to see if there is a stop in place, if there is it will exit the work generator.
        check_stop_daemons();

        ostringstream unvalidated_video_query;
    //    unvalidated_video_query << "SELECT id FROM video_segment_2 WHERE crowd_obs_count > 0"; // << " AND crowd_status != 'VALIDATED'";
        unvalidated_video_query << "SELECT id, duration_s, species_id, location_id FROM video_segment_2 WHERE crowd_obs_count >= required_views AND crowd_status = 'WATCHED'";

        mysql_query_check(wildlife_db_conn, unvalidated_video_query.str());
        MYSQL_RES *video_segment_result = mysql_store_result(wildlife_db_conn);

        int count = 0;

        MYSQL_ROW video_segment_row;
        while ((video_segment_row = mysql_fetch_row(video_segment_result)) != NULL) {
            int video_segment_id = atoi(video_segment_row[0]);
            int duration_s = atoi(video_segment_row[1]);
            int species_id = atoi(video_segment_row[2]);
            int location_id = atoi(video_segment_row[3]);

            ostringstream observation_query;
            observation_query << "SELECT id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, nest_defense, nest_success, corrupt, too_dark, chick_presence, interesting, status, user_id, video_segment_id FROM observations WHERE video_segment_id = " << video_segment_id;

            mysql_query_check(wildlife_db_conn, observation_query.str());
            MYSQL_RES *observation_result = mysql_store_result(wildlife_db_conn);
            MYSQL_ROW observation_row;

            /**
             *  Read the observations for the video segment from the database.
             */
            vector< Observation* > observations;
            while ((observation_row = mysql_fetch_row(observation_result)) != NULL) {
                int id                  = atoi(observation_row[0]);
                int bird_leave          = atoi(observation_row[1]);
                int bird_return         = atoi(observation_row[2]);
                int bird_presence       = atoi(observation_row[3]);
                int bird_absence        = atoi(observation_row[4]);
                int predator_presence   = atoi(observation_row[5]);
                int nest_defense        = atoi(observation_row[6]);
                int nest_success        = atoi(observation_row[7]);
                int corrupt             = atoi(observation_row[8]);
                int too_dark            = atoi(observation_row[9]);
                int chick_presence      = atoi(observation_row[10]);
                int interesting         = atoi(observation_row[11]);
                string status           = observation_row[12];
                int user_id             = atoi(observation_row[13]);
                int video_segment_id    = atoi(observation_row[14]);

                Observation *observation = new Observation(id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, 
                                                           nest_defense, nest_success, corrupt, too_dark, chick_presence, interesting,
                                                           status, user_id, video_segment_id);

                observations.push_back(observation);
            }

            /**
             *  For each observation, see how many other observations it matches.
             */
            vector<int> matches(observations.size(), 0);
            for (uint32_t i = 0; i < observations.size(); i++) {
                for (uint32_t j = 0; j < observations.size(); j++) {
                    if (i == j) continue;

                    if (observations[i]->matches(observations[j])) matches[i]++;
                }
            }

            int max_match = 0;
            int canonical = -1;
            for (uint32_t i = 0; i < observations.size(); i++) {
                if (matches[i] >= 2 && matches[i] > max_match) {
                    if (observations[i]->possible_canonical()) {
                        canonical = i;
                        max_match = matches[i];
                    }
                }
            }

            log_messages.printf(MSG_DEBUG, "canonical: %d\n", canonical);

            for (uint32_t i = 0; i < observations.size(); i++) {
                ostringstream oss;

                if (canonical < 0) {
                    observations[i]->new_status = "UNVALIDATED";
                    oss << "UNVALIDATED";
                } else if (canonical == (int)i) {
                    observations[i]->new_status = "CANONICAL";
                    oss << "  CANONICAL";
                } else if (observations[i]->matches(observations[canonical])) {
                    observations[i]->new_status = "VALID";
                    oss << "      VALID";
                } else {
                    observations[i]->new_status = "INVALID";
                    oss << "    INVALID";
                }

                oss << ", MATCHES: " << matches[i] << ", ";
                oss << observations.at(i)->to_string();

                log_messages.printf(MSG_DEBUG, "%s\n", oss.str().c_str());
            }

            log_messages.printf(MSG_DEBUG, "\n");

            if (canonical < 0) {
                ostringstream vs2_query;

                vs2_query << "UPDATE video_segment_2 SET required_views = IF(required_views < 5, required_views + 1, required_views)";
                if (observations.size() >= 5) vs2_query << ", crowd_status = 'NO_CONSENSUS'";
                vs2_query << " WHERE id = " << video_segment_id;

                log_messages.printf(MSG_DEBUG, "%s\n", vs2_query.str().c_str());
                mysql_query_check(wildlife_db_conn, vs2_query.str());
            } else {

                for (uint32_t i = 0; i < observations.size(); i++) {
                    log_messages.printf(MSG_DEBUG, "observations[%d]->status    : '%s'\n", i, observations[i]->status.c_str());
                    log_messages.printf(MSG_DEBUG, "observations[%d]->new_status: '%s'\n", i, observations[i]->new_status.c_str());

                    if (0 != observations[i]->status.compare(observations[i]->new_status)) {
                        //the status has changed, update the observation in the database
                        ostringstream user_query, observation_query;

                        if (0 == observations[i]->new_status.compare("VALID") || 0 == observations[i]->new_status.compare("CANONICAL")) {
                            //The status was valid, award credit and increment the users valid observations count
                            user_query << "UPDATE user SET valid_observations = valid_observations + 1, bossa_total_credit = bossa_total_credit + " << duration_s << " WHERE id = " << observations[i]->user_id;

                            log_messages.printf(MSG_DEBUG, "%s\n", user_query.str().c_str());
                            mysql_query_check(boinc_db_conn, user_query.str());

                        } else if (0 == observations[i]->new_status.compare("INVALID")) {
                            //The status was invalid, increment the users invalid observations count
                            user_query << "UPDATE user SET invalid_observations = invalid_observations + 1 WHERE id = " << observations[i]->user_id;

                            log_messages.printf(MSG_DEBUG, "%s\n", user_query.str().c_str());
                            mysql_query_check(boinc_db_conn, user_query.str());
                        }

                        observation_query << "UPDATE observations SET status = '" << observations[i]->new_status << "' WHERE id = " << observations[i]->id;
                        log_messages.printf(MSG_DEBUG, "%s\n", observation_query.str().c_str());
                        mysql_query_check(wildlife_db_conn, observation_query.str());

                    }
                }

                ostringstream vs2_query;
                vs2_query << "UPDATE video_segment_2 SET crowd_status = 'VALIDATED' WHERE id = " << video_segment_id;

                log_messages.printf(MSG_DEBUG, "%s\n", vs2_query.str().c_str());
                mysql_query_check(wildlife_db_conn, vs2_query.str());

                ostringstream progress_query;
                progress_query << "UPDATE progress SET validated_video_s = validated_video_s + " << duration_s << " WHERE progress.species_id = " << species_id << " AND progress.location_id = " << location_id;

                log_messages.printf(MSG_DEBUG, "%s\n", progress_query.str().c_str());
                mysql_query_check(wildlife_db_conn, vs2_query.str());
            }

            for (uint32_t i = 0; i < observations.size(); i++) {
                delete observations[i];
            }

            count++;
        }

        log_messages.printf(MSG_DEBUG, "Sleeping...\n"); 
        sleep(30);
    }
}


