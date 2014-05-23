<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\User\Http\Rest;

use Change\Http\Rest\Request;
use Change\Http\Rest\V1\DiscoverNameSpace;
use Change\Http\Rest\V1\Resolver;
use Rbs\User\Http\Rest\Actions\AddPermission;
use Rbs\User\Http\Rest\Actions\AddPermissionRules;
use Rbs\User\Http\Rest\Actions\AddUsersInGroup;
use Rbs\User\Http\Rest\Actions\GetPermission;
use Rbs\User\Http\Rest\Actions\GetPermissionRules;
use Rbs\User\Http\Rest\Actions\GetUserTokens;
use Rbs\User\Http\Rest\Actions\RemovePermission;
use Rbs\User\Http\Rest\Actions\RemovePermissionRule;
use Rbs\User\Http\Rest\Actions\RemoveUsersFromGroup;
use Rbs\User\Http\Rest\Actions\RevokeToken;

/**
 * @name \Rbs\User\Http\Rest\UserResolver
 */
class UserResolver
{
	/**
	 * @param \Change\Http\Rest\V1\Resolver $resolver
	 */
	protected $resolver;

	/**
	 * @param \Change\Http\Rest\V1\Resolver $resolver
	 */
	function __construct(Resolver $resolver)
	{
		$this->resolver = $resolver;
	}

	/**
	 * @param \Change\Http\Event $event
	 * @param string[] $namespaceParts
	 * @return string[]
	 */
	public function getNextNamespace($event, $namespaceParts)
	{
		return array('userTokens', 'revokeToken', 'permissionRules', 'addPermissionRules', 'removePermissionRule');
	}

	/**
	 * Set Event params: resourcesActionName, documentId, LCID
	 * @param \Change\Http\Event $event
	 * @param array $resourceParts
	 * @param $method
	 * @return void
	 */
	public function resolve($event, $resourceParts, $method)
	{
		$nbParts = count($resourceParts);
		if ($nbParts == 0 && $method === Request::METHOD_GET)
		{
			array_unshift($resourceParts, 'user');
			$event->setParam('namespace', implode('.', $resourceParts));
			$event->setParam('resolver', $this);
			$action = function ($event)
			{
				$action = new DiscoverNameSpace();
				$action->execute($event);
			};
			$event->setAction($action);
			return;
		}
		elseif ($nbParts == 1)
		{
			$actionName = $resourceParts[0];
			if ($actionName === 'userTokens')
			{
				$action = new GetUserTokens();
				$event->setAction(function($event) use($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Consumer', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
			else if ($actionName === 'revokeToken')
			{
				$action = new RevokeToken();
				$event->setAction(function($event) use($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Administrator', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
			else if ($actionName === 'permissionRules')
			{
				$action = new GetPermissionRules();
				$event->setAction(function($event) use ($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Consumer', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
			else if ($actionName === 'addPermissionRules')
			{
				$action = new AddPermissionRules();
				$event->setAction(function($event) use ($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Administrator', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
			else if ($actionName === 'removePermissionRule')
			{
				$action = new RemovePermissionRule();
				$event->setAction(function($event) use ($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Administrator', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
			else if ($actionName === 'removeUsersFromGroup')
			{
				$action = new RemoveUsersFromGroup();
				$event->setAction(function($event) use ($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Administrator', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
			else if ($actionName === 'addUsersInGroup')
			{
				$action = new AddUsersInGroup();
				$event->setAction(function($event) use ($action) {$action->execute($event);});
				$authorisation = function() use ($event)
				{
					return $event->getPermissionsManager()->isAllowed('Administrator', $event->getAuthenticationManager()->getCurrentUser()->getId());
				};
				$event->setAuthorization($authorisation);
			}
		}
	}
}