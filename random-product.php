<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Случайный товар");
?><?$APPLICATION->IncludeComponent(
	"my_random_catalog_element", 
	".default", 
	array(
		"SECTION_ID" => "7",
		"COMPONENT_TEMPLATE" => ".default",
		"NUMBER_OF_PRODUCTS" => "1"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>