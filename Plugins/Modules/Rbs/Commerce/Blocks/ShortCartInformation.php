<?php
namespace Rbs\Commerce\Blocks;

use Change\Presentation\Blocks\Information;

/**
 * @name \Rbs\Commerce\Blocks\CartInformation
 */
class ShortCartInformation extends Information
{
	/**
	 * @param \Change\Events\Event $event
	 */
	public function onInformation(\Change\Events\Event $event)
	{
		parent::onInformation($event);
		$i18nManager = $event->getApplicationServices()->getI18nManager();
		$ucf = array('ucf');
		$this->setSection($i18nManager->trans('m.rbs.commerce.admin.module_name', $ucf));
		$this->setLabel($i18nManager->trans('m.rbs.commerce.admin.shortcart_label', $ucf));
	}
}
