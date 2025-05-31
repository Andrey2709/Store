<?php
use Bitrix\Main\Localization\Loc;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
Loc::loadMessages(__FILE__);
$arComponentDescription = array(
	"NAME" => Loc::getMessage('RANDOM_PRODUCT'),
	"DESCRIPTION" => "Случайный товар из торгового католога",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => 'Каталог',
			"SORT" => 30
		),
	),
	"CACHE_PATH" => "Y",
);