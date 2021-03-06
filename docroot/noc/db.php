<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Wikimedia database configuration</title>
	<style>
	body { font: 14px/21px sans-serif; color: #252525; }
	h2 { font-weight: normal; }
	a { text-decoration: none; color: #0645ad; }
	a:hover { text-decoration: underline; }
	code { color: #000; background: #f9f9f9; border: 1px solid #ddd; border-radius: 2px; padding: 1px 4px; }
	main { display: flex; flex-wrap: wrap; }
	nav li { float: left; list-style: none; border: 1px solid #eee; padding: 1px 4px; margin: 0 1em 1em 0; }
	section { flex: 1; min-width: 300px; border: 1px solid #eee; padding: 0 1em; margin: 0 1em 1em 0; }
	main, footer { clear: both; }
	section:target { border-color: orange; }
	section:target h2 { background: #ffe; }
	</style>
</head>
<body>
<?php

// ERRORS
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

// Mock vars from CommonSettings.php for db-eqiad.php
$wgDBname = $wgDBuser = $wgDBpassword = null; $wmfMasterDatacenter = 'eqiad';
define( 'DBO_DEFAULT', 'uniq' );

require_once '../../wmf-config/db-eqiad.php';

class WmfClusters {
	private $clusters;

	function names( ) {
		global $wgLBFactoryConf;
		return array_keys( $wgLBFactoryConf['sectionLoads'] );
	}

	/** Return a list of wikis for a clustername */
	function wikis( $clusterName ) {
		global $wgLBFactoryConf;
		$ret = array();
		foreach ( $wgLBFactoryConf['sectionsByDB'] as $wiki => $cluster ) {
			if ( $cluster == $clusterName ) {
				$ret[] = $wiki;
			}
		}
		return $ret;
	}
	function loads( $clusterName ) {
		global $wgLBFactoryConf;
		return $wgLBFactoryConf['sectionLoads'][$clusterName];
	}
	function databases( $clusterName ) {
		global $wgLBFactoryConf;
		return array_keys( $wgLBFactoryConf['sectionLoads'][$clusterName] );
	}

	function contentFor( $name ) {
		print "<strong>Databases:</strong><br>";
		foreach( $this->databases( $name ) as $db ) {
			print "<code>$db</code> ";
		}
		print '<br><strong>Loads</strong>:<br>';
		foreach( $this->loads( $name ) as $key => $val ) {
			print "$key => $val<br>";
		}
		print '<br><strong>Wikis</strong>:<br>';
		if( $name == 'DEFAULT' ) {
			print 'Any wiki not hosted on the other clusters.<br>';
		} else {
			foreach ( $this->wikis( $name ) as $w ) {
				print "$w<br>";
			}
		}
	}
}

$wmf = new WmfClusters();

// Generate navigation links
print '<nav><ul>';
$tab = 0;
foreach ( $wmf->names() as $name ) {
	$tab++;
	print '<li><a href="#tabs-' . $tab . '">Cluster ' . htmlspecialchars( $name ) . '</a></li>';
}
print '</ul></nav><main>';

// Generate content sections
$tab = 0;
foreach ( $wmf->names() as $name ) {
	$tab++;
	print "<section id=\"tabs-$tab\"><h2>Cluster <strong>" . htmlspecialchars( $name ) . '</strong></h2>';
	print $wmf->contentFor( $name ) . '</section>';
}
print '</main>';
?>
<footer>Automatically generated from <a href="./conf/highlight.php?file=db-eqiad.php">wmf-config/db-eqiad.php</a></footer>
</body>
</html>
