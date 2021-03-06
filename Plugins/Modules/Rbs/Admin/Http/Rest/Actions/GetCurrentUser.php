<?php
namespace Rbs\Admin\Http\Rest\Actions;

use Change\Http\Rest\Result\DocumentResult;
use Zend\Http\Response as HttpResponse;

/**
 * @name \Rbs\Admin\Http\Rest\Actions\GetCurrentUser
 */
class GetCurrentUser
{

	/**
	 * TODO WWW-Authenticate: OAuth realm="Rbs_Admin"
	 * @param \Change\Http\Event $event
	 */
	public function execute($event)
	{

		$user = $event->getAuthenticationManager()->getCurrentUser();
		$properties = array(
			'id' => $user->getId(),
			'pseudonym' => $user->getName()
		);

		$result = new DocumentResult($event->getUrlManager(), $event->getApplicationServices()->getDocumentManager()->getDocumentInstance($user->getId()));
		$result->setProperties($properties);
		$result->setHttpStatusCode(HttpResponse::STATUS_CODE_200);


		$profileManager = $event->getApplicationServices()->getProfileManager();
		$props = array();
		$profile = $profileManager->loadProfile($user, 'Change_User');
		if ($profile)
		{
			foreach ($profile->getPropertyNames() as $name)
			{
				$props[$name] = $profile->getPropertyValue($name);
				if (!isset($props[$name]))
				{
					if ($name === 'LCID')
					{
						$props[$name] = $event->getApplicationServices()->getI18nManager()->getLCID();
					}
					elseif ($name === 'TimeZone')
					{
						$tz =  $event->getApplicationServices()->getI18nManager()->getTimeZone();
						$props[$name] = $tz->getName();
					}
				}
			}
		}

		$profile = $profileManager->loadProfile($user, 'Rbs_Admin');
		if ($profile)
		{
			foreach ($profile->getPropertyNames() as $name)
			{
				$props[$name] = $profile->getPropertyValue($name);
			}
		}
		$result->setProperty('profile', $props);
		$event->setResult($result);
	}
}