<?php
namespace Change\Collection;

/**
 * @name \Change\Collection\BaseItem
 */
class BaseItem implements ItemInterface
{
	/* @var $title string */
	protected $title;

	/* @var $title string */
	protected $value;

	/* @var $title string */
	protected $label;

	/**
	 * @param string $value
	 * @param string|array|\Change\I18n\I18nString $label
	 * @param string|\Change\I18n\I18nString $title
	 */
	function __construct($value, $label = null, $title = null)
	{
		$this->value = $value;
		if (\Zend\Stdlib\ArrayUtils::isList($label))
		{
			list($label, $title) = $label;
		}
		elseif (\Zend\Stdlib\ArrayUtils::isHashTable($label))
		{
			$title = isset($label['title']) ? $label['title'] : null;
			$label = isset($label['label']) ? $label['label'] : null;
		}
		$this->label = $label === null ? $this->value : $label;
		$this->title = $title === null ? $this->label : $title;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return strval($this->label);
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return strval($this->title);
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}