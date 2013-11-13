#include <iostream>
#include <opencv2/opencv.hpp>
#include <opencv2/nonfree/features2d.hpp>
#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <wildlife_surf.hpp>

using namespace std;
using namespace cv;

EventType* getEventType(string);

vector<EventType*> event_types;

int main(int argc, char **argv) {
    namespace po = boost::program_options;
    namespace fs = boost::filesystem;

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help", "Show help menu")
        ("root", po::value<string>(), "Root feature directory")
        ("tag", po::value<string>(), "Tag of features to be combined")
    ;
    po::variables_map vm;
    po::store(po::parse_command_line(argc, argv, desc), vm);
    po::notify(vm);

    if (vm.count("help")) {
        cout << desc << endl;
        return 1;
    }

    string working_directory = vm["root"].as<string>() + vm["tag"].as<string>();
    cout << working_directory << endl;
    for (fs::recursive_directory_iterator end, dir(working_directory); dir != end; ++dir) {
        cout << "Test" << endl;
        if(dir->path().extension().string() == ".yaml") {
            string id = dir->path().stem().string();
            EventType *temp = getEventType(id);
            cout << temp->id << endl;

            cout << dir->path().string() << endl;
            FileStorage infile(dir->path().string(), FileStorage::READ);
            cout << "Opened file" << endl;
            if (infile.isOpened()) {
                Mat temp_descriptors;
                infile[temp->id] >> temp_descriptors;
                temp->descriptors.push_back(temp_descriptors);
                cout << "Added " << temp->descriptors.rows << " to " << temp->id << endl;
                infile.release();
            } else {
                cout << "[ERROR] File could not be opened: " << dir->path() << endl;
                exit(1);
            }
        }
    }

    exit(0);

	string featFileName(argv[1]);

	Mat totalDescriptors;
	for (int i=2; i<argc; i++) {
		Mat descriptors_file;
		string nextFile(argv[i]);
		FileStorage infile(nextFile, FileStorage::READ);
		if (infile.isOpened()) {
			read(infile["Descriptors"], descriptors_file);
			infile.release();
			totalDescriptors.push_back(descriptors_file);
		} else {
			std::cout << "Feature file " << featFileName << " does not exist." << std::endl;
		}
	}
	
	if (totalDescriptors.rows > 0) {
		
		FileStorage outfile(featFileName + ".feats", FileStorage::WRITE);
		write(outfile, "Descriptors", totalDescriptors);
		outfile.release();
	}
}

/* @function getEventType */
EventType* getEventType(string id) {
    EventType *temp = NULL;
    for (int i=0; i<event_types.size(); i++) {
        if (event_types.at(i)->id == id) {
            cout << "Event found!" << endl;
            temp = event_types.at(i);
        }
    }
    if (temp == NULL) {
        cout << "Creating new event." << endl;
        temp = new EventType;
        temp->id = id;
        event_types.push_back(temp);
    }
    return temp;
}
