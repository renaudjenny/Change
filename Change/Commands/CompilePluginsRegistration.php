<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Commands;

use Change\Commands\Events\Event;

/**
 * @name \Change\Commands\CompilePluginsRegistration
 */
class CompilePluginsRegistration
{
	/**
	 * @param Event $event
	 */
	public function execute(Event $event)
	{
		$applicationServices = $event->getApplicationServices();
		$pluginManager = $applicationServices->getPluginManager();
		$plugins = $pluginManager->compile();
		$nbPlugins = count($plugins);

		$response = $event->getCommandResponse();
		$response->addInfoMessage($nbPlugins. ' plugins registered');
	}
}