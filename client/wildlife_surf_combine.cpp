#include <iostream>
#include <opencv2/opencv.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <EventType.hpp>

using namespace std;
using namespace cv;

enum class CV {ALL, LEAVE_ONE_OUT, TWO_FOLD};

EventType* getEventType(string);

string tag_dir;
string root_dir = "/projects/wildlife/feature_files/";
string cross_validation = ALL;
string output_dir = "combined";
vector<EventType*> event_types;

int main(int argc, char **argv) {
    namespace po = boost::program_options;
    namespace fs = boost::filesystem;
    namespace sys = boost::system;

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help,h", "Show help menu")
        ("tag,t", po::value<string>(), "Tag of features to be combined")
        ("root,r", po::value<string>()->required(), "Root feature directory")
        ("cv", po::value<string>()->required(), "Cross-validation type")
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

    if (vm.count("output")) {
        output_dir = vm["output"].as<string>();
    }

    if (vm.count("cv")) {
        if(to_upper(vm["cv"].as<string>()) == "LEAVE_ONE_OUT") {
            cross_validation = CV.LEAVE_ONE_OUT;
        } else if(to_upper(vm["cv".as<string>()]) == "TWO_FOLD") {
            cross_validation = CV.TWO_FOLD;
        } else {
            cross_validation = CV.ALL;
        }
    }

    cout << "Tag: '" << tag_dir << "'" << endl;
    cout << "Root: '" << root_dir << "'" << endl;
    cout << "Output: '" << output_dir << "'" << endl;
    cout << "CV: '" << cross_validation << "'" << endl;

    string working_directory = root_dir + tag_dir;
    cout << working_directory << endl;

    // Count videos
    int vid_count = count_if(
            directory_iterator(working_directory),
            directory_iterator(),
            bind(static_cast<bool(*)(const path&)>(is_directory),
                bind(&directory_entry::path, _1)));
    cout << "Vid Count: " << vid_count << endl;

    // Collect Events
    for (fs::recursive_directory_iterator end, dir(working_directory); dir != end; ++dir) {
        if(dir->path().extension().string() == ".desc") {
            string id = dir->path().stem().string();
            EventType *temp = getEventType(id);
            cout << temp->getId() << endl;
            cout << dir->path().string() << endl;
            cout << dir->path().branch_path().string() << endl;
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
