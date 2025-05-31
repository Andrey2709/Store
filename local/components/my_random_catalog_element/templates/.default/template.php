<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}
?>
<?php foreach ($arResult as $item) { ?>
	<h5><?= 'Название: ' . $item['NAME'] ?></h5>
	<p><?= 'Цена: ' . $item['PRICE'] ?></p>
	<a href="<?= $item['LINK'] ?>">Страница товара</a>
<?php } ?>