<?php
namespace Rbs\Theme\Setup;

/**
 * Class Install
 * @package Rbs\Theme\Setup
 * @name \Rbs\Theme\Setup\Install
 */
class Install
{

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 * @param \Change\Application $application
	 * @throws \RuntimeException
	 */
	public function executeApplication($plugin, $application)
	{
		/* @var $config \Change\Configuration\EditableConfiguration */
		$config = $application->getConfiguration();

		$config->addPersistentEntry('Change/Events/ListenerAggregateClasses/Rbs_Theme',
			'\\Rbs\\Theme\\Events\\SharedListenerAggregate');
		$config->addPersistentEntry('Rbs/Admin/Listeners/Rbs_Theme', '\\Rbs\\Theme\\Admin\\Register');
	}

	/**
	 * @param \Change\Plugins\Plugin $plugin
	 */
	public function finalize($plugin)
	{
		$plugin->setConfigurationEntry('locked', true);
	}
}