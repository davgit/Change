<?php
namespace Change\Http\Rest\Actions;

/**
* @name \Change\Http\Rest\Actions\Activation
*/
class Activation
{
	/**
	 * Use Required Event Params: documentId
	 * @param \Change\Http\Event $event
	 * @throws \RuntimeException
	 * @throws \Exception
	 */
	public function execute($event)
	{
		$documentId = $event->getRequest()->getQuery('documentId');
		$documentManager = $event->getApplicationServices()->getDocumentManager();
		$document = $documentManager->getDocumentInstance($documentId);
		if ($document instanceof \Change\Documents\Interfaces\Activable)
		{
			if ($document instanceof \Change\Documents\Interfaces\Localizable)
			{
				$LCID = $event->getRequest()->getQuery('LCID', $document->getRefLCID());
				try
				{
					$documentManager->pushLCID($LCID);
					$this->updateStatus($event, $document, $this->getNewStatus(), $LCID);
					$documentManager->popLCID();
				}
				catch (\Exception $e)
				{
					$documentManager->popLCID($e);
				}
			}
			else
			{
				$this->updateStatus($event, $document, $this->getNewStatus());
			}
		}
	}

	/**
	 * @return boolean
	 */
	protected function getNewStatus()
	{
		return true;
	}

	/**
	 * @param \Change\Http\Event $event
	 * @param \Change\Documents\Interfaces\Activable $document
	 * @param boolean $newStatus
	 * @param string $LCID
	 * @throws \Exception
	 */
	protected function updateStatus($event, $document, $newStatus, $LCID = null)
	{
		$transactionManager = $event->getApplicationServices()->getTransactionManager();
		try
		{
			$transactionManager->begin();
			$document->updateActivationStatus($newStatus);
			$result = new \Change\Http\Rest\Result\ArrayResult();
			$pc = new \Change\Http\Rest\ValueConverter($event->getUrlManager(), null);
			if ($newStatus)
			{
				$l = new \Change\Http\Rest\Result\Link($event->getUrlManager(), 'actions/deactivate', 'deactivate');
			}
			else
			{
				$l = new \Change\Http\Rest\Result\Link($event->getUrlManager(), 'actions/activate', 'activate');
			}
			$query = ['documentId' => $document->getId()];
			if ($LCID)
			{
				$query['LCID'] = $LCID;
			}
			$l->setQuery($query);

			$result->setArray([
				'active' => $document->getDocumentModel()->getPropertyValue($document, 'active'),
				'modificationDate' =>  $pc->toRestValue($document->getDocumentModel()->getPropertyValue($document, 'modificationDate'),
						\Change\Documents\Property::TYPE_DATETIME),
				'action' => $l->toArray()
			]);
			$event->setResult($result);
			$transactionManager->commit();
		}
		catch (\Exception $e)
		{
			throw $transactionManager->rollBack($e);
		}
	}
} 