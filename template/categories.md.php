<?php
$categories = category_list_all();
?>
Category List
=====

There are <?=count($categories);?> categorie(s) in this wiki:

<?php
foreach ($categories as $category):
?>
* [[Category:<?=$category;?>]]
<?php
endforeach;
?>
