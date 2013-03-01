<?php
namespace Change\Http\Rest;

use Change\Documents\Property;
use Change\Documents\AbstractDocument;
use Change\Http\Rest\Result\DocumentLink;

/**
 * @name \Change\Http\Rest\PropertyConverter
 */
class PropertyConverter
{
	/**
	 * @var \Change\Documents\AbstractDocument
	 */
	protected $document;

	/**
	 * @var \Change\Http\UrlManager
	 */
	protected $urlManager;

	/**
	 * @var \Change\Documents\Property
	 */
	protected $property;

	/**
	 * @param \Change\Documents\AbstractDocument $document
	 * @param \Change\Documents\Property $property
	 * @param \Change\Http\UrlManager $urlManager
	 */
	public function __construct(AbstractDocument $document, Property $property, \Change\Http\UrlManager $urlManager = null)
	{
		$this->document = $document;
		$this->property = $property;
		$this->urlManager = $urlManager;
	}

	/**
	 * @param \Change\Http\UrlManager $urlManager
	 */
	public function setUrlManager(\Change\Http\UrlManager $urlManager = null)
	{
		$this->urlManager = $urlManager;
	}

	/**
	 * @throws \RuntimeException
	 * @return \Change\Http\UrlManager
	 */
	public function getUrlManager()
	{
		if ($this->urlManager === null)
		{
			throw new \RuntimeException('UrlManager is not set', 70000);
		}
		return $this->urlManager;
	}


	/**
	 * @return mixed
	 * @throws \RuntimeException
	 */
	public function getRestValue()
	{
		$value = $this->property->getValue($this->document);
		return $this->convertToRestValue($value);
	}

	/**
	 * @param mixed $propertyValue
	 * @return mixed
	 * @throws \RuntimeException
	 */
	public function convertToRestValue($propertyValue)
	{
		$restValue = null;

		switch ($this->property->getType())
		{
			case Property::TYPE_DATE:
			case Property::TYPE_DATETIME:
				if ($propertyValue instanceof \DateTime)
				{
					$restValue = $propertyValue->format(\DateTime::ISO8601);
				}
				elseif ($propertyValue !== null)
				{
					throw new \RuntimeException('Invalid Property value', 70001);
				}
				break;
			case Property::TYPE_DOCUMENT:
				if ($propertyValue instanceof AbstractDocument)
				{
					$restValue = new DocumentLink($this->getUrlManager(), $propertyValue, DocumentLink::MODE_PROPERTY);
				}
				elseif ($propertyValue !== null)
				{
					throw new \RuntimeException('Invalid Property value', 70001);
				}
				break;
			case Property::TYPE_DOCUMENTARRAY:
				if (is_array($propertyValue))
				{
					$urlManager = $this->getUrlManager();
					$restValue = array_map(function ($doc) use ($urlManager)
					{
						if (!($doc instanceof AbstractDocument))
						{
							throw new \RuntimeException('Invalid Property value', 70001);
						}
						return new DocumentLink($urlManager, $doc, DocumentLink::MODE_PROPERTY);
					}, $propertyValue);
				}
				elseif ($propertyValue !== null)
				{
					throw new \RuntimeException('Invalid Property value', 70001);
				}
				break;
			default:
				$restValue = $propertyValue;
				break;
		}
		return $restValue;
	}

	/**
	 * @param  mixed $restValue
	 * @throws \RuntimeException
	 */
	public function setPropertyValue($restValue)
	{
		$value = $this->convertToPropertyValue($restValue);
		$this->property->setValue($this->document, $value);
	}

	/**
	 * @param mixed $restValue
	 * @return mixed
	 * @throws \RuntimeException
	 */
	public function convertToPropertyValue($restValue)
	{
		$value = null;
		switch ($this->property->getType())
		{
			case Property::TYPE_DATE:
			case Property::TYPE_DATETIME:
				if (is_string($restValue))
				{
					$value = \DateTime::createFromFormat(\DateTime::ISO8601, $restValue);
					if ($value === false)
					{
						throw new \RuntimeException('Invalid Property value', 70001);
					}
				}
				elseif ($restValue !== null)
				{
					throw new \RuntimeException('Invalid Property value', 70001);
				}
				break;

			case Property::TYPE_DOCUMENT:
				if ($restValue !== null)
				{
					$value = $this->document->getDocumentManager()->getDocumentInstance($restValue);
					if ($value === null)
					{
						throw new \RuntimeException('Invalid Property value', 70001);
					}
				}
				break;

			case Property::TYPE_DOCUMENTARRAY:
				if (is_array($restValue))
				{
					$documentManager = $this->document->getDocumentManager();
					$value = array_map(function($id) use ($documentManager) {
						$doc = $documentManager->getDocumentInstance($id);
						if ($doc === null)
						{
							throw new \RuntimeException('Invalid Property value', 70001);
						}
						return $doc;
					}, $restValue);
				}
				elseif ($restValue === null)
				{
					$value = array();
				}
				else
				{
					throw new \RuntimeException('Invalid Property value', 70001);
				}
				break;
			default:
				$value = $restValue;
				break;
		}
		return $value;
	}
}