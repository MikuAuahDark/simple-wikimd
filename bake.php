<?php
if (php_sapi_name() !== 'cli')
	throw new Exception('run in CLI');

if (count($argv) < 2)
{
	fwrite(STDERR, "Usage: bake.php <output html dir>\n");
	exit(1);
}

require_once('config.php');
require_once("category.php");
require_once("Wiki.php");

class BakePage
{
	static function urlencode_except_path(string $str)
	{
		return implode('/', array_map('urlencode', explode('/', $str)));
	}

	static function format_wiki_link(string $wiki)
	{
		$newwiki = str_replace(':', '_', $wiki);
		$func = str_starts_with($wiki, 'Category:') ? 'urlencode' : [self::class, 'urlencode_except_path'];
		return $func($newwiki) . '.html';
	}

	static function has_wiki_page(string $wiki)
	{
		if (str_starts_with($wiki, 'Category:'))
			return true;

		return file_exists(CONTENT_DIR . "/$wiki.md");
	}

	/** @return array<string, \Wiki> */
	static function process(string $dir, int $substart)
	{
		$result = [];

		foreach (scandir(CONTENT_DIR, SCANDIR_SORT_NONE) as $filename)
		{
			if ($filename == '.' || $filename == '..' || strtolower(substr($filename, -3)) != '.md')
				continue;

			$path = "$dir/$filename";
			if (is_dir($path))
				$result = array_merge($result, self::process($path, $substart));

			$page_name = substr($path, $substart, -3);
			$wiki = new Wiki($page_name, self::$page_check, self::$link_format);
			assert($wiki->load_from_file($path), new Exception('baking failed'));

			$result[$page_name] = $wiki;
		}

		return $result;
	}

	static function main(string $outdir)
	{
		$outdir = str_replace('\\', '/', $outdir);
		if (!is_dir($outdir))
			mkdir($outdir, recursive: true);

		if ($outdir[-1] == '/')
			$outdir = substr($outdir, 0, -1);

		// Load all wiki pages
		$wiki_pages = self::process(CONTENT_DIR, strlen(CONTENT_DIR) + 1);
		$categories = [];
		$page_categories = [];

		foreach ($wiki_pages as $page_name => $wiki)
		{
			$page_path = $outdir . '/' . self::format_wiki_link($page_name);
			$page_dir = dirname($page_path);

			if (!is_dir($page_dir))
				mkdir($page_dir, recursive: true);

			// Append category
			$cat = category_query($wiki->getMetadata());
			$page_categories[$page_name] = $cat;
			$categories = array_merge($categories, $cat);

			// Render
			ob_start();
			$wiki->render(CONTENT_HTML_PHP);
			$html = ob_get_clean();
			file_put_contents($page_path, $html);
		}

		// Copy the main page to index.html
		if (!is_file("$outdir/index.html"))
			copy($outdir . '/' . self::format_wiki_link(MAIN_PAGE), "$outdir/index.html");

		// Write categories
		$categories = array_unique($categories, SORT_STRING);
		category_set_cached($categories, $page_categories);

		if (count($categories) > 0)
		{
			// Write category page
			foreach ($categories as $category)
			{
				$title = "Category:$category";
				$wiki = new Wiki($title, self::$page_check, self::$link_format);
				category_set_current($category);
				$wiki->load_from_php_name(CATEGORY_MARKDOWN_PHP);

				// Render
				ob_start();
				$wiki->render(CONTENT_HTML_PHP);
				$html = ob_get_clean();
				file_put_contents($outdir . '/' . self::format_wiki_link($title) . '.html', $html);
			}

			// Write list of categories page
			$wiki = new Wiki("Category:Category", self::$page_check, self::$link_format);
			category_set_current($category);
			$wiki->load_from_php_name(CATEGORIES_MARKDOWN_PHP);

			// Render
			ob_start();
			$wiki->render(CONTENT_HTML_PHP);
			$html = ob_get_clean();
			file_put_contents("$outdir/Category_Category.html", $html);
		}
	}

	static $page_check = [self::class, 'has_wiki_page'];
	static $link_format = [self::class, 'format_wiki_link'];
}

BakePage::main($argv[1]);
