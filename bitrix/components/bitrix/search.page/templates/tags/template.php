<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$arCloudParams = [
	'SEARCH' => $arResult['REQUEST']['~QUERY'],
	'TAGS' => $arResult['REQUEST']['~TAGS'],
	'CHECK_DATES' => $arParams['CHECK_DATES'],
	'arrFILTER' => $arParams['arrFILTER'],
	'SORT' => $arParams['TAGS_SORT'],
	'PAGE_ELEMENTS' => $arParams['TAGS_PAGE_ELEMENTS'],
	'PERIOD' => $arParams['TAGS_PERIOD'],
	'URL_SEARCH' => $arParams['TAGS_URL_SEARCH'],
	'TAGS_INHERIT' => $arParams['TAGS_INHERIT'],
	'FONT_MAX' => $arParams['FONT_MAX'],
	'FONT_MIN' => $arParams['FONT_MIN'],
	'COLOR_NEW' => $arParams['COLOR_NEW'],
	'COLOR_OLD' => $arParams['COLOR_OLD'],
	'PERIOD_NEW_TAGS' => $arParams['PERIOD_NEW_TAGS'],
	'SHOW_CHAIN' => $arParams['SHOW_CHAIN'],
	'COLOR_TYPE' => $arParams['COLOR_TYPE'],
	'WIDTH' => $arParams['WIDTH'],
	'CACHE_TIME' => $arParams['CACHE_TIME'],
	'CACHE_TYPE' => $arParams['CACHE_TYPE'],
	'RESTART' => $arParams['RESTART'],
];

if (is_array($arCloudParams['arrFILTER']))
{
	foreach ($arCloudParams['arrFILTER'] as $strFILTER)
	{
		if ($strFILTER == 'main')
		{
			$arCloudParams['arrFILTER_main'] = $arParams['arrFILTER_main'];
		}
		elseif ($strFILTER == 'forum' && IsModuleInstalled('forum'))
		{
			$arCloudParams['arrFILTER_forum'] = $arParams['arrFILTER_forum'];
		}
		elseif (mb_strpos($strFILTER, 'iblock_') === 0)
		{
			if (isset($arParams['arrFILTER_' . $strFILTER]) && is_array($arParams['arrFILTER_' . $strFILTER]))
			{
				foreach ($arParams['arrFILTER_' . $strFILTER] as $strIBlock)
				{
					$arCloudParams['arrFILTER_' . $strFILTER] = $arParams['arrFILTER_' . $strFILTER];
				}
			}
		}
		elseif ($strFILTER == 'blog')
		{
			$arCloudParams['arrFILTER_blog'] = $arParams['arrFILTER_blog'];
		}
		elseif ($strFILTER == 'socialnetwork')
		{
			$arCloudParams['arrFILTER_socialnetwork'] = $arParams['arrFILTER_socialnetwork'];
		}
	}
}

$APPLICATION->IncludeComponent('bitrix:search.tags.cloud', '.default', $arCloudParams, $component);

?><br /><div class="search-page">
<form action="" method="get">
	<input type="hidden" name="tags" value="<?php echo $arResult['REQUEST']['TAGS']?>" />
<?php if (isset($arParams['USE_SUGGEST']) && $arParams['USE_SUGGEST'] === 'Y'):
	if (mb_strlen($arResult['REQUEST']['~QUERY']) && is_object($arResult['NAV_RESULT']))
	{
		$arResult['FILTER_MD5'] = $arResult['NAV_RESULT']->GetFilterMD5();
		$obSearchSuggest = new CSearchSuggest($arResult['FILTER_MD5'], $arResult['REQUEST']['~QUERY']);
		$obSearchSuggest->SetResultCount($arResult['NAV_RESULT']->NavRecordCount);
	}
	?>
	<?php $APPLICATION->IncludeComponent(
		'bitrix:search.suggest.input',
		'',
		[
			'NAME' => 'q',
			'VALUE' => $arResult['REQUEST']['~QUERY'],
			'INPUT_SIZE' => 40,
			'DROPDOWN_SIZE' => 10,
			'FILTER_MD5' => $arResult['FILTER_MD5'],
		],
		$component, ['HIDE_ICONS' => 'Y']
	);?>
<?php else:?>
	<input type="text" name="q" value="<?=$arResult['REQUEST']['QUERY']?>" size="40" />
<?php endif;?>
<?php if ($arParams['SHOW_WHERE']):?>
	&nbsp;<select name="where">
	<option value=""><?=GetMessage('SEARCH_ALL')?></option>
	<?php foreach ($arResult['DROPDOWN'] as $key => $value):?>
	<option value="<?=$key?>"<?php echo ($arResult['REQUEST']['WHERE'] == $key) ? ' selected' : '';?>><?=$value?></option>
	<?php endforeach?>
	</select>
<?php endif;?>
	&nbsp;<input type="submit" value="<?=GetMessage('SEARCH_GO')?>" />
	<input type="hidden" name="how" value="<?php echo $arResult['REQUEST']['HOW'] == 'd' ? 'd' : 'r'?>" />
<?php if ($arParams['SHOW_WHEN']):?>
	<script>
	var switch_search_params = function()
	{
		var sp = document.getElementById('search_params');
		var flag;
		var i;

		if(sp.style.display == 'none')
		{
			flag = false;
			sp.style.display = 'block'
		}
		else
		{
			flag = true;
			sp.style.display = 'none';
		}

		var from = document.getElementsByName('from');
		for(i = 0; i < from.length; i++)
			if(from[i].type.toLowerCase() == 'text')
				from[i].disabled = flag;

		var to = document.getElementsByName('to');
		for(i = 0; i < to.length; i++)
			if(to[i].type.toLowerCase() == 'text')
				to[i].disabled = flag;

		return false;
	}
	</script>
	<br /><a class="search-page-params" href="#" onclick="return switch_search_params()"><?php echo GetMessage('CT_BSP_ADDITIONAL_PARAMS')?></a>
	<div id="search_params" class="search-page-params" style="display:<?php echo $arResult['REQUEST']['FROM'] || $arResult['REQUEST']['TO'] ? 'block' : 'none'?>">
		<?php $APPLICATION->IncludeComponent(
			'bitrix:main.calendar',
			'',
			[
				'SHOW_INPUT' => 'Y',
				'INPUT_NAME' => 'from',
				'INPUT_VALUE' => $arResult['REQUEST']['~FROM'],
				'INPUT_NAME_FINISH' => 'to',
				'INPUT_VALUE_FINISH' => $arResult['REQUEST']['~TO'],
				'INPUT_ADDITIONAL_ATTR' => 'size="10"',
			],
			null,
			['HIDE_ICONS' => 'Y']
		);?>
	</div>
<?php endif?>
</form><br />

<?php if (isset($arResult['REQUEST']['ORIGINAL_QUERY'])):
	?>
	<div class="search-language-guess">
		<?php echo GetMessage('CT_BSP_KEYBOARD_WARNING', ['#query#' => '<a href="' . $arResult['ORIGINAL_QUERY_URL'] . '">' . $arResult['REQUEST']['ORIGINAL_QUERY'] . '</a>'])?>
	</div><br /><?php
endif;?>

<?php if ($arResult['REQUEST']['QUERY'] === false && $arResult['REQUEST']['TAGS'] === false):?>
<?php elseif ($arResult['ERROR_CODE'] != 0):?>
	<p><?=GetMessage('SEARCH_ERROR')?></p>
	<?php ShowError($arResult['ERROR_TEXT']);?>
	<p><?=GetMessage('SEARCH_CORRECT_AND_CONTINUE')?></p>
	<br /><br />
	<p><?=GetMessage('SEARCH_SINTAX')?><br /><b><?=GetMessage('SEARCH_LOGIC')?></b></p>
	<table border="0" cellpadding="5">
		<tr>
			<td align="center" valign="top"><?=GetMessage('SEARCH_OPERATOR')?></td><td valign="top"><?=GetMessage('SEARCH_SYNONIM')?></td>
			<td><?=GetMessage('SEARCH_DESCRIPTION')?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage('SEARCH_AND')?></td><td valign="top">and, &amp;, +</td>
			<td><?=GetMessage('SEARCH_AND_ALT')?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage('SEARCH_OR')?></td><td valign="top">or, |</td>
			<td><?=GetMessage('SEARCH_OR_ALT')?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage('SEARCH_NOT')?></td><td valign="top">not, ~</td>
			<td><?=GetMessage('SEARCH_NOT_ALT')?></td>
		</tr>
		<tr>
			<td align="center" valign="top">( )</td>
			<td valign="top">&nbsp;</td>
			<td><?=GetMessage('SEARCH_BRACKETS_ALT')?></td>
		</tr>
	</table>
<?php elseif (count($arResult['SEARCH']) > 0):?>
	<?php echo (!isset($arParams['DISPLAY_TOP_PAGER']) || $arParams['DISPLAY_TOP_PAGER'] != 'N') ? $arResult['NAV_STRING'] : '';?>
	<br /><hr />
	<?php foreach ($arResult['SEARCH'] as $arItem):?>
		<a href="<?php echo $arItem['URL']?>"><?php echo $arItem['TITLE_FORMATED']?></a>
		<p><?php echo $arItem['BODY_FORMATED']?></p>
		<?php if (
			$arParams['SHOW_RATING'] == 'Y'
			&& !empty($arItem['RATING_TYPE_ID'])
			&& $arItem['RATING_ENTITY_ID'] > 0
		):?>
			<div class="search-item-rate"><?php
				$APPLICATION->IncludeComponent(
					'bitrix:rating.vote', $arParams['RATING_TYPE'],
					[
						'ENTITY_TYPE_ID' => $arItem['RATING_TYPE_ID'],
						'ENTITY_ID' => $arItem['RATING_ENTITY_ID'],
						'OWNER_ID' => $arItem['USER_ID'],
						'USER_VOTE' => $arItem['RATING_USER_VOTE_VALUE'],
						'USER_HAS_VOTED' => $arItem['RATING_USER_VOTE_VALUE'] == 0 ? 'N' : 'Y',
						'TOTAL_VOTES' => $arItem['RATING_TOTAL_VOTES'],
						'TOTAL_POSITIVE_VOTES' => $arItem['RATING_TOTAL_POSITIVE_VOTES'],
						'TOTAL_NEGATIVE_VOTES' => $arItem['RATING_TOTAL_NEGATIVE_VOTES'],
						'TOTAL_VALUE' => $arItem['RATING_TOTAL_VALUE'],
						'PATH_TO_USER_PROFILE' => $arParams['~PATH_TO_USER_PROFILE'],
					],
					$component,
					['HIDE_ICONS' => 'Y']
				);?>
			</div>
		<?php endif;?>
		<small><?=GetMessage('SEARCH_MODIFIED')?> <?=$arItem['DATE_CHANGE']?></small><br /><?php
		if (!empty($arItem['TAGS']))
		{
			?><small><?php
			$first = true;
			foreach ($arItem['TAGS'] as $tags):
				if (!$first)
				{
					?>, <?php
				}
				?><a href="<?=$tags['URL']?>"><?=$tags['TAG_NAME']?></a> <?php
				$first = false;
			endforeach;
			?></small><br /><?php
		}
		if ($arItem['CHAIN_PATH']):?>
			<small><?=GetMessage('SEARCH_PATH')?>&nbsp;<?=$arItem['CHAIN_PATH']?></small><?php
		endif;
		?><hr />
	<?php endforeach;?>
	<?php echo (!isset($arParams['DISPLAY_BOTTOM_PAGER']) || $arParams['DISPLAY_BOTTOM_PAGER'] != 'N') ? $arResult['NAV_STRING'] : '';?>
	<br />
	<p>
	<?php if ($arResult['REQUEST']['HOW'] == 'd'):?>
		<a href="<?=$arResult['URL']?>&amp;how=r"><?=GetMessage('SEARCH_SORT_BY_RANK')?></a>&nbsp;|&nbsp;<b><?=GetMessage('SEARCH_SORTED_BY_DATE')?></b>
	<?php else:?>
		<b><?=GetMessage('SEARCH_SORTED_BY_RANK')?></b>&nbsp;|&nbsp;<a href="<?=$arResult['URL']?>&amp;how=d"><?=GetMessage('SEARCH_SORT_BY_DATE')?></a>
	<?php endif;?>
	</p>
<?php else:?>
	<?php ShowNote(GetMessage('SEARCH_NOTHING_TO_FOUND'));?>
<?php endif;?>
</div>
