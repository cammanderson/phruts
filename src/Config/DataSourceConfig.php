<?php

namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of a <data-source>
 * element from a PHruts configuration file.
 */
class DataSourceConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
	 * Freeze the configuration of this data source.
	 */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
	 * The actionKernel context attribute key under which this data source is stored
	 * and made available.
	 *
	 * @var string
	 */
    protected $key = \Phruts\Globals::DATA_SOURCE_KEY;

    /**
	 * @return string
	 */
    public function getKey()
    {
        return $this->key;
    }

    /**
	 * @param string $key
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setKey($key)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $this->key = (string) $key;
    }

    /**
	 * The custom configuration properties for this data source implementation.
	 *
	 * @var array
	 */
    protected $properties = array ();

    /**
	 * @return array
	 */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
	 * Add a new custom configuration property.
	 *
	 * @param string $name Custom property name
	 * @param string $value Custom property value
	 */
    public function addProperty($name, $value)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $name = (string) $name;
        $this->properties[$name] = (string) $value;
    }

    /**
	 * The factory class to create data source object.
	 *
	 * @var string
	 */
    protected $type = 'phruts::util::PDODataSourceFactory';

    /**
	 * @return string
	 */
    public function getType()
    {
        return $this->type;
    }

    /**
	 * @param string $type
	 */
    public function setType($type)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $this->type = (string) $type;
    }

    /**
	 * Return a string representation of this object.
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\DataSourceConfig[';
        $sb .= 'key=' . var_export($this->key, true);
        $sb .= ',type=' . var_export($this->type, true);
        foreach ($this->properties as $name => $value) {
            $sb .= ',' . $name . '=' . var_export($value, true);
        }
        $sb .= ']';

        return $sb;
    }
}
