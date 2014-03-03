<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Media\Http\Rest\Actions;

use Change\Http\Result;
use Change\Http\Event;
use Zend\Http\Response;

/**
 * @name \Rbs\Media\Http\Rest\Actions\Resize
 */
class Resize
{

	public function resize(Event $event)
	{
		$maxWidth = $event->getRequest()->getQuery('maxWidth', 0);
		$maxHeight = $event->getRequest()->getQuery('maxHeight', 0);
		$documentId = $event->getParam('documentId');
		$document = $event->getApplicationServices()->getDocumentManager()->getDocumentInstance($documentId);
		if (!($document instanceof \Rbs\Media\Documents\Image))
		{
			$event->setResult(new Result(Response::STATUS_CODE_404));
			return;
		}
		$result = new Result(Response::STATUS_CODE_301);
		$result->setHeaderLocation($document->getPublicURL($maxWidth, $maxHeight));
		$event->setResult($result);
	}
}