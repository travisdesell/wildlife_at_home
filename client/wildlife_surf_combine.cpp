#include <set>
#include <vector>
#include <iostream>
#include <algorithm>
#include <cstdlib>
#include <ctime>

#include <opencv2/opencv.hpp>
#include <opencv2/nonfree/features2d.hpp>

#include <boost/lexical_cast.hpp>
#include <boost/algorithm/string.hpp>
#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <EventType.hpp>

using namespace std;
using namespace cv;
using namespace boost;

enum cross_validation {ALL, LEAVE_ONE_OUT, TWO_FOLD};

EventType* getEventType(string, int);

string tag_dir;
string root_dir = "/projects/wildlife/feature_files/";
string species_dir;
string location_dir;
unsigned int cross_validation = ALL;
string output_dir = "combined";
vector<EventType*> *event_types;

int get_rand(const int i) {
    return rand()%i;
}

int main(int argc, char **argv) {
    namespace po = program_options;
    namespace fs = filesystem;
    namespace sys = system;
    namespace alg = algorithm;

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help,h", "Show help menu")
        ("tag,t", po::value<string>(), "Tag of features to be combined")
        ("species,s", po::value<string>(), "Species of features to be combined")
        ("location,l", po::value<string>(), "Location of features to be combined")
        ("root,r", po::value<string>(), "Root feature directory")
        ("cv", po::value<string>(), "Cross-validation type")
        ("output,o", po::value<string>(), "Directory name for combined features")
    ;
    po::variables_map vm;
    po::store(po::parse_command_line(argc, argv, desc), vm);
    po::notify(vm);

    if (vm.count("help") || !vm.count("tag")) {
        cout << desc << endl;
        return 1;
    }

    tag_dir = vm["tag"].as<string>();

    if (vm.count("root")) {
        root_dir = vm["root"].as<string>();
    }

    if (vm.count("species")) {
        species_dir = vm["species"].as<string>();
    }

    if (vm.count("location")) {
        location_dir = vm["location"].as<string>();
    }

    if (vm.count("output")) {
        output_dir = vm["output"].as<string>();
    }

    if (vm.count("cv")) {
        string cv = vm["cv"].as<string>();
        alg::to_upper(cv);
        if(cv == "LEAVE_ONE_OUT") {
            cross_validation = LEAVE_ONE_OUT;
        } else if(cv == "TWO_FOLD") {
            cross_validation = TWO_FOLD;
        } else {
            cross_validation = ALL;
        }
    }

    cout << "Root       : '" << root_dir << "'" << endl;
    cout << "Tag        : '" << tag_dir << "'" << endl;
    cout << "Species    : '" << species_dir << "'" << endl;
    cout << "Location   : '" << location_dir << "'" << endl;
    cout << "Output     : '" << output_dir << "'" << endl;
    cout << "CV         : '" << cross_validation << "'" << endl;

    string working_directory = root_dir + tag_dir;
    if(!species_dir.empty()) working_directory += "/" + species_dir;
    if(!location_dir.empty()) working_directory += "/" + location_dir;
    cout << working_directory << endl;

    // Count videos
    set<string> vid_set;
    for (fs::recursive_directory_iterator end, dir(working_directory); dir != end; ++dir) {
        if(dir->path().extension()  == ".desc") {
            string filename = dir->path().branch_path().string();
            //cout << filename << endl;
            vid_set.insert(filename);
        }
    }
    cout << "Vid Count: " << vid_set.size() << endl;

    // Shuffle Files
    srand(time(NULL));
    vector<string> vid_files(vid_set.begin(), vid_set.end());
    random_shuffle(vid_files.begin(), vid_files.end(), get_rand);

    // Split Files
    unsigned int num_vid_groups = 1;
    unsigned int *group_sizes;
    switch(cross_validation) {
        case ALL:
            num_vid_groups = 1;
            group_sizes = new unsigned int[num_vid_groups];
            group_sizes[0] = vid_files.size();
            break;
        case LEAVE_ONE_OUT:
            num_vid_groups = 2;
            group_sizes = new unsigned int[num_vid_groups];
            group_sizes[0] = vid_files.size()-1;
            group_sizes[1] = 1;
            break;
        case TWO_FOLD:
            num_vid_groups = 2;
            group_sizes = new unsigned int[num_vid_groups];
            for(int i=0; i<num_vid_groups; i++) {
                group_sizes[i] = vid_files.size()/num_vid_groups;
            }
            break;
        default:
            cout << "ERROR: Unknown Cross Validation Type" << endl;
            exit(1);
    }


    // Collect Events
    event_types = new vector<EventType*>[num_vid_groups];
    unsigned int current_index = 0;
    for(int i=0; i<num_vid_groups; i++) {
        for(int j=0; j<group_sizes[i]; j++) {
            cout << "********** Vid File: '" << vid_files[current_index] << "'" << endl;
            for(fs::recursive_directory_iterator end, dir(vid_files[current_index]); dir != end; ++dir) {
                if(dir->path().extension() == ".desc") {
                    string id = dir->path().stem().string();
                    EventType *temp = getEventType(id, i);
                    //cout << temp->getId() << endl;
                    //cout << dir->path().string() << endl;
                    //cout << dir->path().branch_path().stem().string() << endl;
                    FileStorage infile(dir->path().string(), FileStorage::READ);
                    try {
                        temp->read(infile);
                    } catch(const std::exception &ex) {
                        cerr << "main: " << ex.what() << endl;
                        exit(1);
                    }
                    infile.release();
                }
            }
            current_index++;
        }

        string combined_feats_directory = output_dir + "/" + lexical_cast<string>(i) + "/";
        for (vector<EventType*>::iterator it = event_types[i].begin(); it != event_types[i].end(); ++it) {
            string pathname = combined_feats_directory;

            fs::path path(pathname);
            bool created = fs::create_directories(path);

            if(!created) {
                cout << "[ERROR] wildlife_surf_collect_assimilation_policy failed with 'cannot create directories error', directory: " << pathname << endl;
                //return 1;
                //return 0;
            }

            string filename = path.string() + (*it)->getId() + ".desc";
            cout << "Writing events to: '" << filename << "'" << endl;
            FileStorage outfile(filename, FileStorage::WRITE);
            try {
                (*it)->writeDescriptors(outfile);
                (*it)->writeKeypoints(outfile);
            } catch(const std::exception &ex) {
                cerr << "main: " << ex.what() << endl;
                exit(1);
            }
            outfile.release();
            delete(*it);
        }
    }
    delete(group_sizes);
    //delete(event_types);
    return 0;
}

EventType* getEventType(string id, int set_num) {
    EventType *temp = NULL;
    for (int i=0; i<event_types[set_num].size(); i++) {
        if (event_types[set_num].at(i)->getId() == id) {
            temp = event_types[set_num].at(i);
        }
    }
    if (temp == NULL) {
        cout << "Creating new event: " << id << endl;
        temp = new EventType(id);
        event_types[set_num].push_back(temp);
    }
    return temp;
}
