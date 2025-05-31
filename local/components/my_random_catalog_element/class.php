<?php

use \Bitrix\Main\Loader;

class RandomCatalogElement extends CBitrixComponent
{
	private const IBLOCK_ID = 2;

	public function executeComponent()
	{
		Loader::includeModule('iblock');
		Loader::includeModule('catalog');

		if ($this->startResultCache()) {
			$products = $this->getElements();
			$this->addElementsPrices($products);
			$section = $this->getInfoSection($this->arParams['SECTION_ID']);
			$this->addLinkElement($products, $section['INFO']['CODE']);
			$this->arResult = $products;
			$this->includeComponentTemplate();
		}
		return $this->arResult;
	}


	protected function getElements(): array
	{

		$element = [];
		$arFilter = [
			"IBLOCK_ID" => $this->IBLOCK_ID,
			"IBLOCK_SECTION_ID" => $this->arParams['SECTION_ID']
		];
		$arSelect = [
			"ID",
			'NAME',
			'CODE',
		];

		$res = CIBlockElement::GetList(["RAND" => "ASC"], $arFilter, false, ['nTopCount' => $this->arParams['NUMBER_OF_PRODUCTS']], $arSelect);
		while ($ob = $res->Fetch()) {
			$elements[$ob['ID']] = $ob;
		}


		return $elements;
	}

	protected function addElementsPrices(&$products)
	{
		$productIds = array_column($products, 'ID');
		$rsPrices = CPrice::GetList([], ['PRODUCT_ID' => $productIds]);
		while ($price = $rsPrices->Fetch()) {
			$products[$price['PRODUCT_ID']]['PRICE'] = $price['PRICE'];
		}

	}

	protected function getInfoSection($sectionId): array
	{
		$section = [];
		$arSections = CIBlockSection::GetList(["SORT" => "ASC"], ['ACTIVE' => 'Y', 'ID' => $sectionId])->Fetch();
		$section['INFO'] = $arSections;
		return $section;
	}

	protected function addLinkElement(&$products, $sectionCode)
	{
		$protocol = CMain::IsHTTPS() ? "https://" : "http://";
		$basic = $protocol . SITE_SERVER_NAME . '/catalog/' . $sectionCode . '/';

		foreach ($products as $product) {
			$products[$product['ID']]['LINK'] = $basic . $product['CODE'] . '/';
		}
	}

}