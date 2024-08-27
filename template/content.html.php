<?php
$wiki = wiki_get_current();
assert($wiki, new Exception("wiki is null"));
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?=htmlspecialchars($wiki->getTitle(), ENT_HTML5);?></title>
	</head>
	<body class="markdown_content">
<?=$wiki->getContent();?>

	</body>
</html>
