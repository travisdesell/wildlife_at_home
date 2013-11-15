#include <iostream>
#include <opencv2/opencv.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <wildlife_surf.hpp>

using namespace std;
using namespace cv;

Mat getDifferenceMatrix(Mat, Mat);
EventType* getEventType(string);

string tag_dir;
string root_dir = "/projects/wildlife/feature_files/";
string combined_dir = "combined";
string output_dir = "analyzed";
vector<string> set_a;
vector<string> set_b;
EventType event_type_a;
EventType event_type_b;
EventType event_type_out;

int main(int argc, char **argv) {
    namespace po = boost::program_options;
    namespace fs = boost::filesystem;
    namespace sys = boost::system;

    event_type_a.id = "set_a";
    event_type_b.id = "set_b";

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help", "Show help menu")
        ("tag", po::value<string>(), "Tag of features to be analyzed")
        ("root", po::value<string>(), "Root feature directory")
        ("combined", po::value<string>(), "Directory name for combined features")
        ("output", po::value<string>(), "Directory name for analyzed features")
        ("set_a", po::value< vector<string> >(), "First group of features")
        ("set_b", po::value< vector<string> >(), "Second group of features")
    ;
    po::variables_map vm;
    po::store(po::parse_command_line(argc, argv, desc), vm);
    po::notify(vm);

    if (vm.count("help") || !vm.count("tag") || !vm.count("set_a") || !vm.count("set_b")) {
        cout << desc << endl;
        return 1;
    }

    tag_dir = vm["tag"].as<string>();
    vector<string> temp = vm["set_a"].as< vector<string> >();
    set_a.insert(set_a.end(), temp.begin(), temp.end());
    temp = vm["set_b"].as< vector<string> >();
    set_b.insert(set_b.end(), temp.begin(), temp.end());
    if (vm.count("root")) root_dir = vm["root"].as<string>();
    if (vm.count("combined")) combined_dir = vm["combined"].as<string>();
    if (vm.count("output")) output_dir = vm["output"].as<string>();

    string combined_feats_directory = root_dir + tag_dir + "/" + combined_dir;
    cout << "Combined feats directory: " << combined_feats_directory << endl;
    for (fs::recursive_directory_iterator end, dir(combined_feats_directory); dir != end; ++dir) {
        if(dir->path().extension().string() == ".desc") {
            string id = dir->path().stem().string();
            EventType *temp = getEventType(id);
            if(temp == NULL) continue;

            cout << "ID: "<< temp->id << endl;
            cout << "File path: " << dir->path().string() << endl;
            FileStorage infile(dir->path().string(), FileStorage::READ);
            if (infile.isOpened()) {
                Mat temp_descriptors;
                infile[id] >> temp_descriptors;
                temp->descriptors.push_back(temp_descriptors);
                cout << "Added " << temp->descriptors.rows << " descriptors to " << temp->id << endl;
                infile.release();
            } else {
                cout << "[ERROR] File could not be opened: " << dir->path() << endl;
                exit(1);
            }
        }
    }

    Mat differenceMat = getDifferenceMatrix(event_type_a.descriptors, event_type_b.descriptors);
    cout << differenceMat.rows << "x" << differenceMat.cols << endl;
    /*
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
    }*/
}

Mat getDifferenceMatrix(Mat desc1, Mat desc2) {
    string matchers[] = {"BruteForce", "BruteForce-SL2", "BruteForce-L1", "BruteForce-Hamming", "BruteForce-Hamming(2)", "FlannBased"};
    Ptr<DescriptorMatcher> matcher = DescriptorMatcher::create(matchers[0]);
    Mat trainDesc, queryDesc;
    vector<DMatch> matches;

    if(desc1.rows > desc2.rows) {
        trainDesc = desc2;
        queryDesc = desc1;
    } else {
        trainDesc = desc1;
        queryDesc = desc2;
    }

    Mat distance(queryDesc.rows, trainDesc.rows, CV_32FC1, Scalar::all(0));
    Mat mask(queryDesc.rows, trainDesc.rows, CV_8UC1, Scalar::all(1));
    do {
    matcher->match(queryDesc, trainDesc, matches, mask);

    // Fill mask matrix and distance matrix
    for(int i=0; i<matches.size(); i++) {
        mask.at<uchar>(matches.at(i).queryIdx, matches.at(i).trainIdx) = 0;
        distance.at<float>(matches.at(i).queryIdx, matches.at(i).trainIdx) = matches.at(i).distance;
    }

    } while(matches.size() > 0);
    return distance;
}

EventType* getEventType(string id) {
    for (int i=0; i<set_a.size(); i++) {
        if (set_a.at(i) == id) {
            return &event_type_a;
        }
    }
    for (int i=0; i<set_b.size(); i++) {
        if (set_b.at(i) == id) {
            return &event_type_b;
        }
    }
    return NULL;
}
