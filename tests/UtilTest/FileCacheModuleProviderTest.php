<?php
namespace UtilTest;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Phigester\Digester;
use Phruts\Config\ConfigRuleSet;
use Phruts\Util\ModuleProvider\FileCacheModuleProvider;

class FileCacheModuleProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('cacheDir'));
    }

    public function testGetModuleConfig()
    {
        $application = new \Silex\Application();
        $application[\Phruts\Util\Globals::DIGESTER] = $application->share(function() {
                $digester = new Digester();
                $digester->addRuleSet(new ConfigRuleSet('phruts-config'));
                return $digester;
            });
        $fileCache = new FileCacheModuleProvider($application);
        $fileCache->setCachePath(vfsStream::url('cacheDir'));

        $moduleConfig = $fileCache->getModuleConfig('test', realpath(__DIR__ . '/../ConfigTest/full-config.xml'));

        $this->assertNotEmpty($moduleConfig);
        $this->assertTrue($moduleConfig instanceof \Phruts\Config\ModuleConfig);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('phruts-test.data'));
        $this->assertTrue(count($moduleConfig->findActionConfigs()) > 0);

        $moduleConfig2 = $fileCache->getModuleConfig('test', realpath(__DIR__ . '/../ConfigTest/full-config.xml'));
        $this->assertEquals($moduleConfig, $moduleConfig2);

        touch(realpath(__DIR__ . '/../ConfigTest/full-config.xml'), strtotime('+10 minutes'));
        $moduleConfig3 = $fileCache->getModuleConfig('test', realpath(__DIR__ . '/../ConfigTest/full-config.xml'));
        $this->assertEquals($moduleConfig, $moduleConfig3);
    }
}