<?php
namespace Rbs\geo\Documents;

/**
 * @name \Rbs\geo\Documents\Address
 */
class Address extends \Compilation\Rbs\Geo\Documents\Address implements \Rbs\Geo\Interfaces\Address
{
	/**
	 * @return string|null
	 */
	public function getCountryCode()
	{
		return ($this->getCountry()) ? $this->getCountry()->getCode() : null;
	}

	/**
	 * @return string[]
	 */
	public function getLines()
	{
		return array();
	}
}
