<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage('RANDOM_PRODUCT'),
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