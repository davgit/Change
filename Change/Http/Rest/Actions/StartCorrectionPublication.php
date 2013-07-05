<?php
namespace Change\Http\Rest\Actions;

use Change\Documents\AbstractDocument;
use Change\Documents\Interfaces\Correction;
use Change\Documents\Interfaces\Localizable;
use Change\Http\Rest\Result\ArrayResult;
use Change\Http\Rest\Result\DocumentLink;
use Change\Http\Rest\Result\ErrorResult;
use Zend\Http\Response as HttpResponse;

/**
 * @name \Change\Http\Rest\Actions\StartCorrectionPublication
 */
class StartCorrectionPublication
{

	/**
	 * @param \Change\Http\Event $event
	 * @throws \RuntimeException
	 * @return AbstractDocument|null
	 */
	protected function getDocument($event)
	{
		$documentId = intval($event->getParam('documentId'));
		$document = $event->getDocumentServices()->getDocumentManager()->getDocumentInstance($documentId);
		if (!$document)
		{
			return null;
		}

		if (!($document->getDocumentModel()->useCorrection()))
		{
			throw new \RuntimeException('Invalid Parameter: documentId', 71000);
		}
		return $document;
	}

	/**
	 * Use Required Event Params: documentId
	 * @param \Change\Http\Event $event
	 * @throws \RuntimeException
	 */
	public function execute($event)
	{
		$document = $this->getDocument($event);
		if (!$document)
		{
			return;
		}

		$LCID = null;
		if ($document instanceof Localizable)
		{
			$LCID = $event->getParam('LCID');
			if (!$LCID || !$event->getApplicationServices()->getI18nManager()->isSupportedLCID($LCID))
			{
				throw new \RuntimeException('Invalid Parameter: LCID', 71000);
			}
		}

		$publishImmediately = $event->getRequest()->getQuery('publishImmediately');
		if ($publishImmediately)
		{
			if ($publishImmediately === 'true')
			{
				$publishImmediately = true;
			}
			elseif ($publishImmediately === 'false')
			{
				$publishImmediately = false;
			}
			else
			{
				throw new \RuntimeException('Invalid Parameter: publishImmediately', 71000);
			}
		}
		else
		{
			$publishImmediately = false;
		}
		$documentManager = $event->getDocumentServices()->getDocumentManager();
		$transactionManager = $event->getApplicationServices()->getTransactionManager();
		try
		{
			$transactionManager->begin();

			if ($LCID)
			{
				if ($document->isNew())
				{
					throw new \RuntimeException('Invalid Parameter: LCID', 71000);
				}
				$documentManager->pushLCID($LCID);
				$this->doStartPublication($event, $document, $publishImmediately);
				$documentManager->popLCID();
			}
			else
			{
				$this->doStartPublication($event, $document, $publishImmediately);
			}

			$transactionManager->commit();
		}
		catch (\Exception $e)
		{
			throw $transactionManager->rollBack($e);
		}
	}

	/**
	 * @param \Change\Http\Event $event
	 * @param AbstractDocument $document
	 * @param boolean $publishImmediately
	 * @throws \Exception
	 */
	protected function doStartPublication($event, $document, $publishImmediately)
	{
		try
		{
			/* @var $document AbstractDocument|Correction */
			$correction = $document->startCorrectionPublication();
			if ($correction)
			{
				if ($publishImmediately && ($correction->getPublicationDate() <= new \DateTime()))
				{
					$document->publishCorrection();
				}

				$result = new ArrayResult();
				$result->setHttpStatusCode(HttpResponse::STATUS_CODE_200);
				$l = new DocumentLink($event->getUrlManager(), $document);
				$l->setRel('resource');
				$result->setArray(array('link' => $l->toArray(),
					'data' => array('correction-id' => $correction->getId(),
						'correction-status' => $correction->getStatus())));
				$event->setResult($result);
			}
		}
		catch (\Exception $e)
		{
			$code = $e->getCode();
			if ($code && $code == 55000)
			{
				$errorResult = new ErrorResult('PUBLICATION-ERROR', 'Invalid Publication status', HttpResponse::STATUS_CODE_409);
				$event->setResult($errorResult);
				return;
			}
			throw $e;
		}
	}
}
