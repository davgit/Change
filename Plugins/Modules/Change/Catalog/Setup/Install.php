<?php
namespace Change\Catalog\Setup;

use Change\Plugins\PluginManager;


/**
 * @name \Change\Generic\Setup\Install
 */
class Install implements \Zend\EventManager\ListenerAggregateInterface
{
	/**
	 * @return string
	 */
	protected function getVendor()
	{
		return 'change';
	}

	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'catalog';
	}

	/**
	 * Attach one or more listeners
	 * Implementors may add an optional $priority argument; the EventManager
	 * implementation will pass this to the aggregate.
	 * @param \Zend\EventManager\EventManagerInterface $events
	 */
	public function attach(\Zend\EventManager\EventManagerInterface $events)
	{
		$vendor = $this->getVendor();
		$name = $this->getName();

		$callBack = function (\Zend\EventManager\Event $event) use ($vendor, $name)
		{
			/* @var $pluginManager PluginManager */
			$pluginManager = $event->getTarget();
			return $pluginManager->getModule($vendor, $name)->setPackage('core')->setConfigurationEntry('locked', true);
		};

		$eventNames = array(
			PluginManager::composeEventName(
				PluginManager::EVENT_SETUP_INITIALIZE, PluginManager::EVENT_TYPE_PACKAGE, $vendor, 'core'),
			PluginManager::composeEventName(
				PluginManager::EVENT_SETUP_INITIALIZE, PluginManager::EVENT_TYPE_MODULE, $vendor, $name)
		);
		$events->attach($eventNames, $callBack, 5);

		$callBack = function (\Zend\EventManager\Event $event) use ($vendor, $name)
		{
			/* @var $application \Change\Application */
			$application = $event->getParam('application');
			$this->executeApplication($application);

			/* @var $pluginManager PluginManager */
			$pluginManager = $event->getTarget();
			$pluginManager->getModule($vendor, $name)
				->setConfigurationEntry(PluginManager::EVENT_SETUP_APPLICATION, 'Ok');
		};
		$eventNames = array(
			PluginManager::composeEventName(
				PluginManager::EVENT_SETUP_APPLICATION, PluginManager::EVENT_TYPE_PACKAGE, $vendor, 'core'),
			PluginManager::composeEventName(
				PluginManager::EVENT_SETUP_APPLICATION, PluginManager::EVENT_TYPE_MODULE, $vendor, $name)
		);
		$events->attach($eventNames, $callBack, 5);

		$callBack = function (\Zend\EventManager\Event $event) use ($vendor, $name)
		{
			/* @var $documentServices \Change\Documents\DocumentServices */
			$documentServices = $event->getParam('documentServices');
			$this->executeServices($documentServices);
			/* @var $pluginManager PluginManager */
			$pluginManager = $event->getTarget();

			$pluginManager->getModule($vendor, $name)->setConfigurationEntry(PluginManager::EVENT_SETUP_SERVICES, 'Ok');
		};
		$eventNames = array(
			PluginManager::composeEventName(
				PluginManager::EVENT_SETUP_SERVICES, PluginManager::EVENT_TYPE_PACKAGE, $vendor, 'core'),
			PluginManager::composeEventName(
				PluginManager::EVENT_SETUP_SERVICES, PluginManager::EVENT_TYPE_MODULE, $vendor, $name)
		);
		$events->attach($eventNames, $callBack, 5);
	}

	/**
	 * Detach all previously attached listeners
	 * @param \Zend\EventManager\EventManagerInterface $events
	 */
	public function detach(\Zend\EventManager\EventManagerInterface $events)
	{
		// TODO: Implement detach() method.
	}

	/**
	 * @param \Change\Application $application
	 */
	public function executeApplication($application)
	{
		$application->getConfiguration()
			->addPersistentEntry('Change/Admin/Listeners/Change_Catalog', '\\Change\\Catalog\\Admin\\Register');
	}

	/**
	 * @param \Change\Documents\DocumentServices $documentServices
	 */
	protected function executeServices($documentServices)
	{
		$rootNode = $documentServices->getTreeManager()->getRootNode('Change_Catalog');
		if (!$rootNode)
		{
			/* @var $folder \Change\Generic\Documents\Folder */
			$folder = $documentServices->getDocumentManager()->getNewDocumentInstanceByModelName('Change_Generic_Folder');
			$folder->setLabel('Change_Catalog');
			$folder->create();
			$documentServices->getTreeManager()->insertRootNode($folder, 'Change_Catalog');
		}
	}
}