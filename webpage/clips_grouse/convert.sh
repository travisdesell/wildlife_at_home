for i in ./*/*.wmv; do
    echo $i
    echo ""

#    avconv -y -i $i "${i/.wmv/.mp4}"
#    avconv -y -i $i "${i/.wmv/.ogv}"
    avconv -y -i $i -vcodec libx264 -preset slow -vf 'movie=/video/wildlife/watermark.png [watermark]; [in][watermark] overlay=10:10 [out]' -b:v 200k "${i/.wmv/.mp4}"
    avconv -y -i "${i/.wmv/.mp4}" -vcodec libtheora -acodec libvorbis -ab 160000 -g 30 "${i/.wmv/.ogv}"

    echo ""
    echo ""
done
