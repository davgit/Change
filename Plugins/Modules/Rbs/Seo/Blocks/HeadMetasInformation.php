<?php
namespace Rbs\Seo\Blocks;

use Change\Presentation\Blocks\Information;

/**
 * @name \Rbs\Seo\Blocks\HeadMetasInformation
 */
class HeadMetasInformation extends Information
{
	public function onInformation(\Change\Events\Event $event)
	{
		parent::onInformation($event);
		$i18nManager = $event->getApplicationServices()->getI18nManager();
		$ucf = array('ucf');
		$this->setSection($i18nManager->trans('m.rbs.seo.admin.module_name', $ucf));
		$this->setLabel($i18nManager->trans('m.rbs.seo.admin.head_metas', $ucf));
	}
}
