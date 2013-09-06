#/opt/local/bin/g++-mp-4.7 -std=c++11 \
g++ \
    -D_BOINC_APP_ \
	-I../../ffmpeg \
    -I../../boinc \
    -I../../boinc/api \
    -I../../boinc/lib \
	wildlife_average.cpp \
    -o wildlife_average \
    ../../ffmpeg/libavformat/libavformat.a \
	../../ffmpeg/libavcodec/libavcodec.a \
    ../../ffmpeg/libswscale/libswscale.a \
    ../../ffmpeg/libavutil/libavutil.a \
    ../../ffmpeg/libavdevice/libavdevice.a \
    ../../ffmpeg/libavfilter/libavfilter.a \
    ../../ffmpeg/libpostproc/libpostproc.a \
    ../../ffmpeg/libswresample/libswresample.a \
    ../../opencv/build/3rdparty/lib/libzlib.a \
    ../../x264/libx264.a \
    -L/Users/deselt/Documents/Dropbox/software/boinc/mac_build/build/Deployment \
    -lboinc_api \
    -lboinc \
    -lbz2


