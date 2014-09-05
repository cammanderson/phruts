<?php

namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of
 * a <message-resources> element in a PHruts configuration file.
 *
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class MessageResourcesConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
	 * Freeze the configuration of this component.
	 */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
	 * Fully qualified PHP class name of the MessageResourcesFactory class
	 * we should use.
	 *
	 * @var string
	 */
    protected $factory = '\Phruts\Util\PropertyMessageResourcesFactory';

    /**
	 * @return string
	 */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
	 * @param string $factory
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setFactory($factory)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $this->factory = (string) $factory;
    }

    /**
	 * Parameter that is passed to the createResources method of our
	 * MessageResourcesFactory implementation.
	 *
	 * @var string
	 */
    protected $parameter = null;

    /**
	 * @return string
	 */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
	 * @param string $parameter
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setParameter($parameter)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $this->parameter = (string) $parameter;
    }

    /**
	 * The actionKernel context attributes key under which this MessageResources
	 * instance is stored.
	 *
	 * @var string
	 */
    protected $key = \Phruts\Globals::MESSAGES_KEY;

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
	 * Should we return null for unknown message keys?
	 *
	 * @var boolean
	 */
    protected $nullValue = true;

    /**
	 * @return boolean
	 */
    public function getNull()
    {
        return $this->nullValue;
    }

    /**
	 * @param boolean $nullValue
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setNull($nullValue)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $temp = strtolower($nullValue);
        if ($temp === 'false' || $temp === 'no') {
            $this->nullValue = false;
        } else {
            $this->nullValue = (boolean) $nullValue;
        }
    }

    /**
	 * Return a String representation of this object.
	 *
	 * @return string
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\MessageResourcesConfig[';
        $sb .= 'key=' . var_export($this->key, true);
        $sb .= ',factory=' . var_export($this->factory, true);
        $sb .= ',parameter=' . var_export($this->parameter, true);
        $sb .= ',null=' . var_export($this->nullValue, true);
        $sb .= ']';

        return $sb;
    }
}
