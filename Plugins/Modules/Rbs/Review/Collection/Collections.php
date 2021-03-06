<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Review\Collection;

use Change\Collection\CollectionArray;
use Change\Events\Event;

/**
 * @name \Rbs\Review\Collection\Collections
 */
class Collections
{
	const PROMOTED_REVIEW_MODES_MANUAL = 'manual';
	const PROMOTED_REVIEW_MODES_PROMOTED = 'promoted';
	const PROMOTED_REVIEW_MODES_RECENT = 'recent';

	/**
	 * @param Event $event
	 */
	public function addPromotedReviewModes(Event $event)
	{
		$applicationServices = $event->getApplicationServices();
		if ($applicationServices)
		{
			$i18nManager = $applicationServices->getI18nManager();
			$collection = new CollectionArray('Rbs_Review_Collection_PromotedReviewModes', array(
				static::PROMOTED_REVIEW_MODES_MANUAL => $i18nManager->trans('m.rbs.review.admin.promoted_review_modes_manual'),
				static::PROMOTED_REVIEW_MODES_PROMOTED => $i18nManager->trans('m.rbs.review.admin.promoted_review_modes_promoted'),
				static::PROMOTED_REVIEW_MODES_RECENT => $i18nManager->trans('m.rbs.review.admin.promoted_review_modes_recent')
			));
			$event->setParam('collection', $collection);
			$event->stopPropagation();
		}
	}
}