<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;

function printUsage()
{
	print __FILE__ . " -rRegion -nServerName -fFlavorID -iImageID -cCredsFile\n";
	print "CredsFile defaults to ~/.rackspace_cloud_credentials.\n";
	print "Menus are supplied for Region, FlavorID, and ImageID if not provided.";
}

function getRegions($catalog, $serviceName, $serviceType)
{
	$regions = array();
	foreach ($catalog->getItems() as $catalogItem)
		if ($catalogItem->getName() == $serviceName && $catalogItem->getType() == $serviceType)
		{
			foreach ($catalogItem->getEndpoints() as $endpoint)
				$regions[] = $endpoint->region;
			break;
		}
	return $regions;
}

function makeChoice($array, $prompt)
{
	foreach($array as $index => $item)
		print "$index:  $item\n";
	do {
		$choice = readline($prompt);
	} while(!in_array($choice, array_keys($array)));

	return $array[$choice];
}

$options = getopt("r:n:f:i:c:");

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
$client->authenticate();


if (!array_key_exists("r", $options))
{
	$regions = getRegions($client->getCatalog(), 'cloudServersOpenStack', 'compute');
	$options["r"] = makeChoice($regions, "Select a region: ");
}

$compute = $client->computeService('cloudServersOpenStack', $options["r"]);

?>