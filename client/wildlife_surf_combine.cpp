#include <iostream>
#include <opencv2/opencv.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <wildlife_surf.hpp>

using namespace std;
using namespace cv;

EventType* getEventType(string);

string tag_dir;
string root_dir = "/projects/wildlife/feature_files/";
string output_dir = "combined";
vector<EventType*> event_types;

int main(int argc, char **argv) {
    namespace po = boost::program_options;
    namespace fs = boost::filesystem;
    namespace sys = boost::system;

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help", "Show help menu")
        ("tag", po::value<string>(), "Tag of features to be combined")
        ("root", po::value<string>(), "Root feature directory")
        ("output", po::value<string>(), "Directory name for combined features")
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

    if (vm.count("output")) {
        output_dir = vm["output"].as<string>();
    }

    string working_directory = root_dir + tag_dir;
    cout << working_directory << endl;
    for (fs::recursive_directory_iterator end, dir(working_directory); dir != end; ++dir) {
        if(dir->path().extension().string() == ".desc") {
            string id = dir->path().stem().string();
            EventType *temp = getEventType(id);
            cout << temp->id << endl;

            cout << dir->path().string() << endl;
            FileStorage infile(dir->path().string(), FileStorage::READ);
            if (infile.isOpened()) {
                Mat temp_descriptors;
                infile[temp->id] >> temp_descriptors;
                temp->descriptors.push_back(temp_descriptors);
                cout << "Added " << temp->descriptors.rows << " descriptors to " << temp->id << endl;
                infile.release();
            } else {
                cout << "[ERROR] File could not be opened: " << dir->path() << endl;
                exit(1);
            }
        }
    }

    string combined_feats_directory = working_directory + "/" + output_dir + "/";
    for (int i=0; i<event_types.size(); i++) {
        string pathname = combined_feats_directory;

        fs::path path(pathname);
        sys::error_code returnedError;
        fs::create_directories(path, returnedError);

        if(returnedError) {
            cout << "[ERROR] wildlife_surf_collect_assimilation_policy failed with 'cannot create directories error', directory: " << pathname << endl;
            return 1;
        }

        string filename = path.string() + event_types.at(i)->id + ".desc";
        FileStorage outfile(filename, FileStorage::WRITE);
        if (outfile.isOpened()) {
            outfile << event_types.at(i)->id << event_types.at(i)->descriptors;
            outfile.release();
        } else {
            cout << "[ERROR] File could not be opened: " << filename << endl;
            exit(1);
        }
    }
}

/* @function getEventType */
EventType* getEventType(string id) {
    EventType *temp = NULL;
    for (int i=0; i<event_types.size(); i++) {
        if (event_types.at(i)->id == id) {
            temp = event_types.at(i);
        }
    }
    if (temp == NULL) {
        cout << "Creating new event: " << id << endl;
        temp = new EventType;
        temp->id = id;
        event_types.push_back(temp);
    }
    return temp;
}
