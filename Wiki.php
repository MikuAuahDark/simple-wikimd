<?php
require_once("ExtendedMarkdown.php");
require_once("utils.php");

class WikiTOC
{
	public string $name;
	public string $slug_id;
	/** @var array<int> */
	public array $levels;
}

class WikiLinkResult
{
	public bool $found;
	public string $url;
}

class Wiki
{
	public function __construct(string $title, callable $wikiCheck, callable $wikiLinkResolver)
	{
		$this->wikiLinkResolver = $wikiLinkResolver;
		$this->wikiCheck = $wikiCheck;
		$this->title = $title;
		$this->clear();
	}

	public function clear()
	{
		$this->htmlcontent = '';
		$this->toc = [];
		$this->metadata = [];
	}

	public function load_from_file(string $path)
	{
		Wiki::$current = $this;
		$f = @fopen($path, "rb");
	
		if (!$f)
			return false;
	
		$content = [];
		while (!@feof($f))
			$content[] = @fread($f, 8192);

		$this->load_from_string(implode($content));
		Wiki::$current = null;
		return true;
	}

	public function load_from_php_name(string $path)
	{
		Wiki::$current = $this;
		ob_start();
		require($path);
		$this->load_from_string(ob_get_clean());
		Wiki::$current = null;
	}

	public function load_from_string(string $content)
	{
		[$mdcontent, $this->metadata] = utils_separate_yaml(str_replace("\r\n", "\n", $content));

		$resolver = $this->wikiLinkResolver;
		$checker = $this->wikiCheck;
		$parsedown = new ExtendedMarkdown(function(string $wiki) use($resolver, $checker): WikiLinkResult {
			$result = new WikiLinkResult;
			$result->found = $checker($wiki);
			$result->url = $resolver($wiki);
			return $result;
		});
		$this->htmlcontent = $parsedown->text($mdcontent);

		// Populate ToC
		foreach ($parsedown->toc as $toc_data)
		{
			$wtoc = new WikiTOC;
			$wtoc->name = $toc_data[0];
			$wtoc->slug_id = $toc_data[1];
			$wtoc->levels = $this->computeTOCLevel($toc_data[2]);
			$this->toc[] = $wtoc;
		}
	}

	public function render(string $phpfile)
	{
		Wiki::$current = $this;
		require($phpfile);
		Wiki::$current = null;
	}

	public function getContent()
	{
		return $this->htmlcontent;
	}

	public function getMetadata()
	{
		return $this->metadata;
	}

	public function getTitle()
	{
		return $this->title;
	}

	private function computeTOCLevel(int $level)
	{
		$l = array_fill(0, $level, 1);
		$toc_count = count($this->toc);

		if ($toc_count > 0)
		{
			$last_level = $this->toc[$toc_count - 1]->levels;
			$last_level_count = count($last_level);

			for ($i = 0; $i < min($level, $last_level_count); $i++)
				$l[$i] = $last_level[$i];

			if ($last_level_count >= $level)
				$l[$level - 1]++;
		}

		return $l;
	}

	private string $title;
	private string $htmlcontent;
	/** @var array<int, WikiTOC> */
	private array $toc;
	private array $metadata;
	/** @var callable(string):WikiLinkResult */
	private $wikiLinkResolver;
	/** @var callable(string):bool */
	private $wikiCheck;

	public static Wiki|null $current = null;
}

function wiki_get_current()
{
	return Wiki::$current;
}
