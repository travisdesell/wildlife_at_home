#include <iostream>
#include <opencv2/nonfree/features2d.hpp>

using namespace std;
using namespace cv;

void printUsage();

int main(int argc, char **argv) {
	if (argc < 3) {
		printUsage();
		return -1;
	}
	
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

/** @function printUsage */
void printUsage() {
	std::cout << "Usage: cv_combine <outfile> <feats> [more feats]" << std::endl;
}