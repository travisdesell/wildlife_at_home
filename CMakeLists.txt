cmake_minimum_required (VERSION 2.6)
project (WILDLIFE_AT_HOME)
# The version number.
set (WILDLIFE_AT_HOME_VERSION_MAJOR 1)
set (WILDLIFE_AT_HOME_VERSION_MINOR 0)

set(PLATFORM 64)

set(CMAKE_CXX_FLAGS "-g -Wall -O3 -funroll-loops -ftree-vectorize -msse3")

#This may need to be updated or Linux/Windows
set(CMAKE_LIBRARY_PATH ${CMAKE_LIBRARY_PATH} /opt/local/lib)

#this adds the undvc_common FindX.cmake modules to look for BOINC and OpenCV
set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/undvc_common/cmake/Modules/")

#
#   Just in case we need Boost
#
#set(BOOST_LIB_PREFIX "lib")
#set(Boost_LIB_PREFIX "lib")
#set(BOOST_COMPILER "-vc100")
#set(Boost_COMPILER "-vc100")
#set(Boost_ADDITIONAL_VERSIONS "1.47" "1.47.0")
#find_package(Boost REQUIRED system filesystem)
#include_directories (${Boost_INCLUDE_DIR})

find_package(BOINC)
find_package(OpenCV)

add_subdirectory(client)
