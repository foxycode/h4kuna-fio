<?php

namespace h4kuna\Fio\Response\Read;

use h4kuna\Fio\Utils;

/**
 * @author Milan Matějček
 */
abstract class ATransaction implements \Iterator
{

	/** @var array */
	private $properties = [];

	/** @var Utils\Date\DateFormat */
	protected $dateFormat;

	public function __construct(Utils\Date\DateFormat $dateFormat)
	{
		$this->dateFormat = $dateFormat;
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->properties)) {
			return $this->properties[$name];
		}
		throw new Utils\FioException('Property does not exists. ' . $name);
	}

	public function bindProperty($name, $type, $value)
	{
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$value = $this->{$method}($value);
		} elseif ($value !== NULL) {
			$value = $this->checkValue($value, $type);
		}
		$this->properties[$name] = $value;
	}

	public function current()
	{
		return current($this->properties);
	}

	public function key()
	{
		return key($this->properties);
	}

	public function next()
	{
		next($this->properties);
	}

	public function rewind()
	{
		reset($this->properties);
	}

	public function valid()
	{
		return array_key_exists($this->key(), $this->properties);
	}

	/** @return array */
	public function getProperties()
	{
		return $this->properties;
	}

	protected function checkValue($value, $type)
	{
		switch ($type) {

			case 'datetime':
				return $this->dateFormat->createDateTime($value);
			case 'float':
				return floatval($value);
			case 'string':
			case 'int': // on 32bit platform works inval() bad with variable symbol
				return trim($value);
			case 'string|null':
				return trim($value) ? : NULL;
		}
		return $value;
	}

}