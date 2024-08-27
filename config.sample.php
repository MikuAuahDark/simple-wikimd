<?php

// The root path of this config.php
define('ROOT_DIR', realpath(dirname(__FILE__)));

// Where the Markdown contents are located
define('CONTENT_DIR', ROOT_DIR . '/content');

// Path to PHP script that emits Markdown content when enumerating categories.
define('CATEGORIES_MARKDOWN_PHP', ROOT_DIR . '/template/categories.md.php');

// Path to PHP script that emits Markdown content when enumerating page with specific category.
define('CATEGORY_MARKDOWN_PHP', ROOT_DIR . '/template/category.md.php');

// Path to PHP script that emits Markdown content when a page is not found.
define('NOT_FOUND_MARKDOWN_PHP', ROOT_DIR . '/template/not_found.md.php');

// Path to PHP script that emits HTML page.
define('CONTENT_HTML_PHP', ROOT_DIR . '/template/content.html.php');

// The default "Main Page" page.
define('MAIN_PAGE', 'Main Page');
