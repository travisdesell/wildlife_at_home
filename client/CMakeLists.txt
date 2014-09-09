cmake_minimum_required (VERSION 2.6)
project (WILDLIFE_SURF_DETECTOR)

#This may need to be updated or Linux/Windows
set(CMAKE_LIBRARY_PATH ${CMAKE_LIBRARY_PATH} /usr/local/lib /opt/local/lib)

#set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/../undvc_common/cmake/Modules/")

#this adds the undvc_common FindX.cmake modules to look for BOINC and OpenCV
#set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/undvc_common/cmake/Modules/")

#unset(BOINC_INCLUDE_DIR)

#find_package(BOINC)
#message( "boinc include dir: ${BOINC_INCLUDE_DIR}")

#include_directories(${BOINC_INCLUDE_DIR})
#include_directories(${BOINC_INCLUDE_DIR}/api)
#include_directories(${BOINC_INCLUDE_DIR}/lib)

find_package(Boost REQUIRED COMPONENTS system thread)

find_package(OpenCV COMPONENTS features2d nonfree flann imgproc highgui legacy core)

include_directories (${PROJECT_SOURCE_DIR})

include_directories(${OpenCV_INCLUDE_DIR})
include_directories(${OpenCV_INCLUDE_DIR}/../)

message(STATUS "opencv libraries: ${OpenCV_LIBRARIES}")

#include_directories(${BOINC_INCLUDE_DIR})
#include_directories(${OPENGL_INCLUDE_DIRS} ${GLUT_INCLUDE_DIRS} ${Boost_INCLUDE_DIR})

#link_directories(${OPENGL_LIBRARY_DIRS} ${GLUT_LIBRARY_DIRS} ${Boost_LIBRARY_DIRS} ${OpenCV_LIBRARY_DIRS})

#add_executable(wildlife_surf wildlife_surf)
#target_link_libraries(wildlife_surf ${Boost_SYSTEM_LIBRARY} ${Boost_THREAD_LIBRARY} pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_feature_extractor wildlife_feature_extractor)
target_link_libraries(wildlife_feature_extractor ${Boost_SYSTEM_LIBRARY} ${Boost_THREAD_LIBRARY} pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_feature_comparison wildlife_feature_comparison file_io)
target_link_libraries(wildlife_feature_comparison ${Boost_SYSTEM_LIBRARY} ${Boost_THREAD_LIBRARY} pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_feature_matcher wildlife_feature_matcher file_io)
target_link_libraries(wildlife_feature_matcher ${Boost_SYSTEM_LIBRARY} ${Boost_THREAD_LIBRARY} pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})


