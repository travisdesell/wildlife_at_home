CMAKE_MINIMUM_REQUIRED(VERSION 2.6)
PROJECT(Wildlife_Client)

set(CMAKE_FIND_LIBRARY_SUFFIXES ".a")
set(CMAKE_EXE_LINKER_FLAGS "-static -static-libgcc -static-libstdc++")
set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/cmake/Modules/")

add_executable(wildlife_surf_collect wildlife_surf_collect)

#find_package(Boost REQUIRED)
find_package(OpenCV REQUIRED)
find_package(BOINC REQUIRED)

include_directories(${OpenCV_INCLUDE_DIR} ${BOINC_INCLUDE_DIR})
target_link_libraries(wildlife_surf_collect pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})
