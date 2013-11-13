CMAKE_MINIMUM_REQUIRED(VERSION 2.8)
PROJECT(Wildlife_Client)

set(OpenCV_DIR /Users/kgoehner/repos/opencv/install/share/OpenCV/)
#set(CMAKE_FIND_LIBRARY_SUFFIXES ".a")
#set(CMAKE_EXE_LINKER_FLAGS "-static -static-libgcc -static-libstdc++")
#set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/cmake/Modules/")

#add_executable(wildlife_surf_collect wildlife_surf_collect)

find_package(OpenCV REQUIRED videostab nonfree)
find_package(Boost REQUIRED COMPONENTS program_options filesystem system)
#find_package(BOINC REQUIRED)

#include_directories("${PROJECT_SOURCE_DIR}/../include/" ${OpenCV_INCLUDE_DIR} ${BOINC_INCLUDE_DIR})
#target_link_libraries(wildlife_surf_collect pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_surf_combine wildlife_surf_combine)
include_directories("${PROJECT_SOURCE_DIR}/../include/" ${Boost_INCLUDE_DIR})
target_link_libraries(wildlife_surf_combine ${Boost_LIBRARIES} ${OpenCV_LIBS})
