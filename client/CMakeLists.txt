CMAKE_MINIMUM_REQUIRED(VERSION 2.6)
PROJECT(Wildlife_Client)

set(CMAKE_EXE_LINKER_FLAGS "-static-libgcc -static-libstdc++")
set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/cmake/Modules/")

#find_package(Boost REQUIRED)
find_package(OpenCV REQUIRED)
find_package(BOINC RREQUIRED)

include_directories(${OpenCV_INCLUDE_DIR} ${BOINC_INCLUDE_DIR})
add_executable(wildlife_surf_collect wildlife_surf_collect)
target_link_libraries(wildlife_surf_collect pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})
