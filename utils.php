<?php
require_once("Spyc.php");

// https://stackoverflow.com/a/2955878
function utils_slugify(string $text, string $divider = '-')
{
	// replace non letter or digits by divider
	$text = preg_replace('~[^\pL\d]+~u', $divider, $text);

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	// trim
	$text = trim($text, $divider);

	// remove duplicate divider
	$text = preg_replace('~-+~', $divider, $text);

	// lowercase
	$text = strtolower($text);

	if (empty($text))
		return '-';

	return $text;
}

/**
 * Separates Markdown and YAML metadata.
 * @return array{0:string,1:array}
 **/
function utils_separate_yaml(string $content)
{
	$metadata = [];

	if (strcmp(substr($content, 0, 4), "---\n") == 0)
	{
		// Has metadata
		$end = strpos($content, "---\n", 4);
		assert($end !== false, new Exception("cannot find YAML end marker"));

		$end += 4;
		$yaml = substr($content, 0, $end);
		$metadata = spyc_load($yaml);
		$content = substr($content, $end);
	}

	return [$content, $metadata];
}
