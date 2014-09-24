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

    protected $fileCache;

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('cacheDir'));

        $application = new \Silex\Application();
        $application[\Phruts\Util\Globals::DIGESTER] = $application->share(function() {
                $digester = new Digester();
                $digester->addRuleSet(new ConfigRuleSet('phruts-config'));
                return $digester;
            });
        $this->fileCache = new FileCacheModuleProvider($application);
        $this->fileCache->setCachePath(vfsStream::url('cacheDir'));
    }

    public function testGetModuleConfig()
    {
        $moduleConfig = $this->fileCache->getModuleConfig('test', realpath(__DIR__ . '/../Resources/example-config.xml'));

        $this->assertNotEmpty($moduleConfig);
        $this->assertTrue($moduleConfig instanceof \Phruts\Config\ModuleConfig);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('phruts-test.data'));
        $this->assertTrue(count($moduleConfig->findActionConfigs()) > 0);

        $moduleConfig2 = $this->fileCache->getModuleConfig('test', realpath(__DIR__ . '/../Resources/example-config.xml'));
        $this->assertEquals($moduleConfig, $moduleConfig2);

        touch(realpath(__DIR__ . '/../Resources/example-config.xml'), strtotime('+10 minutes'));
        $moduleConfig3 = $this->fileCache->getModuleConfig('test', realpath(__DIR__ . '/../Resources/example-config.xml'));
        $this->assertEquals($moduleConfig, $moduleConfig3);
    }

    public function testGetMultipleModuleConfig()
    {
        /** @var \Phruts\Config\ModuleConfig $moduleConfig */
        $moduleConfig = $this->fileCache->getModuleConfig('test', realpath(__DIR__ . '/../Resources/example-config.xml') . ',' . realpath(__DIR__ . '/../Resources/module1-config.xml'));
        $this->assertNotEmpty($moduleConfig);

        $this->assertNotEmpty($moduleConfig->findActionConfig('/test'));
        $this->assertNotEmpty($moduleConfig->findActionConfig('/resourceA'));
    }
}