<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Commerce\Events;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;

/**
 * @name \Rbs\Commerce\Events\SharedListeners
 */
class SharedListeners implements SharedListenerAggregateInterface
{
	/**
	 * Attach one or more listeners
	 * Implementors may add an optional $priority argument; the SharedEventManager
	 * implementation will pass this to the aggregate.
	 * @param SharedEventManagerInterface $events
	 */
	public function attachShared(SharedEventManagerInterface $events)
	{
		$callback = function ($event){
			if (($event instanceof \Change\Events\Event) &&
				($eventManagerFactory = $event->getParam('eventManagerFactory')) instanceof \Change\Events\EventManagerFactory)
			{
				$commerceServices = new \Rbs\Commerce\CommerceServices($event->getApplication(), $eventManagerFactory, $event->getApplicationServices());
				$event->getServices()->set('commerceServices', $commerceServices);
			}
		};
		$events->attach(array('Commands', 'JobManager', 'Http'), 'registerServices', $callback, 5);
	}

	/**
	 * Detach all previously attached listeners
	 * @param SharedEventManagerInterface $events
	 */
	public function detachShared(SharedEventManagerInterface $events)
	{
		//TODO: Implement detachShared() method.
	}
}