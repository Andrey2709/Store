<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Новости с фильтром");

$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
    'FILTER_ID' => 'LIVEFEED',
    'FILTER' => [
        ['id' => 'NAME', 'name' => 'Название', 'type' => 'string'],
        ['id' => 'DATE', 'name' => 'Дата', 'type' => 'date',
            'exclude' => [
                \Bitrix\Main\UI\Filter\DateType::NONE,
                \Bitrix\Main\UI\Filter\DateType::YESTERDAY,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
                \Bitrix\Main\UI\Filter\DateType::TOMORROW,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
                \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
                \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
                \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
                \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
                \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
                \Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
                \Bitrix\Main\UI\Filter\DateType::MONTH,
                \Bitrix\Main\UI\Filter\DateType::QUARTER,
                \Bitrix\Main\UI\Filter\DateType::YEAR,
                \Bitrix\Main\UI\Filter\DateType::EXACT,
                \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
                \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
                \Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
                \Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
            ]
        ],
    ],
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_LABEL' => true
]);

$filterOption = new Bitrix\Main\UI\Filter\Options('LIVEFEED');
$filterData = $filterOption->getFilter([]);

if ($filterData['NAME']) {
    $arFilter ['NAME'] = $filterData['NAME'];
}

if ($filterData['DATE_from']) {
    $arFilter ['>=DATE_ACTIVE_FROM'] = $filterData['DATE_from'];
}

if ($filterData['DATE_to']) {
    $arFilter ['<=DATE_ACTIVE_TO'] = $filterData['DATE_to'];
}
?>
<br>
<br>
<br>
<br>
<?
$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	".default", 
	array(
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_NAME" => "arFilter",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => "1",
		"IBLOCK_TYPE" => "news",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"INCLUDE_SUBSECTIONS" => "Y",
		"MESSAGE_404" => "",
		"NEWS_COUNT" => "5",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Новости",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"PREVIEW_TRUNCATE_LEN" => "",
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"SET_BROWSER_TITLE" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "Y",
		"SET_META_KEYWORDS" => "Y",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "Y",
		"SHOW_404" => "N",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "DESC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
); ?>
<script>
    BX.bindDelegate(document.body, 'click', {className: 'main-ui-filter-find'}, function (e) {
        BX.PreventDefault(e);
        setTimeout(() => {
            window.location.reload();
        }, 500);

    });

    BX.bindDelegate(document.body, 'click', {className: 'main-ui-filter-reset'}, function (e) {
        BX.PreventDefault(e);
        setTimeout(() => {
            window.location.reload();
        }, 500);

    })
</script>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>

