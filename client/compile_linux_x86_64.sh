#/opt/local/bin/g++-mp-4.7 -std=c++11 \
clang++ \
    -D_BOINC_APP_ \
	-I../../ffmpeg \
    -I../../boinc \
    -I../../boinc/api \
    -I../../boinc/lib \
	wildlife_average.cpp \
    -o test \
    ../../ffmpeg/libavformat/libavformat.a \
	../../ffmpeg/libavcodec/libavcodec.a \
    ../../ffmpeg/libswscale/libswscale.a \
    ../../ffmpeg/libavutil/libavutil.a \
    ../../ffmpeg/libavdevice/libavdevice.a \
    ../../ffmpeg/libavfilter/libavfilter.a \
    ../../ffmpeg/libpostproc/libpostproc.a \
    ../../ffmpeg/libswresample/libswresample.a \
    ../../zlib-1.2.7/libz.a \
    ../../x264/libx264.a \
    -L../../boinc/api \
    -L../../boinc/lib \
    -lboinc_api \
    -lboinc \
    -pthread
