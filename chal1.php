<?php

require 'vendor/autoload.php';
use OpenCloud\Rackspace;

/**
* Print script usage message
*/
function printUsage()
{
	print "\nUSAGE:\n" . basename(__FILE__) . " -rRegion -nServerName -fFlavorID -iImageID -kSSHKeyFile -cCredsFile\n\n";
	print "CredsFile defaults to ~/.rackspace_cloud_credentials.\n";
	print "Menus are supplied for Region, FlavorID, and ImageID if not provided.\n\n";
}

/**
* Read command line input in a cross-platform fashion
* (readline not available on Windows)
* @return string
*/
function read()
{
	$fp = fopen("php://stdin", "r");
	$in = fgets($fp, 4094);
	fclose($fp);

	#strip newline
	(PHP_OS == "WINNT") ? ($read = str_replace("\r\n", "", $in)) : 
		($read = str_replace("\n", "", $in));

	return $read;
}

/**
* Return regions for a service
* @param Catalog $catalog
* @param string $serviceName
* @param string $serviceType
* @return array
*/
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

/**
* Choose an option from an enumerated array based on a prompt
* @param array $array
* @param string $prompt
* @return any
*/
function makeChoice($array, $prompt)
{
	foreach($array as $index => $item)
		print "$index:  $item\n";
	do {
		echo $prompt;
		$choice = read();
	} while (!array_key_exists($choice, $array));

	return $array[$choice];
}

function makeChoiceFromCollection($iterator, $prompt)
{
	$index = 0;
	$ids = array();
	foreach ($iterator as $item)
	{
		echo $index . ":  " . $item->name . "\n";
		$ids[$index] = $item->id;
		$index++;
	}
	do {
		echo $prompt;
		$choice = read();
	} while (!array_key_exists($choice, $ids));

	return $ids[$choice];
}

$options = getopt("r:n:f:i:k:c:");

if (!array_key_exists("n", $options))
{
	printUsage();
	exit(0);
}

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

$flavors = $compute->FlavorList(FALSE);

if (!array_key_exists("f", $options))
	$options["f"] = makeChoiceFromCollection($flavors, "Select a flavor: ");

echo "Using flavor ID '" . $options["f"] . "'.\n";

?>