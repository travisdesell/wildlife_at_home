#!/usr/bin/python

import MySQLdb as sql
import subprocess
import hashlib
import sys
import os

und_watermark_file = "/share/wildlife/und_watermark.png"
duck_watermark_file = "/share/wildlife/duck_watermark.png"

connection = None

try:
    # Server, username, password, database
    db_password = "NOPE"
    db_user = "wildlife_user"
    db_name = "wildlife_video"
    connection = sql.connect("wildlife.und.edu", db_user, db_password, db_name)

    cursor = connection.cursor()
    cursor.execute("SELECT VERSION()")
    #connection.commit()

    version = cursor.fetchone()

    print "Database version: %s " % version

    # NOTE: The item queried should already be marked as 'WATERMARKING' by the
    # head node otherwise we should probably return an error and exit.
    if len(sys.argv) > 1:
        video_id = sys.argv[1]
        cursor.execute("SELECT id, location_id, archive_filename, watermarked_filename, processing_status, duration_s FROM video_2 WHERE processing_status = 'UNWATERMARKED' AND id = %s LIMIT 1", [video_id])
    else:
        cursor.execute("SELECT id, location_id, archive_filename, watermarked_filename, processing_status, duration_s FROM video_2 WHERE processing_status = 'UNWATERMARKED' LIMIT 1")

    row = cursor.fetchone()
    print row

    video_id = row[0]
    location_id = row[1]
    archive_filename = row[2]
    watermarked_filename = row[3]
    processing_status = row[4]
    duration_s = row[5]

    print "Video ID: '%s'" % video_id
    print "Archive Filename: '%s'" % archive_filename
    print "Watermarked Filename: '%s'" % watermarked_filename
    print "Processing Status: '%s'" % processing_status
    print "Duration (s): %d" % duration_s

    # TODO: If the video is valid, set update it's processing status to prevent
    # another process from taking it.

    base_directory = watermarked_filename[:watermarked_filename.rfind("/")]

    # NOTE: This can error if another process creates the same directory
    # between the check and the makedirs call. (I added a final catch to check
    # for this)
    if not os.path.exists(base_directory):
        try:
            os.makedirs(base_directory)
        except OSError as e:
            if e.errno != errno.EEXIST:
                raise

    # Watermark mp4 file
    if location_id == 7:
        command = "ffmpeg -y -i %s -i %s -i %s -vcodec h264 -preset veryslow -crf 26 -an -filter_complex '[1:v]scale=87:40 [und]; [0:v][und]overlay=x=10:y=10 [und_marked]; [2:v]scale=79:50 [duck]; [und_marked][duck]overlay=x=10;y=(main_h-overlay_h)' %s.mp4" % (archive_filename, und_watermark_file, duck_watermark_file, watermarked_filename)
    else:
        command = "ffmpeg -y -i %s -i %s -vcodec h264 -preset veryslow -crf 26 -an -filter_complex '[1:v]scale=87:40 [und]; [0:v][und]overlay=x=10:y=10' %s.mp4" % (archive_filename, und_watermark_file, watermarked_filename)

    print command

    try:
        # TODO: 'shell=True' is not secure
        subprocess.check_call(command, shell=True)
    except OSError, e:
        print "Error %d: %s" % (e.args[0],e.args[1])
        sys.exit(1)

    # Watermark ogv file
    if location_id == 7:
        command = "ffmpeg -y -i %s -i %s -i %s -vcodec theora -qscale:v 6 -an -filter_complex '[1:v]scale=87:40 [und]; [0:v][und]overlay=x=10:y=10 [und_marked]; [2:v]scale=79:50 [duck]; [und_marked][duck]overlay=x=10;y=(main_h-overlay_h)' %s.ogv" % (archive_filename, und_watermark_file, duck_watermark_file, watermarked_filename)
    else:
        command = "ffmpeg -y -i %s -i %s -vcodec theora -qscale:v 6 -an -filter_complex '[1:v]scale=87:40 [und]; [0:v][und]overlay=x=10:y=10' %s.ogv" % (archive_filename, und_watermark_file, watermarked_filename)

    print command

    try:
        # TODO: 'shell=True' is not secure
        subprocess.check_call(command, shell=True)
    except OSError, e:
        print "Error %d: %s" % (e.args[0],e.args[1])
        sys.exit(1)

    md5_hash = hashlib.md5(open((watermarked_filename + '.mp4'), 'rb').read()).hexdigest()
    filesize = os.path.getsize(watermarked_filename + '.mp4')

    print "MD5 Hash: '%s'" % md5_hash
    print "Filesize: %d" % filesize

    #cursor.execute("UPDATE video_2 SET processing_status = 'WATERMARKED', size = %d, md5_hash = '%s', ogv_generated = true, needs_reconversion = false WHERE id = $d", [filesize, md5_hash, video_id]);

except sql.Error, e:
    print "Error %d: %s" % (e.args[0],e.args[1])
    sys.exit(1)

finally:
    if connection:
        connection.close()

