CMAKE_MINIMUM_REQUIRED(VERSION 2.6)
PROJECT(Wildlife_Client)

set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/cmake/Modules/")

IF(OpenCV_DIR)
    MESSAGE(STATUS "OpenCV_DIR: " ${OpenCV_DIR})
ELSE()
    IF(APPLE)
        set(OpenCV_DIR ~/repos/opencv/install/share/OpenCV/)
    ELSE()
        set(OpenCV_DIR /home/kgoehner/opencv/install/share/OpenCV/)
    ENDIF()
    MESSAGE(STATUS "OpenCV_DIR: " ${OpenCV_DIR})
ENDIF()

IF (LINK_STATIC_LIBS)
    set(CMAKE_FIND_LIBRARY_SUFFIXES ".a")
    set(CMAKE_EXE_LINKER_FLAGS "-static -static-libgcc -static-libstdc++")
ENDIF()

IF (APPLE)
    MESSAGE(STATUS "Setting FLAGS for APPLE")
    set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -mmacosx-version-min=10.5.8")
    set(CMAKE_CXX_FLAGS "${CMAKE_CXX_FLAGS} -mmacosx-version-min=10.5.8")
ENDIF()


# Executables
#add_executable(wildlife_surf_collect wildlife_surf_collect)

find_package(OpenCV REQUIRED COMPONENTS videostab nonfree)
find_package(Boost REQUIRED COMPONENTS program_options filesystem system)
find_package(BOINC REQUIRED)

#include_directories("${PROJECT_SOURCE_DIR}/../include/" ${OpenCV_INCLUDE_DIR} ${BOINC_INCLUDE_DIR})
#target_link_libraries(wildlife_surf_collect pthread ${OpenCV_LIBRARIES} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_surf_collect wildlife_surf_collect)
include_directories("${PROJECT_SOURCE_DIR}/../include/" ${BOINC_INCLUDE_DIR})
target_link_libraries(wildlife_surf_collect ${OpenCV_LIBS} ${BOINC_API_LIBRARY} ${BOINC_APP_LIBRARIES})

#add_executable(wildlife_graphics_app wildlife_graphics_app)
#include_directories (${PROJECT_SOURCE_DIR}/../include/ ${BOINC_INCLUDE_DIR})
#target_link_libraries(wildlife_graphics_app ${BOINC_LIBRARIES} ${OpenCV_LIBS})

add_executable(wildlife_surf_combine wildlife_surf_combine)
include_directories(${PROJECT_SOURCE_DIR}/../include/ ${Boost_INCLUDE_DIR})
target_link_libraries(wildlife_surf_combine ${Boost_LIBRARIES} ${OpenCV_LIBS})

add_executable(wildlife_surf_analyze wildlife_surf_analyze)
include_directories(${PROJECT_SOURCE_DIR}/../include/ ${Boost_INCLUDE_DIR})
target_link_libraries(wildlife_surf_analyze ${Boost_LIBRARIES} ${OpenCV_LIBS})
