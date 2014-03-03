<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Notification\Documents;

/**
 * @name \Rbs\Notification\Documents\Notification
 */
class Notification extends \Compilation\Rbs\Notification\Documents\Notification
{
	const STATUS_NEW = 'new';
	const STATUS_READ = 'read';
	const STATUS_DELETED = 'deleted';

	public function setStatus($status)
	{
		switch ($status)
		{
			case static::STATUS_NEW:
			case static::STATUS_READ:
			case static::STATUS_DELETED:
			{
				parent::setStatus($status);
				break;
			}
			default:
				throw new \Exception('status must be: "' . static::STATUS_NEW . '", "' .
					static::STATUS_READ . '" or "' . static::STATUS_DELETED . '". "' . $status . '" given', 999999);
		}
	}

	protected function onCreate()
	{
		if (!$this->getStatus())
		{
			$this->setStatus(static::STATUS_NEW);
		}
	}
}
