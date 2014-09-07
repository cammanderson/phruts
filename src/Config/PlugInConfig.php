<?php

namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of a <plug-in> element
 * in a PHruts configuration file.
 */
class PlugInConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
	 * A map of the name-value pairs that will be used to configure the property
	 * values of a PlugIn instance.
	 *
	 * @var array
	 */
    protected $properties = array ();

    /**
	 * The fully qualified PHP class name of the PlugIn implementation
	 * class being configured.
	 *
	 * @var string
	 */
    protected $className = null;

    protected $key = null;

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
	 * @return string
	 */
    public function getClassName()
    {
        return $this->className;
    }

    /**
	 * @param string $className
	 */
    public function setClassName($className)
    {
        $this->className = (string) $className;
    }

    /**
	 * Add a new property name and value to the set that will be used to configure
	 * the PlugIn instance.
	 *
	 * @param string $name Property name
	 * @param string $value Property value
	 * @throws \Phruts\Exception\IllegalStateException
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
	 * Freeze the configuration of this component.
	 */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
	 * Return the properties that will be used to configure a PlugIn
	 * instance.
	 *
	 * @return array
	 */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
	 * Return a string representation of this object.
	 *
	 * @return string
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\PlugInConfig[';
        $sb .= 'className=' . var_export($this->className, true);
        foreach ($this->properties as $name => $value) {
            $sb .= ',' . $name . '=' . var_export($value, true);
        }
        $sb .= ']';

        return $sb;
    }
}
