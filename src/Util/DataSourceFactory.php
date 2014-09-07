<?php

namespace Phruts\Util;

/**
 * Factory for data source instances.
 *
 * The general usage pattern for this class is:
 * <ul>
 * <li>Call <samp>createFactory</samp> to retrieve
 * a DataSourceFactory instance.</li>
 * <li>Call the <samp>createDataSource</samp> method of the factory to retrieve
 * a newly instantiated data source instance.</li>
 * </ul>
 *
 */
abstract class DataSourceFactory
{
    /**
	 * The fully qualified class name to be used for DataSourceFactory
	 * instances.
	 *
	 * @var string
	 */
    protected static $factoryClass = '\Phruts\Util\PDODataSourceFactory';

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
	 * Data source configuration.
	 *
	 * @var \Phruts\Config\DataSourceConfig
	 */
    protected $config = null;

    /**
	 * @return \Phruts\Config\DataSourceConfig
	 */
    public function getConfig()
    {
        return $this->config;
    }

    /**
	 * @param \Phruts\Config\DataSourceConfig $config
	 */
    public function setConfig(\Phruts\Config\DataSourceConfig $config)
    {
        $this->config = $config;
    }

    /**
	 * Create and return a DataSourceFactory instance of the appropriate
	 * class, which can be used to create customized data source instances.
	 *
	 * @param \Phruts\Config\DataSourceConfig $config
	 * @return DataSourceFactory
	 */
    public static function createFactory(\Phruts\Config\DataSourceConfig $config)
    {
        try {
            $factory = \Phruts\ClassLoader::newInstance(self::$factoryClass, '\Phruts\Util\DataSourceFactory');

            $factory->setConfig($config);

            return $factory;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
	 * Create a data source object.
	 *
	 * @return object
	 * @throws \Exception
	 */
    abstract public function createDataSource();
}
