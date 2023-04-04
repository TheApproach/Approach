<?php

namespace Approach\Resource;

use \Approach\path;
use \Approach\Scope;
use \Approach\Render\Node;

function generate()
{
	$sources_php = generateSources();
	file_put_contents(Scope::$Active->GetPath(path::services) . '/sources.php', $sources_php);

	$aspect_php = generateAspects();
	foreach($aspect_php as $aspect => $php)
	{
		file_put_contents(Scope::$Active->GetPath(path::services) . '/' . $aspect . '.php', $php);
	}
}


function generateSources()
{
	$name = Scope::$Active->project . '\\Service';
	$paths = [
 		$name . '\\'					=> Scope::$Active->GetPath(path::services),	// project path
		$name . '\\extension'			=> Scope::$Active->GetPath(path::services) . '/extension',
		$name . '\\community'			=> Scope::$Active->GetPath(path::services) . '/community',
		$name . '\\vendor'				=> Scope::$Active->GetPath(path::services) . '/vendor',
		$name . '\\wild'				=> Scope::$Active->GetPath(path::services) . '/wild',
		'Approach\\Service'				=> Scope::$Active->GetPath(path::installed) . '/approach/Service',
		'Approach\\Service\\extension'	=> Scope::$Active->GetPath(path::installed) . '/approach/Service/extension',
		'Approach\\Service\\community'	=> Scope::$Active->GetPath(path::installed) . '/approach/Service/community',
		'Approach\\Service\\vendor'		=> Scope::$Active->GetPath(path::installed) . '/approach/Service/vendor',
		'Approach\\Service\\wild'		=> Scope::$Active->GetPath(path::installed) . '/approach/Service/wild'
	];

	$sources = [];
	// Check project folder and Approach folder for sources
	foreach ($paths as $project => $path) {
		foreach (glob($path . '*.php') as $f) {
			$classname = basename($f, '.php');
			$interfacing  = class_implements($classname);
			if (in_array('Approach\Service\sourceable', $interfacing)) {
				$sources[] = '\\' . $project . '\\' . $classname;
			}
		}
	}

	$output_file = new Node(
		content: '<?php ' . PHP_EOL . 'namespace Approach\Service;' . PHP_EOL
	);
	$output_file[] = new Node(
		content: 'enum Sources:int' . PHP_EOL . '{' . PHP_EOL,
	);

	for ($i = 0; $i < count($sources); $i++) {
		$output_file[] = new Node(
			content: "\t" . 'case ' . $sources[$i] . "\t" . ' = ' . "\t" . $i . ';' . PHP_EOL,
		);
	}
	$output_file[] = new Node(
		content: '}' . PHP_EOL,
	);
	return $output_file->render();
}

function generateAspects()
{
	$sources =  Scope::$Active->GetPath(path::services) . '/sources.php';
	$name = Scope::$Active->project . '\\Service';

	$name = Scope::$Active->project . '\\Resource';
	$paths = [
		$name . '\\'					=> Scope::$Active->GetPath(path::resource),	// project path
		$name . '\\extension'			=> Scope::$Active->GetPath(path::resource) . '/extension',
		$name . '\\community'			=> Scope::$Active->GetPath(path::resource) . '/community',
		$name . '\\vendor'				=> Scope::$Active->GetPath(path::resource) . '/vendor',
		$name . '\\wild'				=> Scope::$Active->GetPath(path::resource) . '/wild',

		'Approach\\Service'				=> Scope::$Active->GetPath(path::installed) . '/approach/Resource',
		'Approach\\Service\\extension'	=> Scope::$Active->GetPath(path::installed) . '/approach/Resource/extension',
		'Approach\\Service\\community'	=> Scope::$Active->GetPath(path::installed) . '/approach/Resource/community',
		'Approach\\Service\\vendor'		=> Scope::$Active->GetPath(path::installed) . '/approach/Resource/vendor',
		'Approach\\Service\\wild'		=> Scope::$Active->GetPath(path::installed) . '/approach/Resource/wild'
	];

	$sources = [];
	// Check project folder and Approach folder for sources
	foreach ($paths as $project => $path) {
		foreach (glob($path . '*.php') as $f) {
			$classname = basename($f, '.php');
			$interfacing  = class_implements($classname);
			if (in_array('Approach\Service\sourceable', $interfacing)) {
				$sources[] = '\\' . $project . '\\' . $classname;
			}
		}
	}

	$output_file = new Node(
		content: '<?php ' . PHP_EOL . 'namespace Approach\Service;' . PHP_EOL
	);
	$output_file[] = new Node(
		content: 'enum Sources:int' . PHP_EOL . '{' . PHP_EOL,
	);

	for ($i = 0; $i < count($sources); $i++) {
		$output_file[] = new Node(
			content: "\t" . 'case ' . $sources[$i] . "\t" . ' = ' . "\t" . $i . ';' . PHP_EOL,
		);
	}
	$output_file[] = new Node(
		content: '}' . PHP_EOL,
	);
	return [
		$output_file->render()
	];
}
