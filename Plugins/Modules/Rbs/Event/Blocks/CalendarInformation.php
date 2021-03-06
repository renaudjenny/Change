<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Event\Blocks;

use Change\Documents\Property;

/**
 * @name \Rbs\Event\Blocks\CalendarInformation
 */
class CalendarInformation extends \Rbs\Event\Blocks\Base\BaseEventListInformation
{
	public function onInformation(\Change\Events\Event $event)
	{
		parent::onInformation($event);
		$i18nManager = $event->getApplicationServices()->getI18nManager();
		$ucf = array('ucf');
		$this->setLabel($i18nManager->trans('m.rbs.event.admin.calendar_label', $ucf));
		$this->addInformationMeta('sectionId', Property::TYPE_DOCUMENTID, false, null)
			->setAllowedModelsNames('Rbs_Website_Section')
			->setLabel($i18nManager->trans('m.rbs.event.admin.base_event_list_section_id', $ucf));
		$this->addInformationMeta('includeSubSections', Property::TYPE_BOOLEAN, false, true)
			->setLabel($i18nManager->trans('m.rbs.event.admin.base_event_list_include_sub_sections', $ucf));
		$this->getParameterInformation('templateName')->setDefaultValue('calendar.twig');
	}
}
