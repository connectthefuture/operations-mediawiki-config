<?php
### Determine realm and datacenter we are on #############################
# $wmfRealm and $wmfDatacenter are used to vary configuration based on server
# location. Thet should be provisioned by puppet in /etc/wikimedia-site and
# /etc/wikimedia-realm.
#
# The possible values of $wmfRealm and $wmfDatacenter as of October 2012 are:
#  - labs + pmtpa
#  - labs + eqiad
#  - production + pmtpa
#  - production + eqiad
global $wmfDatacenter, $wmfRealm;
$wmfRealm   = 'production';
$wmfDatacenter = 'pmtpa';

# Puppet provision the realm in /etc/wikimedia-realm
if( file_exists( '/etc/wikimedia-realm' ) ) {
	$wmfRealm = trim( file_get_contents( '/etc/wikimedia-realm' ) );
}
if( file_exists( '/etc/wikimedia-site' ) ) {
	$wmfDatacenter = trim( file_get_contents( '/etc/wikimedia-site' ) );
}

# Validate settings
switch( $wmfRealm ) {
case 'labs':
case 'production':
	if ( ! in_array( $wmfDatacenter, array( 'pmtpa', 'eqiad' ) ) ) {
		$wmfDatacenter = 'pmtpa';
	}
	break;

default:
	# Assume something vaguely resembling a default
	$wmfRealm   = 'production';
	$wmfDatacenter = 'pmtpa';
	break;
}

# Function to get the filename for the current realm/datacenter, falling back
# to the "base" file if not found.
#
# Files checked are:
#   base-realm-datacenter.ext
#   base-realm.ext
#   base.ext
#
# @param $filename string Base filename, must contain an extension
# @returns string Filename to be used
function getRealmSpecificFilename( $filename ) {
	global $wmfRealm, $wmfDatacenter;

	$dotPos = strrpos( $filename, '.' );
	if ( $dotPos === false ) {
		return $filename;
	}
	$base = substr( $filename, 0, $dotPos );
	$ext = substr( $filename, $dotPos );

	# Test existence of the following file suffix and return
	# immediately whenever found:
	#  - {realm}-{datacenter}
	#  - {realm}
	#  - {datacenter}
	#  - {}
	#
	# Please update /README whenever changing code below.

	$new_filename = "{$base}-{$wmfRealm}-{$wmfDatacenter}{$ext}";
	if ( file_exists( $new_filename ) ) {
		return $new_filename;
	}

	# realm take precedence over datacenter.
	$new_filename = "{$base}-{$wmfRealm}{$ext}";
	if ( file_exists( $new_filename ) ) {
		return $new_filename;
	}

	$new_filename = "{$base}-{$wmfDatacenter}{$ext}";
	if ( file_exists( $new_filename ) ) {
		return $new_filename;
	}

	return $filename;
}
### End /Determine realm and datacenter we are on/ ########################
