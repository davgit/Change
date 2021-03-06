<?php
namespace Rbs\Geo\Http\Web;

/**
 * @name \Rbs\Geo\Http\Web\GetCountriesByZoneCode
 */
class GetCountriesByZoneCode extends \Change\Http\Web\Actions\AbstractAjaxAction
{
	/**
	 * @param \Change\Http\Web\Event $event
	 * @return mixed
	 */
	public function execute(\Change\Http\Web\Event $event)
	{
		$request = $event->getRequest();
		$arguments = array_merge($request->getQuery()->toArray(), $request->getPost()->toArray());

		$compatibleCountries = array();
		$genericServices = $event->getServices('genericServices');
		if ($genericServices instanceof \Rbs\Generic\GenericServices)
		{
			$i18n = $event->getApplicationServices()->getI18nManager();
			$zoneCode = isset($arguments['zoneCode']) ? $arguments['zoneCode'] : null;
			foreach ($genericServices->getGeoManager()->getCountriesByZoneCode($zoneCode) as $country)
			{
				// Exclude countries without defined address model.
				if (!$country->getAddressFields())
				{
					continue;
				}

				$compatibleCountries[] = array(
					'id' => $country->getId(),
					'code' => $country->getCode(),
					'title' => $i18n->trans($country->getI18nTitleKey())
				);
			}
		}

		$result = $this->getNewAjaxResult($compatibleCountries);
		$event->setResult($result);
		return;
	}
}