<?php
namespace Rbs\Commerce\Events\BlockManager;

use Change\Presentation\Blocks\Standard\RegisterByBlockName;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * @name \Rbs\Commerce\Events\BlockManager\Listeners
 */
class Listeners implements ListenerAggregateInterface
{
	/**
	 * Attach one or more listeners
	 * Implementors may add an optional $priority argument; the EventManager
	 * implementation will pass this to the aggregate.
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function attach(EventManagerInterface $events)
	{
		new RegisterByBlockName('Rbs_Catalog_ProductList', true, $events);
		new RegisterByBlockName('Rbs_Catalog_Product', true, $events);
		new RegisterByBlockName('Rbs_Catalog_CrossSelling', true, $events);
		new RegisterByBlockName('Rbs_Catalog_CartCrossSelling', true, $events);
		new RegisterByBlockName('Rbs_Commerce_Cart', true, $events);
		new RegisterByBlockName('Rbs_Commerce_ShortCart', true, $events);
		new RegisterByBlockName('Rbs_Commerce_OrderProcess', true, $events);
		new RegisterByBlockName('Rbs_Store_WebStoreSelector', true, $events);
	}

	/**
	 * Detach all previously attached listeners
	 * @param EventManagerInterface $events
	 * @return void
	 */
	public function detach(EventManagerInterface $events)
	{
		// TODO: Implement detach() method.
	}
}
