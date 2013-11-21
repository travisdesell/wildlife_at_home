wildlife_at_home
================

Source code for the Wildlife@Home project.

To compile:

1. First initialize and checkout submodule:
    git submodule init
    git submodule update

2. Create cmake build directory and run cmake:
    mkdir build
    cd build
    cmake ..
    make

Software Requirements:
    cmake
    opencv
    BOINC

Note that building BOINC on OSX now requires setting CFLAGS="-mmacosx-min-version=10.8" (Something prior to 10.9)


On Ubuntu you can get all of boost with:
    sudo apt-get install libboost-all-dev
