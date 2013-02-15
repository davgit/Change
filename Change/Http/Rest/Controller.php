<?php
namespace Change\Http\Rest;

use Zend\Http\Response as HttpResponse;

/**
 * @name \Change\Http\Rest\Controller
 */
class Controller extends \Change\Http\Controller
{

	/**
	 * @param \Zend\EventManager\EventManager $eventManager
	 * @return void
	 */
	protected function registerDefaultListeners($eventManager)
	{
		$eventManager->attach(\Change\Http\Event::EVENT_EXCEPTION, array($this, 'onException'));
		$eventManager->attach(\Change\Http\Event::EVENT_RESPONSE, array($this, 'onDefaultJsonResponse'));
	}

	/**
	 * @param $request
	 * @return \Change\Http\Event
	 */
	protected function createEvent($request)
	{
		$event = parent::createEvent($request);
		$event->setApplicationServices(new \Change\Application\ApplicationServices($this->getApplication()));
		$event->setDocumentServices(new \Change\Documents\DocumentServices($event->getApplicationServices()));
		return $event;
	}

	/**
	 * @api
	 * @return \Zend\Http\PhpEnvironment\Response
	 */
	public function createResponse()
	{
		$response = parent::createResponse();
		$response->getHeaders()->addHeaderLine('Content-Type: application/json');
		return $response;
	}


	/**
	 * @param \Change\Http\Event $event
	 * @return \Zend\Http\PhpEnvironment\Response
	 */
	protected function getDefaultResponse($event)
	{
		$response = $this->createResponse();
		$response->setStatusCode(HttpResponse::STATUS_CODE_500);
		$content = array('code' => 'ERROR-GENERIC', 'message' => 'Generic error');
		$response->setContent(json_encode($content));
		return $response;
	}

	/**
	 * @param \Change\Http\Event $event
	 */
	public function onException($event)
	{
		if (!($event->getResult() instanceof ErrorResult))
		{
			/* @var $exception \Exception */
			$error = $this->generateErrorException($event->getParam('Exception'));
			if ($event->getResult() instanceof \Change\Http\Result)
			{
				$result = $event->getResult();
				$error->setHttpStatusCode($result->getHttpStatusCode());
				if ($result->getHttpStatusCode() === HttpResponse::STATUS_CODE_404)
				{
					$error->setErrorCode('NOT_FOUND');
					$error->setErrorMessage($event->getRequest()->getPath());
				}
			}

			$event->setResult($error);
			$event->setResponse(null);
		}
	}

	/**
	 * @param \Exception $exception
	 * @return \Change\Http\Rest\ErrorResult
	 */
	protected function generateErrorException(\Exception $exception)
	{
		$code = $exception->getCode() ? 'EXCEPTION-' . $exception->getCode() : 'EXCEPTION';
		return new ErrorResult($code, $exception->getMessage());
	}

	/**
	 * @param \Change\Http\Event $event
	 */
	public function onDefaultJsonResponse($event)
	{
		$result = $event->getResult();
		if ($result instanceof \Change\Http\Result)
		{
			$response = $event->getController()->createResponse();
			$response->setStatusCode($result->getHttpStatusCode());
			$event->setResponse($response);

			$callable = array($result, 'toArray');
			if (is_callable($callable))
			{
				$data = call_user_func($callable);
				$response->setContent(json_encode($data));
			}
			elseif ($result->getHttpStatusCode() === HttpResponse::STATUS_CODE_404)
			{
				$error = new ErrorResult('NOT_FOUND', $event->getRequest()->getPath());
				$response->setContent(json_encode($error->toArray()));
			}
		}
	}
}