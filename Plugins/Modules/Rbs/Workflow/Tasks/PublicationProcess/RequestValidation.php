<?php
namespace Rbs\Workflow\Tasks\PublicationProcess;

use Change\Documents\Interfaces\Publishable;
use Change\Workflow\Interfaces\WorkItem;
use Zend\EventManager\Event;

/**
* @name \Rbs\Workflow\Tasks\PublicationProcess\RequestValidation
*/
class RequestValidation
{
	/**
	 * @param Event $event
	 * @throws \Exception
	 */
	public function execute(Event $event)
	{
		/* @var $workItem WorkItem */
		$workItem = $event->getParam('workItem');
		$document = $workItem->getWorkflowInstance()->getDocument();
		if ($document instanceof Publishable)
		{
			if ($document->getPublicationStatus() === Publishable::STATUS_DRAFT)
			{
				$documentServices = $document->getDocumentServices();
				$transactionManager = $documentServices->getApplicationServices()->getTransactionManager();
				try
				{
					$transactionManager->begin();
					$document->updatePublicationStatus(Publishable::STATUS_VALIDATION);
					$transactionManager->commit();
				}
				catch (\Exception $e)
				{
					throw $transactionManager->rollBack($e);
				}
			}
		}
	}
}