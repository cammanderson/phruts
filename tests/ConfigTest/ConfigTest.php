<?php
namespace ConfigTest;

use Phruts\Config\ControllerConfig;
use Phruts\Config\DataSourceConfig;
use Phruts\Config\ExceptionConfig;
use Phruts\Config\FormBeanConfig;
use Phruts\Config\FormPropertyConfig;
use Phruts\Config\ForwardConfig;
use Phruts\Config\MessageResourcesConfig;
use Phruts\Config\ModuleConfig;
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
        $dataSourceConfig->setKey('key');
        $dataSourceConfig->setType('\PDO');
        $dataSourceConfig->addProperty('key1', 'value1');
        $dataSourceConfig->addProperty('key2', 'value2');
        $expected = "\Phruts\Config\DataSourceConfig[key='key',type='\\\\PDO',key1='value1',key2='value2']";
        $this->assertEquals($expected, (string)$dataSourceConfig);

        // TODO: Test exception
        $dataSourceConfig->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $dataSourceConfig->setKey('expected');
    }

    public function testPlugInConfig()
    {
        $config = new PlugInConfig();
        $config->setKey('key');
        $config->setClassName('\MyClassName');
        $config->addProperty('key1', 'value1');
        $config->addProperty('key2', 'value2');
        $expected = "\Phruts\Config\PlugInConfig[className='\\\\MyClassName',key1='value1',key2='value2']";
        $this->assertEquals($expected, (string)$config);

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->addProperty('expected', true);
    }

    public function testFormPropertyConfig()
    {
        $config = new FormPropertyConfig();
        $config->setName('myName');
        $config->setType('int');
        $config->setInitial('2');
        $expected = "\Phruts\Config\FormPropertyConfig[name=myName,type=int,initial=2]";
        $this->assertEquals($expected, (string)$config);

        $this->assertEquals(2, $config->initial());

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setName('name');
    }

    public function testFormBeanConfig()
    {
        $fpConfig = new FormPropertyConfig();
        $fpConfig->setName('myName');
        $fpConfig->setType(FormPropertyConfig::TYPE_INTEGER);
        $fpConfig->setInitial('2');

        $config = new FormBeanConfig();
        $config->setType('\MyForm');
        $config->setName('name');
        $config->addFormPropertyConfig($fpConfig);
        $expected = "\Phruts\Config\FormBeanConfig[name='name',type='\\\\MyForm',properties=array (" . "\n" . "  0 => 'myName'," . "\n" .")]";
        $this->assertEquals($expected, (string)$config);

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setName('name');
    }

    public function testForwardConfig()
    {
        $config = new ForwardConfig();
        $config->setName('myName');
        $config->setPath('path.php');
        $config->setContextRelative('no');
        $config->setNextActionPath('myNextPath');
        $config->setRedirect('false');
        // TODO: Update the expected to include redirect, next action and context relative
        $expected = "\Phruts\Config\ForwardConfig[name='myName',path='path.php',redirect=false]";
        $this->assertEquals($expected, (string)$config);

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setName('name');
    }

    public function testMessageResourcesConfig()
    {
        $config = new MessageResourcesConfig();
        $config->setKey('myKey');
        $config->setNull('false');
        $config->setParameter('myParameter');
        $expected = "\Phruts\Config\MessageResourcesConfig[key='myKey',factory='\\\\Phruts\\\\Util\\\\PropertyMessageResourcesFactory',parameter='myParameter',null=false]";
        $this->assertEquals($expected, (string)$config);

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setKey('key');
    }

    public function testExceptionConfig()
    {
        $config = new ExceptionConfig();

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setHandler('\Handler');
    }

    public function testModuleConfig()
    {
        $config = new ModuleConfig('prefix');

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setPrefix('prefix2');



        $controllerConfig = new ControllerConfig();
        $controllerConfig->setProcessorClass('\Mock\Proccessor');
        $controllerConfig->setContentType('application/x-javascript');
        $controllerConfig->setNocache('true');
        $controllerConfig->setInputForward('true');
        $controllerConfig->setLocale('true');
        $expected = "\Phruts\Config\ControllerConfig[processorClass='\\\\Mock\\\\Proccessor',contentType='application/x-javascript',nocache=true,inputForward=true,locale=true]";
        $this->assertEquals($expected, (string)$controllerConfig);

        $config->setControllerConfig($controllerConfig);
        $this->assertNotEmpty($config->getControllerConfig());

    }
}
 