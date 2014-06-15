MESSAGE(STATUS "Freetype Path: " ${FREETYPE_DIR})

find_path(FREETYPE_INCLUDE_DIR_ft2build NAMES ft2build.h
    PATHS
    ${FREETYPE_DIR}/include
    ${FREETYPE_DIR}/include/freetype2)
MESSAGE(STATUS "Freetype Dir: " ${FREETYPE_INCLUDE_DIR_ft2build})

find_path(FREETYPE_INCLUDE_DIR_freetype2 NAMES freetype/config/ftheader.h
    PATHS
    ${FREETYPE_DIR}/include
    ${FREETYPE_DIR}/include/freetype2
    /usr/X11R6/include
    /usr/X11R6/include/freetype2
    /usr/include
    /usr/include/freetype2)
MESSAGE(STATUS "Freetype Dir: " ${FREETYPE_INCLUDE_DIR_freetype2})

find_library(FREETYPE_LIBRARY NAMES freetype libfreetype freetype219
    PATH_SUFFIXES lib
    PATHS
    ${FREETYPE_DIR})
MESSAGE(STATUS "Freetype Lib: " ${FREETYPE_LIBRARY})

if(FREETYPE_INCLUDE_DIR_ft2build AND FREETYPE_INCLUDE_DIR_freetype2)
    set(FREETYPE_INCLUDE_DIRS "${FREETYPE_INCLUDE_DIR_ft2build};${FREETYPE_INCLUDE_DIR_freetype2}")
endif()
set(FREETYPE_LIBRARIES "${FREETYPE_LIBRARY}")

mark_as_advanced(FREETYPE_LIBRARY FREETYPE_INCLUDE_DIR_freetype2 FREETYPE_INCLUDE_DIR_ft2build)
