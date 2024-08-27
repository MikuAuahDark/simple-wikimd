<?php
require_once('config.php');
require_once("category.php");
require_once("Wiki.php");

class IndexPage
{
	static function format_wiki_link(string $wiki)
	{
		return 'index.php?w=' . urlencode($wiki);
	}

	static function has_wiki_page(string $wiki)
	{
		if (str_starts_with($wiki, 'Category:'))
			return true;

		return file_exists(CONTENT_DIR . "/$wiki.md");
	}

	static function main()
	{
		$page = $_GET['w'] ?? MAIN_PAGE;
		$wiki = new Wiki($page, [self::class, 'has_wiki_page'], [self::class, 'format_wiki_link']);

		if (str_starts_with($page, 'Category:'))
		{
			$category_name = substr($page, 9);

			if (strcmp($category_name, 'Category') == 0)
				$wiki->load_from_php_name(CATEGORIES_MARKDOWN_PHP);
			else
			{
				category_set_current($category_name);
				$wiki->load_from_php_name(CATEGORY_MARKDOWN_PHP);
			}
		}
		else if (!$wiki->load_from_file(CONTENT_DIR . "/$page.md"))
		{
			http_response_code(404);
			$wiki->load_from_php_name(NOT_FOUND_MARKDOWN_PHP);
		}

		$wiki->render(CONTENT_HTML_PHP);
	}
}

IndexPage::main();
