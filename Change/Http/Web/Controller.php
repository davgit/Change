<?php
namespace Change\Http\Web;

use Change\Application\ApplicationServices;
use Change\Documents\DocumentServices;
use Change\Presentation\PresentationServices;
use Change\Http\Result;
use Zend\Http\PhpEnvironment\Response;
use Zend\Http\Response as HttpResponse;

/**
 * @name \Change\Http\Web\Controller
 */
class Controller extends \Change\Http\Controller
{
	/**
	 * @return string[]
	 */
	protected function getEventManagerIdentifier()
	{
		return array('Http', 'Http.Web');
	}

	/**
	 * @param \Zend\EventManager\EventManager $eventManager
	 */
	protected function attachEvents(\Zend\EventManager\EventManager $eventManager)
	{
		parent::attachEvents($eventManager);
		$callback = function ($event)
		{
			$composer = new \Change\Http\Web\Events\ComposePage();
			$composer->execute($event);
		};
		$eventManager->attach(Event::EVENT_RESULT, $callback, 5);
		$eventManager->attach(Event::EVENT_RESPONSE, array($this, 'onDefaultResponse'), 5);
	}

	/**
	 * @param $request
	 * @return Event
	 */
	protected function createEvent($request)
	{
		$event = new Event();
		$event->setRequest($request);
		$script = $request->getServer('SCRIPT_NAME');
		if (strpos($request->getRequestUri(), $script) !== 0)
		{
			$script = null;
		}
		$applicationServices = new ApplicationServices($this->getApplication());
		$event->setApplicationServices($applicationServices);

		$urlManager = new UrlManager($request->getUri(), $script);
		$urlManager->setApplicationServices($applicationServices);
		$event->setUrlManager($urlManager);

		$event->setDocumentServices(new DocumentServices($applicationServices));
		$urlManager->setDocumentServices($event->getDocumentServices());

		$event->setPresentationServices(new PresentationServices($applicationServices));

		$authenticationManager = new \Change\User\AuthenticationManager();
		$authenticationManager->setDocumentServices($event->getDocumentServices());
		$event->setAuthenticationManager($authenticationManager);

		$permissionsManager = new \Change\Permissions\PermissionsManager();
		$permissionsManager->allow(true);
		$permissionsManager->setApplicationServices($applicationServices);

		$event->setPermissionsManager($permissionsManager);
		return $event;
	}

	/**
	 * @param Event $event
	 */
	public function onDefaultResponse($event)
	{
		$result = $event->getResult();
		if ($result instanceof Result)
		{
			$response = $event->getController()->createResponse();
			$response->setStatusCode($result->getHttpStatusCode());
			$response->getHeaders()->addHeaders($result->getHeaders());

			if ($result instanceof \Change\Http\Web\Result\Page)
			{
				$acceptHeader = $event->getRequest()->getHeader('Accept');
				if ($acceptHeader instanceof \Zend\Http\Header\Accept && $acceptHeader->hasMediaType('text/html'))
				{
					$response->setContent($result->toHtml());
				}
			}
			elseif ($result instanceof \Change\Http\Web\Result\Resource)
			{
				$response->setContent($result->getContent());
			}
			elseif ($result instanceof \Change\Http\Web\Result\AjaxResult)
			{
				$response->setContent(\Zend\Json\Json::encode($result->toArray()));
			}
			else
			{
				$callable = array($result, 'toHtml');
				if (is_callable($callable))
				{
					$response->setContent(call_user_func($callable));
				}
				else
				{
					$response->setContent(strval($result));
				}
			}
			$event->setResponse($response);
		}
	}

	/**
	 * @param Event $event
	 * @return Result
	 */
	public function notFound($event)
	{
		$page = new \Change\Presentation\Themes\DefaultPage($event->getPresentationServices()->getThemeManager(), 'error404');
		$event->setParam('page', $page);
		$this->doSendResult($this->getEventManager(), $event);
		$result = $event->getResult();
		if ($result !== null)
		{
			$result->setHttpStatusCode(HttpResponse::STATUS_CODE_404);
			return $result;
		}

		return parent::notFound($event);
	}

	/**
	 * @api
	 * @param Event $event
	 * @return Result
	 */
	public function error($event)
	{
		$page = new \Change\Presentation\Themes\DefaultPage($event->getPresentationServices()->getThemeManager(), 'error500');
		$event->setParam('page', $page);
		$this->doSendResult($this->getEventManager(), $event);
		$result = $event->getResult();
		if ($result !== null)
		{
			$result->setHttpStatusCode(HttpResponse::STATUS_CODE_500);
			return $result;
		}
		return parent::error($event);
	}

	/**
	 * @param Event $event
	 * @return Response
	 */
	protected function getDefaultResponse($event)
	{
		$result = $this->error($event);
		$event->setResult($result);
		$this->onDefaultResponse($event);
		return $event->getResponse();
	}
}