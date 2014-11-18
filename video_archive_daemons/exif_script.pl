#!/usr/bin/perl

use Image::ExifTool qw(:Public);
use Data::Dumper;

#create array of image files
opendir(DH, '/share/wildlife/archive/hudson_bay_project/2014/COEI/C71/100RECNX') or die $!;
my @files = grep /\.jpg/i , readdir(DH);
closedir(DH);

my $info;

#for each image check if it is in the database, if it is not, add it to the database
#for now just prints data
foreach $file (@files)
{
	$info = ImageInfo("/share/wildlife/archive/hudson_bay_project/2014/COEI/C71/100RECNX/$file");
	print "\n$file: \n";
	print "Date and time: ", $info->{'CreateDate'}, "\n",
		"Temperature: ", $info->{'AmbientTemperature'}, "\n";
}