<?php

namespace Bitrix\Im\V2\Entity\Calendar;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Loader;
use CCalendarEvent;

class CalendarCollection extends EntityCollection
{
	public static function getRestEntityName(): string
	{
		return 'calendars';
	}

	public static function initByGetListArray(array $calendarsInfo): self
	{
		$calendars = new static();

		foreach ($calendarsInfo as $calendarInfo)
		{
			$calendars[] = CalendarItem::initByGetListArray($calendarInfo);
		}

		return $calendars;
	}

	public static function getByIds(array $ids, ?Context $context = null): self
	{
		if (empty($ids) || !Loader::includeModule('calendar'))
		{
			return (new static())->setContext($context);
		}

		$context = $context ?? Locator::getContext();
		$checkPermissions = false;

		$calendarGetList = CCalendarEvent::GetList([
			'arFilter' => [
				'ID' => $ids,
				'DELETED' => false,
			],
			'parseRecursion' => false,
			'fetchAttendees' => true,
			'userId' => $context->getUserId(),
			'fetchMeetings' => false,
			'setDefaultLimit' => false,
			'checkPermissions' => $checkPermissions,
		]);

		return static::initByGetListArray($calendarGetList)->setContext($context);
	}
}