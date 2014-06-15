#include "boinc_utils.hpp"

using namespace std;

string getBoincFilename(string filename) throw(runtime_error) {
    string resolvedPath;
#ifdef _BOINC_APP_
    if(boinc_resolve_filename_s(filename.c_str(), resolvedPath)) {
        cerr << "Could not resolve filename '" << filename.c_str() << "'" << endl;
        throw runtime_error("Boinc could not resolve filename");
    }
#endif
    return resolvedPath;
}
