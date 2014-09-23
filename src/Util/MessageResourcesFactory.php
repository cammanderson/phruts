<?php

namespace Phruts\Util;

/**
 * Factory for MessagesResources instances.
 *
 * The general usage pattern for this class is:
 * <ul>
 * <li>Call <samp>createFactory</samp> to retrieve
 * a MessageResourcesFactory instance.</li>
 * <li>Set properties as required to configure this factory instance to create
 * MessageResources instances with desired characteristics.</li>
 * <li>Call the <samp>createResources</samp> method of the factory to retrieve
 * a newly instantiated MessageResources instance.</li>
 * </ul>
 */
abstract class MessageResourcesFactory
{
    /**
	 * The fully qualified class name to be used for
	 * MessageResourcesFactory instances.
	 *
	 * @var string
	 */
    protected static $factoryClass = '\Phruts\Util\PropertyMessageResourcesFactory';

    /**
	 * @return string
	 */
    public static function getFactoryClass()
    {
        return self::$factoryClass;
    }

    /**
	 * @param string $factoryClass
	 */
    public static function setFactoryClass($factoryClass)
    {
        self::$factoryClass = (string) $factoryClass;
    }

    /**
	 * Create and return a MessageResourcesFactory instance of the
	 * appropriate class, which can be used to create customized
	 * MessageResources instances.
	 *
	 * If no such factory can be created, return null instead.
	 *
	 * @return MessageResourcesFactory
	 */
    public static function createFactory()
    {
        try {
            $factory = \Phruts\Util\ClassLoader::newInstance(self::$factoryClass, '\Phruts\Util\MessageResourcesFactory');

            return $factory;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
	 * The "return null" property value to which newly created
	 * MessageResources should be initialized.
	 *
	 * @var boolean
	 */
    protected $returnNull = true;

    /**
	 * @return boolean
	 */
    public function getReturnNull()
    {
        return $this->returnNull;
    }

    /**
	 * @param boolean $returnNull
	 */
    public function setReturnNull($returnNull)
    {
        $this->returnNull = (boolean) $returnNull;
    }

    /**
	 * Create an return a newly instansiated MessageResources.
	 *
	 * This method must be implemented by concrete subclasses.
	 *
	 * @param string $config Configuration parameter(s) for the requested bundle
	 * @return MessageResources
	 */
    abstract public function createResources($config);
}
