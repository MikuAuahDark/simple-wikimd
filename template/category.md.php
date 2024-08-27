<?php
$current = category_get_current();
$pages = category_list_pages(CONTENT_DIR, $current);
?>
Category of <?=$current;?>

=====

There are <?=count($pages);?> page(s) in this category:

<?php
foreach ($pages as $page):
?>
* [[<?=$page;?>]]
<?php
endforeach;
?>
