<?php
require_once('utils.php');

class CategoryVars
{
	static ?string $current = null;
}

/**
 * List all valid categories
 * @return array<string>
 **/
function category_list_all(string $dir)
{
	$result = [];

	foreach (scandir($dir) as $filename)
	{
		if ($filename == '.' || $filename == '..' || strtolower(substr($filename, -3)) != '.md')
			continue;

		$path = "$dir/$filename";

		if (is_dir($path))
			$result = array_merge($result, category_list_all($path));
		elseif (is_file($path))
		{
			$metadata = utils_separate_yaml(file_get_contents($path))[1];

			if (isset($metadata['category']))
			{
				$category = $metadata['category'];

				if (is_string($category))
					$result[] = $category;
				elseif (is_array($category))
					$result = array_merge($result, array_map('strval', $category));
			}
		}
	}

	$unique = array_unique($result);
	sort($unique, SORT_NATURAL);
	return $unique;
}

/**
 * List all pages with specified category.
 * @return array<string>
 **/
function category_list_pages(string $dir, string $category, int $_internal_sub = 0)
{
	$result = [];
	if ($_internal_sub == 0)
		$_internal_sub = strlen($dir) + 1;

	foreach (scandir($dir) as $filename)
	{
		if ($filename == '.' || $filename == '..' || strtolower(substr($filename, -3)) != '.md')
			continue;

		$path = "$dir/$filename";

		if (is_dir($path))
			$result = array_merge($result, category_list_pages($path, $category, $_internal_sub));
		elseif (is_file($path))
		{
			$metadata = utils_separate_yaml(file_get_contents($path))[1];

			if (isset($metadata['category']))
			{
				$category_data = $metadata['category'];

				if (
					(is_string($category) && $category == $category_data) ||
					(is_array($category_data) && in_array($category, $category_data))
				)
					$result[] = substr($path, $_internal_sub, -3);
			}
		}
	}

	sort($result, SORT_NATURAL);
	return $result;
}

function category_set_current(string $category)
{
	CategoryVars::$current = $category;
}

function category_get_current()
{
	assert(CategoryVars::$current !== null, new Exception('no category'));
	return CategoryVars::$current;
}
