<?php

namespace Bitrix\Calendar\Access\Rule;

use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\Rule\Traits\ExtranetUserTrait;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Rule\Traits\CurrentUserTrait;
use Bitrix\Calendar\Access\Rule\Traits\SectionTrait;
use CCalendarSect;

class SectionEventViewCommentsRule extends \Bitrix\Main\Access\Rule\AbstractRule
{
	use SectionTrait, CurrentUserTrait, ExtranetUserTrait;

	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!$item instanceof SectionModel)
		{
			return false;
		}

		if ($item->getType() === Dictionary::CALENDAR_TYPE['open_event'])
		{
			return true;
		}

		if (!$this->hasCurrentUser())
		{
			return true;
		}

		if (!$this->canSeeOwnerIfExtranetUser($item, $this->user))
		{
			return false;
		}

		if ($this->user->isAdmin() || $this->user->isSocNetAdmin($item->getType()))
		{
			return true;
		}

		if ($this->isOwner($item, $this->user->getUserId()))
		{
			return true;
		}

		$type = TypeModel::createFromSectionModel($item);

		return
			$this->controller->check(
				ActionDictionary::ACTION_TYPE_VIEW,
				$type,
			)
			&& in_array(
				ActionDictionary::getOldActionKeyByNewActionKey(ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL),
				CCalendarSect::GetOperations($item->getId(), $this->user->getUserId()),
				true
			);
	}
}