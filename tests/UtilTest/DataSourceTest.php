<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */

class DataSourceTest  extends \PHPUnit_Framework_TestCase
{

    public function testInstantiate()
    {
        // Test the factory
        $this->assertEquals('\Phruts\Util\PDODataSourceFactory', \Phruts\Util\DataSourceFactory::getFactoryClass());

        $config = new \Phruts\Config\DataSourceConfig();
        $config->setKey('Example');
        $config->setType('PDO');
        $config->addProperty('dsn', 'sqlite::memory:');

        $factory = \Phruts\Util\DataSourceFactory::createFactory($config);
        $this->assertNotEmpty($factory);

        // Create the resources
        $pdo = $factory->createDataSource();
        $this->assertNotEmpty($pdo);
    }

}
 