<?php
require_once('config.php');
require_once('utils.php');

class CategoryVars
{
	static ?string $current = null;
	/** @var ?array<string> */
	static ?array $categories = null;
	/** @var ?array<string, array<string>> */
	static ?array $page_categories = null;
}

/**
 * @param array<string> $categories
 * @param array<string, array<string>> $page_categories
 */
function category_set_cached(array $categories, array $page_categories)
{
	CategoryVars::$categories = $categories;
	CategoryVars::$page_categories = $page_categories;
}

/**
 * List all valid categories
 * @return array<string>
 **/
function category_list_all(string $dir = CONTENT_DIR)
{
	if (CategoryVars::$categories)
		return CategoryVars::$categories;

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
function category_list_pages(string $category, string $dir = CONTENT_DIR, int $_internal_sub = 0)
{
	if (CategoryVars::$page_categories && isset(CategoryVars::$page_categories[$category]))
		return CategoryVars::$page_categories[$category];

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

function category_query(array $metadata)
{
	if (isset($metadata['category']))
	{
		if (is_string($metadata['category']))
			return [$metadata['category']];
		elseif (is_array($metadata['category']))
			return array_map('strval', $metadata['category']);
	}

	return [];
}
