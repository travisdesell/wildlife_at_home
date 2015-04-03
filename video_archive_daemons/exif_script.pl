#!/usr/bin/perl
#made by Jaeden Lovin

use strict;
#use Image::ExifTool qw(:Public);
use Data::Dumper;
use File::Find;
use DBI;
use Cwd 'abs_path'; 

require "wildlife_db.pl";


#TODO add command line argument for path specified

#directory to start with
my $dir = "/share/wildlife/archive/nd_predators";

#this recursively goes through a directory and will do the function on every file it finds
find(\&get_data, $dir);

sub get_data
{
	my $file = $_;

	my ($camera_id, $species, $year);
	

	#if the file is a jpeg do stuff
	#TODO check for corrupt images
	if($file =~ /\w*\.JPG/)
	{
		#get info from image
		my $info = ImageInfo($file);

		#get temp as an integer
		my $temp = ($info->{'AmbientTemperature'});
		$temp =~ s/\D+//g;

		my $abs_path = abs_path($file);

		#if it is from the hudson bay directory do this stuff
		if($abs_path =~ /hudson_bay_project/)
		{

			#get year from path
			$abs_path =~ /hudson_bay_project\/(\d\d\d\d)\//;
			$year = $1;

			#get species from path
			$abs_path =~ /hudson_bay_project\/\d\d\d\d\/(\w+)\//;
			my $species_name = $1;
			$species;

			#set species based on which directory file was in		
			if($species_name eq "COEI")
			{
				$species = 1;
			}
			elsif($species_name eq "LSGO")
			{
				$species = 2;
			}
			elsif($species_name eq "LSGO")
			{
				$species = 2;
			}
			else
			{
				$species = 0;
			}

			#get camera id from path
			$abs_path =~ /hudson_bay_project\/\d\d\d\d\/\w+\/(\w+-?\w*)\//;
			$camera_id = $1;
		}
		#if it is from the nd predators directory do this stuff
		elsif($abs_path =~ /nd_predators/)
		{
			#get year
			$abs_path =~ /(\d\d\d\d)/;
			$year = $1;

			#get species (PRED)
			$species = 3;

			#get camera id
			$abs_path =~ /Period_\d\/((\w*\d*)*)\//;
			$camera_id = $1;
			
			
		}

		
		#connect to database
		my $driver = "mysql"; 
		my $database = "wildlife_video";
		my $dsn = "DBI:$driver:database=$database";
		my $userid = get_user();
		my $password = get_password();

		my $dbh = DBI->connect($dsn, $userid, $password ) or die "ERROR: Could not connect to database";

		#add data to database
		my $sth = $dbh->prepare("INSERT INTO images
                       (temp, datetime, archive_filename, camera_id, species, year)
                        values
                       ('$temp', '$info->{'DateTimeOriginal'}', '$abs_path', '$camera_id', '$species', '$year')");
		$sth->execute();    # or print $DBI::errstr;
		$sth->finish();
		#$dbh->commit or die $DBI::errstr; #this was not working

		#this just prints some data to test
		#print "\n$file: \n";
		#print "Date and time: ", $info->{'CreateDate'}, "\n",
		#	"Temperature: ", $temp, "\n",
		#	"Path: ", $abs_path, "\n";

	}
}
