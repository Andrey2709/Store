<?php
/**
 * @var array $arCurrentValues
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');

$arCatalogs = Bitrix\Catalog\CatalogIblockTable::getList();
while ($catalogs = $arCatalogs->fetch()) {
	if ($catalogs['PRODUCT_IBLOCK_ID'])
		$productIblockId = $catalogs['PRODUCT_IBLOCK_ID'];
}

$sectionList = [];
$arSections = CIBlockSection::GetList(["SORT" => "ASC"], ['ACTIVE' => 'Y', 'IBLOCK_ID' => $productIblockId]);
while ($section = $arSections->Fetch()) {
	$sectionList[$section['ID']] = '[' . $section['ID'] . ']' . $section['NAME'];
	$arSectionFilter[$section['IBLOCK_SECTION_ID']] = $section['IBLOCK_SECTION_ID'];
}
$resultSectionList = array_diff_key($sectionList, $arSectionFilter);

$arComponentParameters = array(
	"GROUPS" => array(
		"SETTINGS" => array(
			"NAME" => GetMessage('BASIC_SETTINGS')
		),
	),
	"PARAMETERS" => array(
		"SECTION_ID" => array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage('SECTION'),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $resultSectionList,
			"REFRESH" => "Y"
		),
		"NUMBER_OF_PRODUCTS" => array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage('NUMBER_OF_PRODUCTS'),
			"TYPE" => "STRING",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => '1',
			"REFRESH" => "Y"
		),
	),

);