<?php
namespace ConfigTest;

use Phruts\Action;
use Phruts\Config\ActionConfig;
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
        $config->setType('\Exception');
        $config->setKey('key1');
        $config->setPath('exception');
        $config->setScope('session');
        $config->setBundle('mybundle');
        $config->setHandler('\Phruts\Action\ExceptionHandler');
        $expected = "\Phruts\Config\ExceptionConfig[type=\\Exception,bundle=mybundle,key=key1,path=exception,scope=session]";
        $this->assertEquals($expected, (string)$config);

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setHandler('\Handler');
    }

    public function testActionConfig()
    {

        $moduleConfig = new ModuleConfig('prefix');

        $globalForward = new ForwardConfig();
        $globalForward->setPath("login.html.twig");
        $globalForward->setName('login');
        $moduleConfig->addForwardConfig($globalForward);

        $config = new ActionConfig();
        $config->setType('\MyAction');
        $this->assertEquals('\MyAction', $config->getType());
        $config->setScope('session');
        $this->assertEquals('session', $config->getScope());
        $config->setScope('request');
        $config->setName('myForm');
        $this->assertEquals('myForm', $config->getName());
        $config->setPath('mypath');
        $this->assertEquals('/mypath', $config->getPath());
        $config->setInput('form.php');
        $this->assertEquals('form.php', $config->getInput());
        $config->setPrefix('prefix');
        $this->assertEquals('prefix', $config->getPrefix());
        $config->setRoles("role1,role2,role3");
        $this->assertEquals('role1,role2,role3', $config->getRoles());
        $this->assertEquals(array('role1', 'role2', 'role3'), $config->getRoleNames());
        $config->setAttribute('attribute');
        $config->setModuleConfig($moduleConfig);

        // Test our sets
        $expected = "\Phruts\Config\ActionConfig[path='/mypath',type='\\\\MyAction',name='myForm',scope='request',attribute='attribute',prefix='prefix',validate=true,input='form.php',roles='role1,role2,role3',unknown=false]";
        $this->assertEquals($expected, (string)$config);

        // Test we can find the forward config
        $forwardConfig1 = new ForwardConfig();
        $forwardConfig1->setName('success');
        $forwardConfig1->setPath('mypath.html.twig');
        $config->addForwardConfig($forwardConfig1);
        $this->assertNotEmpty($config->findForwardConfig('success'));

        $config->removeForwardConfig($forwardConfig1);
        $this->assertEmpty($config->findForwardConfig('success'));
        $this->assertNotEmpty($config->findForwardConfigs());
        $this->assertNotEmpty($config->findForwardConfig('login'));

        $config->setParameter('myparam');
        $this->assertEquals('myparam', $config->getParameter());

        $exceptionConfig = new ExceptionConfig();
        $exceptionConfig->setType('\Exception');
        $exceptionConfig->setPath('exception.html.twig');
        $config->addExceptionConfig($exceptionConfig);
        $this->assertNotEmpty($config->findExceptionConfig('\Exception'));
        $this->assertEmpty($config->findExceptionConfig('\MyOtherException'));
        $config->removeExceptionConfig($exceptionConfig);
        $this->assertEmpty($config->findExceptionConfig('\Exception'));

        $this->assertTrue($config->getValidate());
        $config->setValidate('no');
        $this->assertNotTrue($config->getValidate());
        $config->setValidate('yes');
        $this->assertTrue($config->getValidate());
        $config->setValidate('false');
        $this->assertNotTrue($config->getValidate());
        $config->setValidate('true');
        $this->assertTrue($config->getValidate());

        $this->assertNotTrue($config->getUnknown());
        $config->setUnknown('yes');
        $this->assertTrue($config->getUnknown());
        $config->setUnknown('false');
        $this->assertNotTrue($config->getUnknown());
        $config->setUnknown('true');
        $this->assertTrue($config->getUnknown());
        $config->setUnknown('no');
        $this->assertNotTrue($config->getUnknown());

        $config->setPath('path');
        $this->assertEquals('/path', $config->getPath());

        // TODO: Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setType('\MyOtherActionActually');
    }

    public function testModuleConfig()
    {
        $config = new ModuleConfig('prefix');
        $this->assertEquals('prefix', $config->getPrefix());


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

        $actionConfig1 = new ActionConfig();
        $actionConfig1->setPath('action1');
        $actionConfig1->setType('\ForwardConfig');
        $config->addActionConfig($actionConfig1);

        $actionConfig2 = new ActionConfig();
        $actionConfig2->setPath('action2');
        $actionConfig2->setType('\ForwardConfig');
        $config->addActionConfig($actionConfig2);

        $this->assertNotEmpty($config->findActionConfig('/action1'));
        $this->assertNotEmpty($config->findActionConfig('/action2'));

        $actionClass = '\MyActionConfigClass';
        $config->setActionClass($actionClass);
        $this->assertEquals('\MyActionConfigClass', $config->getActionClass());

        $formBeanConfig1 = new FormBeanConfig();
        $formBeanConfig1->setName('myForm1');
        $formBeanConfig1->setType('\MyForm1');
        $formBeanConfig2 = new FormBeanConfig();
        $formBeanConfig2->setName('myForm2');
        $formBeanConfig2->setType('\MyForm2');

        $config->addFormBeanConfig($formBeanConfig1);
        $config->addFormBeanConfig($formBeanConfig2);

        $this->assertEquals(2, count($config->findFormBeanConfigs()));
        $this->assertEquals($formBeanConfig1, $config->findFormBeanConfig('myForm1'));
        $this->assertEquals($formBeanConfig2, $config->findFormBeanConfig('myForm2'));

        $forwardConfig1 = new ForwardConfig();
        $forwardConfig1->setName('welcome');
        $forwardConfig1->setPath('welcome.html.twig');
        $forwardConfig2 = new ForwardConfig();
        $forwardConfig2->setName('login');
        $forwardConfig1->setPath('login.html.twig');
        $config->addForwardConfig($forwardConfig1);
        $config->addForwardConfig($forwardConfig2);
        $this->assertEquals($forwardConfig1, $config->findForwardConfig('welcome'));
        $this->assertEquals($forwardConfig2, $config->findForwardConfig('login'));
        $config->removeForwardConfig($forwardConfig1);
        $this->assertEmpty($config->findForwardConfig('welcome'));

        $dataSourceConfig = new DataSourceConfig();
        $dataSourceConfig->setKey('key1');
        $config->addDataSourceConfig($dataSourceConfig);
        $dataSourceConfig2 = new DataSourceConfig();
        $dataSourceConfig2->setKey('key2');
        $config->addDataSourceConfig($dataSourceConfig2);
        $this->assertEquals(2, count($config->findDataSourceConfigs()));
        $this->assertEquals($dataSourceConfig, $config->findDataSourceConfig('key1'));
        $this->assertEquals($dataSourceConfig2, $config->findDataSourceConfig('key2'));
        $config->removeDataSourceConfig($dataSourceConfig);
        $this->assertEmpty($config->findDataSourceConfig('key1'));

        $messageConfig = new MessageResourcesConfig();
        $messageConfig->setKey('key1');
        $config->addMessageResourcesConfig($messageConfig);
        $this->assertEquals($messageConfig, $config->findMessageResourcesConfig('key1'));
        $this->assertEquals(1, count($config->findMessageResourcesConfigs()));

        // Test exception
        $config->freeze();
        $this->setExpectedException('\Phruts\Exception\IllegalStateException');
        $config->setPrefix('prefix2');

    }
}
 