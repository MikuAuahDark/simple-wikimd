<?php
require_once("Parsedown.php");
require_once("utils.php");

class ExtendedMarkdown extends \Parsedown
{
	/** @var callable(string):WikiLinkResult */
	private $wikiLinkResolver;
	private $used_slug;
	public array $toc;

	function __construct(callable $wikiLinkResolver)
	{
		$this->wikiLinkResolver = $wikiLinkResolver;
		$this->toc = [];
		$this->used_slug = [];
	}

	protected function inlineLink($data)
	{
		// Test for [[foo]] syntax
		if (preg_match('/\[\[([^\]\]]+)\]\]/', $data['text'], $matches))
		{
			$title = $matches[1];
			$target = $matches[1];

			// Test for [[foo|bar]] syntax
			if (preg_match('/([^\|]+)\|(.+)/', $matches[1], $advanced_matches))
			{
				$title = $advanced_matches[2];
				$target = $advanced_matches[1];
			}

			$resolver = $this->wikiLinkResolver;
			$resolved = $resolver($target);

			$attr = ["href" => $resolved->url];
			if (!$resolved->found)
				$attr["does-not-exist"] = "";

			return [
				"extent" => strlen($matches[0]),
				"element" => [
					"name" => "a",
					"text" => $title,
					"attributes" => $attr,
				]
			];
		}
		else
			return parent::inlineLink($data);
	}

	protected function blockSetextHeader($Line, ?array $Block = null)
	{
		$Block = parent::blockSetextHeader($Line, $Block);

		if ($Block)
			$this->setupTOC($Block);
		
		return $Block;
	}

	protected function blockHeader($Line)
	{
		$Block = parent::blockHeader($Line);
		
		if ($Block)
			$this->setupTOC($Block);

		return $Block;
	}

	private function getSlug(string $Text)
	{
		$original_slug = utils_slugify($Text);
		$target_slug = $original_slug;
		$i = 1;

		while (array_key_exists($target_slug, $this->used_slug))
		{
			$i++;
			$target_slug = "$original_slug-$i";
		}

		$this->used_slug[$target_slug] = true;
		return $target_slug;
	}

	private static function setBlockAttributeID(array &$Block, string $id)
	{
		if (!isset($Block['element']['attributes']))
			$Block['element']['attributes'] = [];

		$Block['element']['attributes']['id'] = $id;
		fwrite(STDERR, 'setid ' . $Block['element']['attributes']['id'] . "\n");
	}

	private function setupTOC(&$Block)
	{
		fwrite(STDERR, "setup TOC\n");
		var_dump($Block);
		$raw = $Block['element']['handler']['argument'];
		$slug = $this->getSlug($raw);
		self::setBlockAttributeID($Block, $slug);
		$toc[] = [$raw, $slug, intval(substr($Block['element']['name'], 1))];
	}
}
