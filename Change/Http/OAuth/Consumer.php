<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Http\OAuth;

/**
 * @name \Change\Http\Rest\OAuth\Consumer
 */
class Consumer
{
	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $secret;

	/**
	 * @var integer
	 */
	protected $timestampMaxOffset;

	/**
	 * @var string
	 */
	protected $tokenAccessValidity;

	/**
	 * @var string
	 */
	protected $tokenRequestValidity;

	/**
	 * @var boolean
	 */
	protected $active;


	/**
	 * @var integer
	 */
	protected $applicationId;

	/**
	 * @var string
	 */
	protected $applicationName;

	/**
	 * @param string $key
	 * @param string $secret
	 */
	function __construct($key = null, $secret = null)
	{
		$this->key = $key;
		$this->secret = $secret;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setKey($key)
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $secret
	 * @return $this
	 */
	public function setSecret($secret)
	{
		$this->secret = $secret;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->secret;
	}

	/**
	 * @param boolean $active
	 */
	public function setActive($active)
	{
		$this->active = $active;
	}

	/**
	 * @return boolean
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param int $timestampMaxOffset
	 */
	public function setTimestampMaxOffset($timestampMaxOffset)
	{
		$this->timestampMaxOffset = $timestampMaxOffset;
	}

	/**
	 * @return int
	 */
	public function getTimestampMaxOffset()
	{
		return $this->timestampMaxOffset;
	}

	/**
	 * @param string $tokenAccessValidity
	 */
	public function setTokenAccessValidity($tokenAccessValidity)
	{
		$this->tokenAccessValidity = $tokenAccessValidity;
	}

	/**
	 * @return string
	 */
	public function getTokenAccessValidity()
	{
		return $this->tokenAccessValidity;
	}

	/**
	 * @param string $tokenRequestValidity
	 */
	public function setTokenRequestValidity($tokenRequestValidity)
	{
		$this->tokenRequestValidity = $tokenRequestValidity;
	}

	/**
	 * @return string
	 */
	public function getTokenRequestValidity()
	{
		return $this->tokenRequestValidity;
	}

	/**
	 * @param int $applicationId
	 * @return $this
	 */
	public function setApplicationId($applicationId)
	{
		$this->applicationId = $applicationId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getApplicationId()
	{
		return $this->applicationId;
	}

	/**
	 * @param string $applicationName
	 * @return $this
	 */
	public function setApplicationName($applicationName)
	{
		$this->applicationName = $applicationName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getApplicationName()
	{
		return $this->applicationName;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array('consumer_key' => $this->key, 'consumer_secret' => $this->secret,
			'timestamp_max_offset' => $this->timestampMaxOffset, 'token_access_validity' => $this->tokenAccessValidity,
			'token_request_validity' => $this->tokenRequestValidity, 'active' => $this->active);
	}
}