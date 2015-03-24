cmake_minimum_required (VERSION 2.6)
project (WILDLIFE_AT_HOME)
# The version number.
set (WILDLIFE_AT_HOME_VERSION_MAJOR 1)
set (WILDLIFE_AT_HOME_VERSION_MINOR 0)

# These will make cmake use clang and clang++ instead of gcc and g++
#SET (CMAKE_C_COMPILER             "/usr/bin/clang")
SET (CMAKE_C_FLAGS                "-Wall -g -O2 -std=c99 -Wl,-Bstatic")
SET (CMAKE_CXX_FLAGS              "-Wall -g -O2 -std=c++0x")

unset(Boost_INCLUDE_DIR CACHE)
unset(Boost_LIBRARY_DIRS CACHE)

#This may need to be updated or Linux/Windows
set(CMAKE_LIBRARY_PATH /home/tdesell/boost_1_53_0/stage/lib ${CMAKE_LIBRARY_PATH} /Users/deselt/Dropbox/software/opencv/lib /opt/local/lib)

#this adds the undvc_common FindX.cmake modules to look for BOINC and OpenCV
set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/undvc_common/cmake/Modules/")
MESSAGE(STATUS "PROJECT_SOURCE_DIR: " ${PROJECT_SOURCE_DIR})

include_directories (${PROJECT_SOURCE_DIR})

add_definitions( -D_BOINC_ )

#add_subdirectory(undvc_common)


find_package(MySQL REQUIRED)
find_package(BOINC REQUIRED)
find_package(OpenCV REQUIRED videostab nonfree)

MESSAGE(STATUS "OpenCV_LIBRARIES: " ${OpenCV_LIBRARIES})
MESSAGE(STATUS "OpenCV_INCLUDES: " ${OpenCV_INCLUDE_DIR})

find_path(BOINC_INCLUDE_DIR config.h PATHS /boinc/src/boinc/ /boinc/)

include_directories(
    ${PROJECT_SOURCE_DIR}
    ${PROJECT_SOURCE_DIR}/../include
    ${PROJECT_SOURCE_DIR}/undvc_common
	${BOINC_INCLUDE_DIR}
    ${BOINC_INCLUDE_DIR}/api
    ${BOINC_INCLUDE_DIR}/db
    ${BOINC_INCLUDE_DIR}/lib
    ${BOINC_INCLUDE_DIR}/sched
	${MYSQL_INCLUDE_DIR}
    ${OpenCV_INCLUDE_DIR}
)

#find_package(CURL REQUIRED)
find_package(OpenSSL REQUIRED)

include_directories(
    ${Boost_INCLUDE_DIR}
    ${BOINC_INCLUDE_DIR}/tools/
    ${OpenCV_INCLUDE_DIR}
)

#add_executable(wildlife_validator
    #    ${BOINC_INCLUDE_DIR}/sched/validator
    #    ${BOINC_INCLUDE_DIR}/sched/validate_util
    #    ${BOINC_INCLUDE_DIR}/sched/validate_util2
    #    wildlife_validation_policy)
#target_link_libraries(wildlife_validator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES})

#add_executable(wildlife_surf_collect_validator
#    ${BOINC_INCLUDE_DIR}/sched/validator
#    ${BOINC_INCLUDE_DIR}/sched/validate_util
#    ${BOINC_INCLUDE_DIR}/sched/validate_util2
#    ${PROJECT_SOURCE_DIR}/../source/EventType
#    wildlife_surf_collect_validation_policy)
#target_link_libraries(wildlife_surf_collect_validator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${OpenCV_LIBRARIES})

#add_executable(wildlife_assimilator
    #    ${BOINC_INCLUDE_DIR}/sched/assimilator
    #    ${BOINC_INCLUDE_DIR}/sched/validate_util
    #    wildlife_assimilation_policy)
#target_link_libraries(wildlife_assimilator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${Boost_LIBRARIES})

#add_executable(wildlife_surf_collect_assimilator
#    ${BOINC_INCLUDE_DIR}/sched/assimilator
#    ${BOINC_INCLUDE_DIR}/sched/validate_util
#    ${PROJECT_SOURCE_DIR}/../source/EventType
#    wildlife_surf_collect_assimilation_policy)
#target_link_libraries(wildlife_surf_collect_assimilator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${Boost_LIBRARIES} ${OpenCV_LIBRARIES})

add_executable(wildlife_bgsub_validator
    ${BOINC_INCLUDE_DIR}/sched/validator
    ${BOINC_INCLUDE_DIR}/sched/validate_util
    ${BOINC_INCLUDE_DIR}/sched/validate_util2
    wildlife_bgsub_validation_policy)
target_link_libraries(wildlife_bgsub_validator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES})

add_executable(wildlife_work_generator wildlife_work_generator)
target_link_libraries(wildlife_work_generator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${OPENSSL_LIBRARIES} ${Boost_LIBRARIES})

add_executable(crowd_observation_validator crowd_observation_validator)
target_link_libraries(crowd_observation_validator undvc_common ${BOINC_LIBRARIES} ${MYSQL_LIBRARIES} ${OPENSSL_LIBRARIES})
