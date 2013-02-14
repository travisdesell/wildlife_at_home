include_directories(${OpenCV_INCLUDE_DIR})
include_directories(${OpenCV_INCLUDE_DIR}/../)
#include_directories(${BOINC_INCLUDE_DIR})
#include_directories(${BOINC_INCLUDE_DIR}/api)
#include_directories(${BOINC_INCLUDE_DIR}/lib)

find_package(Boost REQUIRED COMPONENTS system thread)
find_package(OpenGL REQUIRED)
find_package(GLUT REQUIRED)

include_directories(${OPENGL_INCLUDE_DIRS} ${GLUT_INCLUDE_DIRS} ${Boost_INCLUDE_DIR})

link_directories(${OPENGL_LIBRARY_DIRS} ${GLUT_LIBRARY_DIRS} ${Boost_LIBRARY_DIRS})

add_definitions( -DUSE_OPENGL )

add_executable(wildlife wildlife)
target_link_libraries(wildlife ${BOINC_APP_LIBRARIES} ${OpenCV_LIBRARIES} ${OPENGL_LIBRARIES} ${GLUT_LIBRARIES} ${Boost_SYSTEM_LIBRARY} ${Boost_THREAD_LIBRARY})
