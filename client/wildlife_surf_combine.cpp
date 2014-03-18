#include <iostream>
#include <opencv2/opencv.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <EventType.hpp>

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
            cout << temp->getId() << endl;
            cout << dir->path().string() << endl;
            FileStorage infile(dir->path().string(), FileStorage::READ);
            try {
                temp->read(infile);
            } catch(const exception &ex) {
                cerr << "main: " << ex.what() << endl;
                exit(1);
            }
            infile.release();
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

        string filename = path.string() + event_types.at(i)->getId() + ".desc";
        FileStorage outfile(filename, FileStorage::WRITE);
        try {
            event_types.at(i)->writeDescriptors(outfile);
            event_types.at(i)->writeKeypoints(outfile);
        } catch(const exception &ex) {
            cerr << "main: " << ex.what() << endl;
            exit(1);
        }
        outfile.release();
    }
}

EventType* getEventType(string id) {
    EventType *temp = NULL;
    for (int i=0; i<event_types.size(); i++) {
        if (event_types.at(i)->getId() == id) {
            temp = event_types.at(i);
        }
    }
    if (temp == NULL) {
        cout << "Creating new event: " << id << endl;
        temp = new EventType(id);
        event_types.push_back(temp);
    }
    return temp;
}
