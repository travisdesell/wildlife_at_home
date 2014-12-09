#!/usr/bin/perl
#made by Jaeden Lovin

use strict;
use DBI;

#connect to database
my $driver = "mysql"; 
my $database = "wildlife_video";
my $dsn = "DBI:$driver:database=$database";
my $userid = "wildlife_user";
my $password = "gr0u\$e\$";

my $dbh = DBI->connect($dsn, $userid, $password ) or die "ERROR: Could not connect to database";

#go through rows and update the species
my $sth = $dbh->prepare(q{SELECT * FROM images where species=0});

$sth->execute();

#this will go through every row from the select statment and add the row to a hash called $field_hash
while( my $field_hash = $sth->fetchrow_hashref() )
{
	my $id = $field_hash->{'id'};
	my $path = $field_hash->{'archive_filename'};
	
	#if path has COEI in it update species with species id
	if($path =~ /COEI/)
	{
		my $sth2 = $dbh->prepare("update images set species=1 where id=$id;");
		$sth2->execute();
		$sth2->finish();

	}
	elsif($path =~ /LSGO/)
	{
		my $sth2 = $dbh->prepare("update images set species=2 where id=$id;");
		$sth2->execute();
		$sth2->finish();
	}
	elsif($path =~ /nd_predators/)
	{
		my $sth2 = $dbh->prepare("update images set species=3 where id=$id;");
		$sth2->execute();
		$sth2->finish();
	}
	else
	{
		#do nothing
	}

	
}

#sth->finish(); #didn't need this I think
