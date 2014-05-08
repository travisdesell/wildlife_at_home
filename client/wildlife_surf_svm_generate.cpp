#include <vector>

#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

#include <EventType.hpp>
#include <utils.hpp>

using namespace std;
using namespace cv;
using namespace boost;

string root_dir = "/projects/wildlife/feature_files/";
string output_file = "svm.dat";
string positive_desc_file = "posFeat.dat";
vector<string> positive_files;
vector<string> negative_files;
EventType positive_events("positive");
EventType negative_events("negative");
Point2f top_left(0,0);
Point2f bottom_right(1,1);
bool subtract_similar;
bool add_keypoints;

int main(int argc, char **argv) {
    namespace po = program_options;
    namespace fs = filesystem;
    namespace sys = system;

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help,h", "Show help menu")
        ("root,r", po::value<string>(), "Root feature directory")
        ("positive,p", po::value<vector<string> >(), "Tags for positive features")
        ("negative,n", po::value<vector<string> >(), "Tags for negative features")
        ("top_left,t", po::value<std::vector<float> >()->multitoken(), "Top-left box corner")
        ("bottom_right,b", po::value<std::vector<float> >()->multitoken(), "Bottom-right box corner")
        ("substract,s", po::value(&subtract_similar)->zero_tokens(), "Subtract similar features")
        ("add_keypoints,k", po::value(&add_keypoints)->zero_tokens(), "Add x and y positions to values")
        ("desc_output,do", po::value<string>(), "Filename for positive descriptor features")
        ("output,o", po::value<string>(), "Filename for SVM features")
    ;
    po::variables_map vm;
    po::store(po::parse_command_line(argc, argv, desc), vm);
    po::notify(vm);

    if (vm.count("help") || !vm.count("root") || !vm.count("positive")) {
        cout << desc << endl;
        return 1;
    }

    root_dir = vm["root"].as<string>();

    vector<string> positives = vm["positive"].as<vector<string> >();
    //cout << "Positives: " << positives.size() << endl;
    for(int i=0; i < positives.size(); i++) {
        positive_files.push_back(positives[i]);
    }

    if (vm.count("negative")) {
        vector<string> negatives = vm["negative"].as<vector<string> >();
        //cout << "Negaties: " << negatives.size() << endl;
        for(int i=0; i < negatives.size(); i++) {
            negative_files.push_back(negatives[i]);
        }
    } else {
        // Get all file names in directory.
        cout << "[ERROR] No negative names given!" << endl;
    }

    if(vm.count("top_left") && vm["top_left"].as<vector<float> >().size() == 2) {
        top_left.x = vm["top_left"].as<vector<float> >().at(0);
        top_left.y = vm["top_left"].as<vector<float> >().at(1);
        cout << "TL: " << top_left << endl;
    }

    if(vm.count("bottom_right") && vm["bottom_right"].as<vector<float> >().size() == 2) {
        bottom_right.x = vm["bottom_right"].as<vector<float> >().at(0);
        bottom_right.y = vm["bottom_right"].as<vector<float> >().at(1);
        cout << "BR: " << bottom_right << endl;
    }

    cout << "Subtract: " << subtract_similar << endl;
    cout << "Keypoints: " << add_keypoints << endl;

    if(vm.count("desc_output")) {
        positive_desc_file = vm["desc_output"].as<string>();
    }

    if(vm.count("output")) {
        output_file = vm["output"].as<string>();
    }

    cout << "Root       : '" << root_dir << "'" << endl;
    cout << "Positive   : " << endl;
    for(int i=0; i < positive_files.size(); i++) {
        cout << "\t" << positive_files[i] << endl;
    }
    cout << "Negative   : " << endl;
    for(int i=0; i < negative_files.size(); i++) {
        cout << "\t" << negative_files[i] << endl;
    }
    cout << "Output     : '" << output_file << "'" << endl;
    cout << "Desc Out   : '" << positive_desc_file << "'" << endl;

    // Load all positive files.
    for(int i=0; i < positive_files.size(); i++) {
        string filename = root_dir + positive_files[i] + ".desc";
        cv::FileStorage infile(filename, cv::FileStorage::READ);
        cout << "Loading from file: " << filename << endl;
        try {
            positive_events.setId(positive_files[i]); //Set Id to read in correct events.
            positive_events.read(infile, cv::Rect_<float>(top_left, bottom_right));
        } catch (const std::exception &ex) {
            cerr << "main positives: " << ex.what() << endl;
            exit(1);
        }
        infile.release();
    }

    // Load all negative files.
    for(int i=0; i < negative_files.size(); i++) {
        string filename = root_dir + negative_files[i] + ".desc";
        cv::FileStorage infile(filename, cv::FileStorage::READ);
        try {
            negative_events.setId(negative_files[i]); //Set Id to read in correct events.
            negative_events.read(infile);
        } catch (const std::exception &ex) {
            cerr << "main negatives: " << ex.what() << endl;
            exit(1);
        }
        infile.release();
    }

    cout << "Positive Size: " << positive_events.getKeypoints().size() << endl;
    cout << "Negative Size: " << negative_events.getKeypoints().size() << endl;

    // Subtract similar features
    if(subtract_similar) {
        Mat positive_desc = positive_events.getDescriptors();
        Mat negative_desc = negative_events.getDescriptors();
        vector<Point2f> positive_keypoints = positive_events.getKeypoints();
        Ptr<DescriptorMatcher> matcher = DescriptorMatcher::create("BruteForce");
        vector<DMatch> matches;
        matcher->match(positive_desc, negative_desc, matches);

        double totalDist = 0;
        double maxDist = 0;
        double minDist = 100;

        for(int i=0; i<matches.size(); i++) {
            double dist = matches[i].distance;
            totalDist += dist;
            if(dist < minDist) minDist = dist;
            if(dist > maxDist) maxDist = dist;
        }

        double avgDist = totalDist/matches.size();
        double stdDev = standardDeviation(matches, avgDist);

        cout << "Max dist: " << maxDist << endl;
        cout << "Min dist: " << minDist << endl;
        cout << "Avg dist: " << avgDist << endl;
        cout << "Std Dev : " << stdDev << endl;

        Mat newDesc;
        vector<Point2f> newKeypoints;
        // matcher->match(train, query, matches);
        for(int i=0; i<matches.size(); i++) {
            if(matches[i].distance > avgDist + 3 * stdDev) {
                //cout << "Index: " << matches[i].queryIdx << endl;
                newDesc.push_back(positive_desc.row(matches[i].queryIdx));
                newKeypoints.push_back(positive_keypoints.at(matches[i].queryIdx));
            }
        }

        positive_events.setDescriptors(newDesc);
        positive_events.setKeypoints(newKeypoints);

        cout << "Positive Size: " << positive_events.getKeypoints().size() << endl;
        cout << "Negative Size: " << negative_events.getKeypoints().size() << endl;
    } else {
        // Did not subtract out similar features
    }

    FileStorage storage_file(positive_desc_file, FileStorage::WRITE);
    positive_events.writeDescriptors(storage_file);
    storage_file.release();

    ofstream outfile;
    outfile.open(output_file.c_str(), ofstream::out);
    positive_events.writeForSVM(outfile, "+1", add_keypoints);
    negative_events.writeForSVM(outfile, "-1", add_keypoints);
    outfile.close();
}
