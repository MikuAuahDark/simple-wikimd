<?php
$wiki = wiki_get_current();
assert($wiki, new Exception("wiki is null"));
?>
<?=$wiki->getTitle();?>

=====

The page "<?=$wiki->getTitle();?>" cannot be found.
