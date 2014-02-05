# - Find BOINC
# Find the native BOINC includes and libraries
#
#  BOINC_INCLUDE_DIR        - where to find boinc.h, etc.
#  BOINC_SERVER_FOUND       - true if libraries required for compiling boinc server code are found
#  BOINC_SERVER_LIBRARIES   - all the libraries required for compiling boinc server code
#  BOINC_APP_FOUND          - true if libraries required for compiling boinc apps are found 
#  BOINC_APP_LIBRARIES      - all the libraries required for compiling boinc apps

IF (BOINC_INCLUDE_DIR)
    # Already in cache, be silent
    SET(BOINC_FIND_QUIETLY TRUE)
ENDIF (BOINC_INCLUDE_DIR)

FIND_PATH(BOINC_INCLUDE_DIR boinc_api.h
    /usr/local/include/boinc
    /boinc/src/boinc
    /home/tdesell/boinc
    /Users/kgoehner/repos/boinc_build/boinc/api
    /Users/Kyle/Dropbox/Windows/include/boinc
    ~/BOINC_SOURCE
)
MESSAGE(STATUS "BOINC_INCLUDE_DIR: ${BOINC_INCLUDE_DIR}")

FIND_LIBRARY(BOINC_LIBRARY
    NAMES boinc libboinc
    PATHS /usr/local/lib /boinc/src/boinc /home/tdesell/boinc /Users/kgoehner/repos/boinc_build/boinc/mac_build/build/Deployment /Users/Kyle/Dropbox/Windows/win32_boinc ~/BOINC_SOURCE/
    PATH_SUFFIXES lib
)
MESSAGE(STATUS "BOINC_LIBRARY: ${BOINC_LIBRARY}")

FIND_LIBRARY(BOINC_CRYPT_LIBRARY
    NAMES boinc_crypt
    PATHS /usr/local/lib /boinc/src/boinc /home/tdesell/boinc /Users/kgoehner/repos/boinc_build/boinc/mac_build/build/Deployment ~/BOINC_SOURCE/
    PATH_SUFFIXES lib
)
MESSAGE(STATUS "BOINC_CRYPT_LIBRARY: ${BOINC_CRYPT_LIBRARY}")

FIND_LIBRARY(BOINC_API_LIBRARY
    NAMES boinc_api libboincapi_staticcrt
    PATHS /usr/local/lib /boinc/src/boinc /home/tdesell/boinc /Users/kgoehner/repos/boinc_build/boinc/mac_build/build/Deployment /Users/Kyle/Dropbox/Windows/win32_boinc ~/BOINC_SOURCE/
    PATH_SUFFIXES api
)
MESSAGE(STATUS "BOINC_API_LIBRARY: ${BOINC_API_LIBRARY}")

FIND_LIBRARY(BOINC_SCHED_LIBRARY
    NAMES sched
    PATHS /usr/local/lib /boinc/src/boinc /home/tdesell/boinc /Users/kgoehner/repos/boinc_build/boinc/mac_build/build/Deployment ~/BOINC_SOURCE/
    PATH_SUFFIXES sched
)
MESSAGE(STATUS "BOINC_SCHED_LIBRARY: ${BOINC_SCHED_LIBRARY}")

IF (BOINC_INCLUDE_DIR AND BOINC_LIBRARY AND BOINC_API_LIBRARY)
    add_definitions( -D_BOINC_APP_ )
    SET (BOINC_APP_FOUND TRUE)
    SET (BOINC_APP_LIBRARIES ${BOINC_API_LIBRARY} ${BOINC_LIBRARY})

    MESSAGE(STATUS "BOINC_APP_LIBRARIES: ${BOINC_APP_LIBRARIES}")
    MESSAGE(STATUS "BOINC_INCLUDE_DIR: ${BOINC_INCLUDE_DIR}")
ELSE (BOINC_INCLUDE_DIR AND BOINC_LIBRARY AND BOINC_API_LIBRARY)
    SET (BOINC_APP_FOUND FALSE)
    SET (BOINC_APP_LIBRARIES )
ENDIF (BOINC_INCLUDE_DIR AND BOINC_LIBRARY AND BOINC_API_LIBRARY)

IF (BOINC_INCLUDE_DIR AND BOINC_LIBRARY AND BOINC_API_LIBRARY AND BOINC_SCHED_LIBRARY AND BOINC_CRYPT_LIBRARY)
    add_definitions( -D_BOINC_SERVER_ )
    SET(BOINC_SERVER_FOUND TRUE)
    SET( BOINC_SERVER_LIBRARIES ${BOINC_SCHED_LIBRARY} ${BOINC_LIBRARY} ${BOINC_API_LIBRARY} ${BOINC_CRYPT_LIBRARY})

    MESSAGE(STATUS "Found BOINC_SERVER_LIBRARIES: ${BOINC_SERVER_LIBRARIES}")
    MESSAGE(STATUS "BOINC_INCLUDE_DIR: ${BOINC_INCLUDE_DIR}")
ELSE (BOINC_INCLUDE_DIR AND BOINC_LIBRARY AND BOINC_API_LIBRARY AND BOINC_SCHED_LIBRARY AND BOINC_CRYPT_LIBRARY)
    SET(BOINC_FOUND FALSE)
    SET( BOINC_LIBRARIES )
ENDIF (BOINC_INCLUDE_DIR AND BOINC_LIBRARY AND BOINC_API_LIBRARY AND BOINC_SCHED_LIBRARY AND BOINC_CRYPT_LIBRARY)

MARK_AS_ADVANCED(
    BOINC_APP_LIBRARIES
    BOINC_SCHED_LIBRARIES
    BOINC_INCLUDE_DIR
    )
