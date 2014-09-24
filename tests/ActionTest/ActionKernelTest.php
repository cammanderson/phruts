<?php
namespace ActionTest;

use Phruts\Action\ActionKernel;
use Phruts\Provider\PhrutsServiceProvider;
use Phruts\Util\ModuleProvider\FileCacheModuleProvider;
use Phruts\Util\RequestUtils;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ActionKernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phruts\Action\ActionKernel
     */
    protected $actionKernel;
    /**
     * @var \Silex\Application
     */
    protected $application;

    public function setUp()
    {
//        $this->application = new \Silex\Application;
        $this->application = $this->getMock('\Silex\Application', array('handle'));
        $this->application->expects($this->any())
            ->method('handle')
            ->willReturn(new Response());

        $this->application[\Phruts\Util\Globals::ACTION_KERNEL_CONFIG] = array(
            'config' => realpath(__DIR__ . '/../Resources/example-config.xml'),
            'config/admin' => realpath(__DIR__ . '/../Resources/example-config.xml'),
        );
        $serviceProvider = new PhrutsServiceProvider();
        $serviceProvider->register($this->application);
        $this->actionKernel = $this->application[\Phruts\Util\Globals::ACTION_KERNEL];

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('cacheDir'));
    }

    public function testInstantiate()
    {
        $this->assertNotEmpty($this->actionKernel);
        $this->assertTrue($this->actionKernel instanceof \Phruts\Action\ActionKernel);
    }

    public function testAccessorMutators()
    {
        $this->assertNotEmpty($this->actionKernel->getApplication());
    }

    public function testHandle()
    {
        $request = Request::create('http://localhost/test', 'GET', array(), array(), array(), array('PATH_INFO' => '/test'));

        $fileCache = new FileCacheModuleProvider($this->application);
        $fileCache->setCachePath(vfsStream::url('cacheDir'));
        $this->application[\Phruts\Util\Globals::MODULE_CONFIG_PROVIDER] = $fileCache;

        $response = $this->actionKernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

//        $request = Request::create('http://localhost/admin/test', 'GET', array(), array(), array(), array('PATH_INFO' => '/admin/test'));
//        RequestUtils::selectModule($request, $this->application);
//        $response = $this->actionKernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
//        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }
}
 