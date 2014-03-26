CMAKE_MINIMUM_REQUIRED(VERSION 2.6)
PROJECT(Wildlife_Client)

set(CMAKE_MODULE_PATH ${CMAKE_MODULE_PATH} "${PROJECT_SOURCE_DIR}/cmake/Modules/")

IF(OpenCV_DIR)
    MESSAGE(STATUS "OpenCV_DIR: " ${OpenCV_DIR})
ELSE()
    IF(APPLE)
        set(OpenCV_DIR ~/Dropbox/osx/opencv/share/OpenCV)
    ELSEIF(WIN_32)
        set(OpenCV_DIR ~/Dropbox/Windows/opencv/build)
    ELSE()
        set(OpenCV_DIR ~/opencv/share/OpenCV)
    ENDIF()
    MESSAGE(STATUS "OpenCV_DIR: " ${OpenCV_DIR})
ENDIF()

IF(APPLE)
    MESSAGE(STATUS "Setting FLAGS for APPLE")
    set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -mmacosx-version-min=10.5")
    set(CMAKE_CXX_FLAGS "${CMAKE_CXX_FLAGS} -mmacosx-version-min=10.5")
    set(CMAKE_C_FLAGS_RELEASE "${CMAKE_C_FLAGS} -O3 -DNDEBUG")
    set(CMAKE_CXX_FLAGS_RELEASE "${CMAKE_CXX_FLAGS} -O3 -DNDEBUG")
ELSEIF(UNIX)
    MESSAGE(STATUS "Seting FLAGS for UNIX")
    set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -mieee-fp")
    set(CMAKE_CXX_FLAGS "${CMAKE_CXX_FLAGS} -mieee-fp")
    set(CMAKE_C_FLAGS_RELEASE "${CMAKE_C_FLAGS} -O1 -DNDEBUG")
    set(CMAKE_CXX_FLAGS_RELEASE "${CMAKE_CXX_FLAGS} -O1 -DNDEBUG")
ENDIF()

# Executables
#add_executable(wildlife_surf_collect wildlife_surf_collect)

set(Boost_USE_MULTITHREADED OFF)

#find_package(OpenCV REQUIRED COMPONENTS nonfree core)
find_package(OpenCV REQUIRED nonfree)
find_package(BOINC REQUIRED)
find_package(Boost COMPONENTS program_options filesystem system REQUIRED)
find_package(OpenGL REQUIRED)
find_package(GLUT REQUIRED)
find_package(Freetype REQUIRED)
find_package(FTGL REQUIRED)


MESSAGE(STATUS "OpenCV Include Dirs: ${OpenCV_INCLUDE_DIRS}")
MESSAGE(STATUS "OpenCV Libs: ${OpenCV_LIBS}")

#include_directories("${PROJECT_SOURCE_DIR}/../include/" ${OpenCV_INCLUDE_DIRS} ${BOINC_INCLUDE_DIR})
#target_link_libraries(wildlife_surf_collect pthread ${OpenCV_LIBS} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_surf_collect
    wildlife_surf_collect
    ../source/Event
    ../source/EventType
    ../source/VideoType
    ../source/boinc_utils)
include_directories("${PROJECT_SOURCE_DIR}/../include/" ${OpenCV_INCLUDE_DIRS} ${BOINC_INCLUDE_DIR})
target_link_libraries(wildlife_surf_collect ${OpenCV_LIBS} ${BOINC_APP_LIBRARIES})

#add_executable(wildlife_surf_collect wildlife_surf_collect2)
#include_directories("${PROJECT_SOURCE_DIR}/../include/" ${OpenCV_INCLUDE_DIR} ${BOINC_INCLUDE_DIR})
#target_link_libraries(wildlife_surf_collect ${OpenCV_LIBS} ${BOINC_API_LIBRARY} ${BOINC_APP_LIBRARIES})

add_executable(wildlife_graphics_app
    wildlife_graphics_app
    ../source/boinc_utils)
include_directories ("${PROJECT_SOURCE_DIR}/../include/" ${OPENGL_INCLUDE_DIRS} ${GLUT_INCLUDE_DIRS} ${FREETYPE_INCLUDE_DIRS} ${FTGL_INCLUDE_DIRS})
target_link_libraries(wildlife_graphics_app
    ${OpenCV_LIBS}
    ${BOINC_APP_LIBRARIES}
    ${BOINC_GRAPHICS_LIBRARY}
    ${OPENGL_LIBRARIES}
    ${GLUT_LIBRARY}
    ${FREETYPE_LIBS}
    ${FTGL_LIBRARIES})

IF(Boost_FOUND AND NOT APPLE)
    add_executable(wildlife_surf_combine
        wildlife_surf_combine
        ../source/EventType)
    include_directories("${PROJECT_SOURCE_DIR}/../include/" ${Boost_INCLUDE_DIR})
    target_link_libraries(wildlife_surf_combine
        ${Boost_LIBRARIES}
        ${Boost_PROGRAM_OPTIONS_LIBRARY}
        ${OpenCV_LIBS})
ENDIF()

#add_executable(wildlife_surf_analyze wildlife_surf_analyze)
#include_directories(${PROJECT_SOURCE_DIR}/../include/ ${Boost_INCLUDE_DIR})
#target_link_libraries(wildlife_surf_analyze ${Boost_LIBRARIES} ${OpenCV_LIBS})
