<?
#############################################
# Bitrix Site Manager Forum					#
# Copyright (c) 2002-2009 Bitrix			#
# https://www.bitrixsoft.com					#
# mailto:admin@bitrixsoft.com				#
#############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$sTableID = "tbl_vote_votes";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/prolog.php");
$VOTE_RIGHT = $APPLICATION->GetGroupRight("vote");
if($VOTE_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");

IncludeModuleLangFile(__FILE__);
$err_mess = "File: ".__FILE__."<br>Line: ";
if ($_GET["find_vote_id"] > 0 && $_GET["export"] == "xls")
{
	CModule::IncludeModule("vote");
	\Bitrix\Vote\Vote::loadFromId($_GET["find_vote_id"])->exportExcel();
}
/********************************************************************
				Functions
********************************************************************/
function CheckFilter()
{
	global $arFilterFields,$lAdmin;
	foreach ($arFilterFields as $s) global $$s;
	$str = "";

	$find_date_1 = trim($find_date_1);
	$find_date_2 = trim($find_date_2);

	if ($find_date_1 <> '' || $find_date_2 <> '')
	{
		$date_1_stm = MkDateTime(ConvertDateTime($find_date_1,"D.M.Y"),"d.m.Y");
		$date_2_stm = MkDateTime(ConvertDateTime($find_date_2,"D.M.Y")." 23:59:59","d.m.Y H:i:s");
		if (!$date_1_stm && trim($find_date_1) <> '')
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_DATE_FROM"));
		}

		if (!$date_2_stm && trim($find_date_2) <> '')
		{
			$bGotErr = true;
			$lAdmin->AddUpdateError(GetMessage("VOTE_WRONG_DATE_TILL"));
		}

		if (!$bGotErr && $date_2_stm <= $date_1_stm && $date_2_stm <> '')
		{
			$bGotErr = true;
			$lAdmin->AddUpdateErro(GetMessage("VOTE_WRONG_FROM_TILL"));
		}
	}

	if ($bGotErr) return false; else return true;
}

/***************************************************************************
				Actions
****************************************************************************/
$arFilterFields = Array(
	"find_id",
	"find_id_exact_match",
	"find_valid",
	"find_date_1",
	"find_date_2",
	"find_vote_id",
	"find_vote",
	"find_vote_exact_match",
	"find_vote_user",
	"find_vote_user_exact_match",
	"find_session",
	"find_session_exact_match",
	"find_ip",
	"find_ip_exact_match"
	);
InitBVar($find_id_exact_match);
InitBVar($find_vote_exact_match);
InitBVar($find_vote_user_exact_match);
InitBVar($find_session_exact_match);
InitBVar($find_ip_exact_match);
$lAdmin->InitFilter($arFilterFields);
if (CheckFilter())
{
	$arFilter = Array(
		"ID"						=> $find_id,
		"ID_EXACT_MATCH"			=> $find_id_exact_match,
		"VALID"						=> $find_valid,
		"DATE_1"					=> $find_date_1,
		"DATE_2"					=> $find_date_2,
		"VOTE"						=> $find_vote,
		"VOTE_EXACT_MATCH"			=> $find_vote_exact_match,
		"VOTE_ID"					=> $find_vote_id,
		"VOTE_USER"					=> $find_vote_user,
		"VOTE_USER_EXACT_MATCH"		=> $find_vote_user_exact_match,
		"SESSION"					=> $find_session,
		"SESSION_EXACT_MATCH"		=> $find_session_exact_match,
		"IP"						=> $find_ip,
		"IP_EXACT_MATCH"			=> $find_ip_exact_match
		);
}

// if submit "Save"
if ($lAdmin->EditAction() && $VOTE_RIGHT>="W" && check_bitrix_sessid())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$DB->StartTransaction();
		$ID = intval($ID);
		InitBVar($arFields["VALID"]);
		$arFieldsStore = Array(
			"VALID"	=> "'$arFields[VALID]'",
			);
		if (!$DB->Update("b_vote_event",$arFieldsStore,"WHERE ID='$ID'",$err_mess.__LINE__))
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".GetMessage("VOTE_SAVE_ERROR"), $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}
// Groups action
if(($arID = $lAdmin->GroupAction()) && $VOTE_RIGHT=="W" && check_bitrix_sessid())
{
		if($_REQUEST['action_target']=='selected')
		{
				$arID = Array();
				$rsData = CVoteEvent::GetList('', '', $arFilter);
				while($arRes = $rsData->Fetch())
						$arID[] = $arRes['ID'];
		}

		foreach($arID as $ID)
		{
				if($ID == '')
						continue;
				$ID = intval($ID);
				switch($_REQUEST['action'])
				{
				case "delete":
						if(!CVoteEvent::Delete($ID)):
							$lAdmin->AddGroupError(GetMessage("DELETE_ERROR"), $ID);
						endif;
						break;
				case "validate":
				case "devalidate":
						$varVALID = ($_REQUEST['action']=="validate"?"Y":"N");
						CVoteEvent::SetValid($ID, $varVALID);
						break;
				}
		}
}


/************** Initial list - Get data ****************************/
global $by, $order;

$rsData = CVoteEvent::GetList($by, $order, $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

/************** Initial list - Navigation **************************/
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("VOTE_PAGES")));

$lAdmin->AddHeaders(array(
				array("id"=>"ID", "content"=>"ID", "sort"=>"s_id", "default"=>true),
				array("id"=>"VOTE_USER_ID", "content"=>GetMessage("VOTE_VISITOR"), "sort"=>"s_vote_user", "default"=>true),
				array("id"=>"STAT_SESSION_ID", "content"=>GetMessage("VOTE_SESSION"), "sort"=>"s_session", "default"=>true),
				array("id"=>"IP", "content"=>"IP", "sort"=>"s_ip", "default"=>true),
				array("id"=>"DATE_VOTE", "content"=>GetMessage("VOTE_DATE"), "sort"=>"s_date", "default"=>true),
				array("id"=>"TITLE", "content"=>GetMessage("VOTE_VOTE"), "sort"=>"s_vote", "default"=>true),
				array("id"=>"VALID", "content"=>GetMessage("VOTE_VALID"), "sort"=>"s_valid", "default"=>true),
));

$arrUsers = array();
while($arRes = $rsData->NavNext(true, "f_"))
{
		$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("VALID",$f_VALID=="Y"?GetMessage("MAIN_YES"):GetMessage("MAIN_NO"));
	$row->AddViewField("VOTE_USER_ID","<a title=\"".GetMessage("VOTE_USER_LIST_TITLE")."\" href=\"vote_user_list.php?lang=".LANGUAGE_ID."&find_id=$f_VOTE_USER_ID&set_filter=Y\">$f_VOTE_USER_ID</a>");
	if (CModule::IncludeModule("statistic"))
		$row->AddViewField("STAT_SESSION_ID","<a title=\"".GetMessage("VOTE_SESSIONU_LIST_TITLE")."\" href=\"session_list.php?lang=".LANGUAGE_ID."&find_id=$f_STAT_SESSION_ID&set_filter=Y\">$f_STAT_SESSION_ID</a>");

	if ($f_TITLE <> '')
		$txt= "[<a title='".GetMessage("VOTE_EDIT_TITLE")."' href='vote_edit.php?lang=".LANGUAGE_ID."&ID=$f_VOTE_ID'>$f_VOTE_ID</a>] $f_TITLE";
	elseif ($f_DESCRIPTION_TYPE=="html")
		$txt= "[<a title='".GetMessage("VOTE_EDIT_TITLE")."' href='vote_edit.php?lang=".LANGUAGE_ID."&ID=$f_VOTE_ID'>$f_VOTE_ID</a>] ".TruncateText(strip_tags(htmlspecialcharsback($f_DESCRIPTION)),50);
	else
		$txt= "[<a href='vote_edit.php?lang=".LANGUAGE_ID."&ID=$f_VOTE_ID'>$f_VOTE_ID</a>] ".TruncateText($f_DESCRIPTION,50);

	$row->AddViewField("TITLE", $txt);


	$arActions = Array();
		$arActions[] = array("DEFAULT"=>true, "ICON"=>"view", "TEXT"=>GetMessage("VOTE_RESULT"), "ACTION"=>$lAdmin->ActionRedirect("vote_user_results.php?lang=".LANGUAGE_ID."&EVENT_ID=".$f_ID));

	if ($VOTE_RIGHT=="W")
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("VOTE_DELETE_U"), "ACTION"=>"if(confirm('".GetMessage('VOTE_DELETE_CONFIRMATION')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", 'VOTE_ID='.$f_ID));

	$row->AddActions($arActions);
}

/************** Initial list - Footer ******************************/
$lAdmin->AddFooter(
		array(
				array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
				array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
);
/************** Initial list - Buttons *****************************/
if ($VOTE_RIGHT=="W")
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("VOTE_DELETE"),
		"validate"=>GetMessage("VOTE_VALIDATE"),
		"devalidate"=>GetMessage("VOTE_DEVALIDATE"),
		));

$lAdmin->AddAdminContextMenu(array(), true);

/************** Initial list - Check AJAX **************************/
$lAdmin->CheckListMode();

/********************************************************************
				Html form
********************************************************************/
$APPLICATION->SetTitle(GetMessage("VOTE_PAGE_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<a name="tb"></a>

<? ShowError($strError);?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
		$sTableID."_filter",
		array(
		GetMessage("VOTE_FL_USER_ID"),
		GetMessage("VOTE_FL_SESS_ID"),
		GetMessage("VOTE_FL_IP"),
		GetMessage("VOTE_FL_DATE"),
		GetMessage("VOTE_FL_VOTE"),
		GetMessage("VOTE_FL_VALID"),
		)
);

$oFilter->Begin();
?>
<tr>
	<td><b>ID</b></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"><?=InputType("checkbox", "find_id_exact_match", "Y", $find_id_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("VOTE_F_USER")?></td>
	<td><input type="text" name="find_vote_user" size="47" value="<?echo htmlspecialcharsbx($find_vote_user)?>"><?=InputType("checkbox", "find_vote_user_exact_match", "Y", $find_vote_user_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td><?echo GetMessage("VOTE_F_SESSION")?></td>
	<td><input type="text" name="find_session" size="47" value="<?echo htmlspecialcharsbx($find_session)?>"><?=InputType("checkbox", "find_session_exact_match", "Y", $find_session_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td>IP</td>
	<td><input type="text" name="find_ip" size="47" value="<?echo htmlspecialcharsbx($find_ip)?>"><?=InputType("checkbox", "find_ip_exact_match", "Y", $find_ip_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_DATE").":"?></td>
	<td nowrap><?echo CalendarPeriod("find_date_1", $find_date_1, "find_date_2", $find_date_2, "form1","Y")?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("VOTE_F_VOTE")?></td>
	<td nowrap><input type="text" name="find_vote" size="47" value="<?echo htmlspecialcharsbx($find_vote)?>"><?=InputType("checkbox", "find_vote_exact_match", "Y", $find_vote_exact_match, false, "", "title='".GetMessage("VOTE_EXACT_MATCH")."'")?>&nbsp;<?=ShowFilterLogicHelp()?><br><?
		echo SelectBox("find_vote_id", CVote::GetDropDownList(), GetMessage("VOTE_ALL"), htmlspecialcharsbx($find_vote_id));
		?></td>
</tr>
<tr valign="top">
	<td nowrap><?echo GetMessage("VOTE_F_VALID_TITLE")?></td>
	<td nowrap><input type="checkbox" name="find_valid" id="find_valid" value="Y" <?=($find_valid == "Y" ? "checked='checked'" : "")?> />
		<label for="find_valid"><?=GetMessage("VOTE_F_VALID")?></label><?
		?></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$oFilter->End();
#############################################################
?>
</form>
<?
/************** Initial list - Display list ************************/
$lAdmin->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
