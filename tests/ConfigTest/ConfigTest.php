<?php
namespace ConfigTest;

use Phruts\Config\ControllerConfig;
use Phruts\Config\DataSourceConfig;
use Phruts\Config\FormBeanConfig;
use Phruts\Config\ForwardConfig;
use Phruts\Config\PlugInConfig;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function testContollerConfig()
    {
        $controllerConfig = new ControllerConfig();
        $controllerConfig->setProcessorClass('\Mock\Proccessor');
        $controllerConfig->setContentType('application/x-javascript');
        $controllerConfig->setNocache('true');
        $controllerConfig->setInputForward('true');
        $controllerConfig->setLocale('true');
        $expected = "\Phruts\Config\ControllerConfig[processorClass='\\\\Mock\\\\Proccessor',contentType='application/x-javascript',nocache=true,inputForward=true,locale=true]";
        $this->assertEquals($expected, (string)$controllerConfig);

        $controllerConfig = new ControllerConfig();
        $controllerConfig->setProcessorClass('\Mock\Proccessor');
        $controllerConfig->setContentType('application/x-javascript');
        $controllerConfig->setNocache('false');
        $controllerConfig->setInputForward('no');
        $controllerConfig->setLocale('no');
        $expected = "\Phruts\Config\ControllerConfig[processorClass='\\\\Mock\\\\Proccessor',contentType='application/x-javascript',nocache=false,inputForward=false,locale=false]";
        $this->assertEquals($expected, (string)$controllerConfig);

        // Test freeze
        // TODO: test the other properties
        $controllerConfig->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $controllerConfig->setNocache(false);
    }

    public function testDataSourceConfig()
    {
        $dataSourceConfig = new DataSourceConfig();

        // TODO: Test exception
        $dataSourceConfig->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $dataSourceConfig->setKey('expected');
    }

    public function testPlugInConfig()
    {
        $config = new PlugInConfig();

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->addProperty('expected', true);
    }

    public function testFormBeanConfig()
    {
        $config = new FormBeanConfig();

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setName('name');
    }

    public function testForwardConfig()
    {
        $config = new ForwardConfig();

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setName('name');
    }
}
 