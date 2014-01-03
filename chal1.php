<?php

require 'vendor/autoload.php';

use OpenCloud\Rackspace;

if ($_SERVER['OS'] == 'Windows_NT')
	$homedir = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . '\\';
else
	$homedir = $_SERVER['HOME'] . '/';

print "Using creds file in $homedir...\n";

$creds = parse_ini_file($homedir . '.rackspace_cloud_credentials');

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
	'username' => $creds['username'],
	'apiKey' => $creds['api_key']
));

$compute = $client->computeService('cloudServersOpenStack', 'IAD');



?>