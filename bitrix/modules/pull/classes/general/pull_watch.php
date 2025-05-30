<?php

class CAllPullWatch
{
	const bucket_size = 100;

	private static $arUpdate = Array();
	private static $arInsert = Array();
	private static array $tagCache = [];

	public static function Add($userId, $tag, $immediate = false)
	{
		global $DB, $CACHE_MANAGER;

		$userId = intval($userId);
		if ($userId == 0 || $tag == '')
			return false;

		$arResult = $CACHE_MANAGER->Read(3600, $cache_id="b_pw_".$userId, "b_pull_watch");
		if ($arResult)
			$arResult = $CACHE_MANAGER->Get($cache_id);

		if(!$arResult)
		{
			CTimeZone::Disable();
			$strSql = "
					SELECT ID, USER_ID, TAG, ".$DB->DatetimeToTimestampFunction("DATE_CREATE")." AS DATE_CREATE
					FROM b_pull_watch
					WHERE USER_ID = ".intval($userId)."
			";
			CTimeZone::Enable();
			$dbRes = $DB->Query($strSql);
			while ($arRes = $dbRes->Fetch())
				$arResult[$arRes["TAG"]] = $arRes;

			$CACHE_MANAGER->Set($cache_id, $arResult);
		}
		if (!empty($arResult[$tag]))
		{
			if ($arResult[$tag]['DATE_CREATE']+1860 > time())
			{
				self::$arUpdate[intval($arResult[$tag]['ID'])] = intval($arResult[$tag]['ID']);
				return true;
			}
			else
			{
				self::Delete($userId, $tag);
				return self::Add($userId, $tag);
			}
		}
		$CACHE_MANAGER->Clean("b_pw_".$userId, "b_pull_watch");

		self::$arInsert[trim($tag)] = trim($tag);

		if ($immediate || defined('BX_CHECK_AGENT_START') && !defined('BX_WITH_ON_AFTER_EPILOG'))
		{
			self::DeferredSql($userId);
		}

		self::cleanCacheByTag((string)$tag);

		return true;
	}

	public static function DeferredSql($userId = false)
	{
		global $DB, $USER;
		if (empty(self::$arUpdate) && empty(self::$arInsert))
			return false;

		$userId = intval($userId);
		if (!$userId)
		{
			if (defined('PULL_USER_ID'))
			{
				$userId = PULL_USER_ID;
			}
			else if (is_object($GLOBALS['USER']) && $GLOBALS['USER']->GetID() > 0)
			{
				$userId = $GLOBALS['USER']->GetId();
			}
			else if (intval($_SESSION["SESS_SEARCHER_ID"]) <= 0 && intval($_SESSION["SESS_GUEST_ID"]) > 0 && \CPullOptions::GetGuestStatus())
			{
				$userId = intval($_SESSION["SESS_GUEST_ID"])*-1;
			}
		}
		if ($userId === 0)
		{
			return false;
		}

		$arChannel = CPullChannel::Get($userId);
		if (!$arChannel)
		{
			return false;
		}
		if (!empty(self::$arUpdate))
		{
			$DB->Query("
				UPDATE b_pull_watch
				SET DATE_CREATE = ".$DB->CurrentTimeFunction().", CHANNEL_ID = '".$DB->ForSQL($arChannel['CHANNEL_ID'])."'
				WHERE ID IN (".(implode(',', self::$arUpdate)).")
			");
		}

		if ($DB->type == "MYSQL")
		{
			if (!empty(self::$arInsert))
			{
				$strSqlPrefix = "INSERT INTO b_pull_watch (USER_ID, CHANNEL_ID, TAG, DATE_CREATE) VALUES ";
				$maxValuesLen = 2048;
				$strSqlValues = "";

				foreach(self::$arInsert as $tag)
				{
					$strSqlValues .= ",\n(".intval($userId).", '".$DB->ForSql($arChannel['CHANNEL_ID'])."', '".$DB->ForSql($tag)."', ".$DB->CurrentTimeFunction().")";
					if(mb_strlen($strSqlValues) > $maxValuesLen)
					{
						$DB->Query($strSqlPrefix.mb_substr($strSqlValues, 2));
						$strSqlValues = "";
					}
				}
				if($strSqlValues <> '')
				{
					$DB->Query($strSqlPrefix.mb_substr($strSqlValues, 2));
				}
			}
		}
		else if (!empty(self::$arInsert))
		{
			foreach(self::$arInsert as $tag)
			{
				$DB->Query("INSERT INTO b_pull_watch (USER_ID, CHANNEL_ID, TAG, DATE_CREATE) VALUES (".intval($userId).", '".$DB->ForSql($arChannel['CHANNEL_ID'])."', '".$DB->ForSql($tag)."', ".$DB->CurrentTimeFunction().")");
			}
		}

		self::$arInsert = Array();
		self::$arUpdate = Array();

		return true;
	}

	public static function Delete($userId, $tag = null)
	{
		global $DB, $CACHE_MANAGER;

		$strSql = "DELETE FROM b_pull_watch WHERE USER_ID = ".intval($userId).(!is_null($tag)? " AND TAG = '".$DB->ForSQL($tag)."'": "");
		$DB->Query($strSql);

		$CACHE_MANAGER->Clean("b_pw_".$userId, "b_pull_watch");

		if ($tag === null)
		{
			self::cleanCacheByUserId((int)$userId);
		}
		else
		{
			self::cleanCacheByTag((string)$tag);
		}

		return true;
	}

	public static function Extend($userId, $tags)
	{
		global $DB, $CACHE_MANAGER;

		if (intval($userId) == 0)
		{
			return false;
		}

		if (is_array($tags))
		{
			$isMulti = true;
			$searchTag = '';
			if (empty($tags))
			{
				return false;
			}
		}
		else
		{
			$isMulti = false;
			$searchTag = trim($tags);
			if ($searchTag == '')
			{
				return false;
			}
			else
			{
				$tags = Array($searchTag);
			}
		}

		$result = Array();
		foreach ($tags as $id => $tag)
		{
			$result[$tag] = false;
			$tags[$id] = $DB->ForSQL($tag);
		}

		$updateIds = Array();
		$strSql = "SELECT ID, TAG FROM b_pull_watch WHERE USER_ID = ".intval($userId)." AND TAG IN ('".implode("', '", $tags)."')";
		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->Fetch())
		{
			$updateIds[] = $arRes['ID'];
			$result[$arRes['TAG']] = true;
		}

		if ($updateIds)
		{
			$DB->Query("UPDATE b_pull_watch SET DATE_CREATE = ".$DB->CurrentTimeFunction()." WHERE ID IN (".implode(', ', $updateIds).")");
			$CACHE_MANAGER->Clean("b_pw_".$userId, "b_pull_watch");
		}

		return $isMulti? $result: $result[$searchTag];
	}

	/**
	 * Sends a message to users, who have subscribed to the tag(s).
	 *
	 * @param string|string[] $tag Tag, or array of tags.
	 * @param array $parameters Message parameters.
	 * @param string $channelType Type of the channel: \CPullChannel::TYPE_PRIVATE | \CPullChannel::TYPE_SHARED .
	 * @return bool
	 */
	public static function AddToStack($tag, $parameters, $channelType = \CPullChannel::TYPE_PRIVATE)
	{
		if (empty($tag))
		{
			return false;
		}

		if (!is_array($tag))
		{
			$tag = [$tag];
		}

		$userIds = array_unique(array_merge(...self::getUsersByTags($tag)));

			if (isset($parameters['skip_users']) && !empty($parameters['skip_users']) && is_array($parameters['skip_users']))
		{
			$userIds = array_diff($userIds, $parameters['skip_users']);
		}

		if (!empty($userIds))
		{
			\Bitrix\Pull\Event::add($userIds, $parameters, $channelType);
		}

		return true;
	}

	protected static function getUsersByTags(array $tags): array
	{
		$userIds = $nonCachedTags = [];

		foreach ($tags as $tag)
		{
			if (array_key_exists($tag, self::$tagCache))
			{
				if (is_array(self::$tagCache[$tag]))
				{
					$userIds[] = self::$tagCache[$tag];
				}

				continue;
			}

			$nonCachedTags[] = $tag;
		}

		if (!empty($nonCachedTags))
		{
			$userIds[] = self::getUsersByTagsInternal($nonCachedTags);
		}

		return $userIds;
	}

	public static function getUsersByTag(string $tag): array
	{
		self::getUsersByTags([$tag]);

		return self::$tagCache[$tag] ?? [];
	}

	private static function getUsersByTagsInternal(array $tags): array
	{
		$result = \Bitrix\Pull\Model\WatchTable::query()
			->setSelect(['USER_ID', 'TAG'])
			->whereIn('TAG', $tags)
			->fetchAll()
		;

		$usersByTag = $users = [];
		foreach ($result as $data)
		{
			$userId = (int)$data['USER_ID'];
			$usersByTag[$data['TAG']][$userId] = $userId;
			$users[$userId] = $userId;
		}

		foreach ($tags as $tag)
		{
			self::$tagCache[$tag] = $usersByTag[$tag] ?? null;
		}

		return $users;
	}

	public static function GetUserList($tag)
	{
		$userIds = \CPullWatch::getUsersByTag($tag);

		return array_map('strval', $userIds);
	}

	public static function cleanCache(): void
	{
		self::$tagCache = [];
	}

	private static function cleanCacheByTag(string $tag): void
	{
		unset(self::$tagCache[$tag]);
	}

	private static function cleanCacheByUserId(int $userId): void
	{
		foreach (self::$tagCache as $tag => $userIds)
		{
			if (is_array($userIds) && in_array($userId, $userIds, true))
			{
				unset(self::$tagCache[$tag]);
			}
		}
	}
}
