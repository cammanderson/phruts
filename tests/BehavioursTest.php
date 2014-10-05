<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use Phruts\Util\ModuleProvider\FileCacheModuleProvider;

class BehavioursTest extends \Silex\WebTestCase
{

    public function testConfigPaths()
    {
        $client = $this->createClient();
        $client->followRedirects(true);

        // Welcome path
        $crawler = $client->request('GET', '');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome")'));

        $crawler = $client->request('GET', '/resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        $crawler = $client->request('GET', '/resourceB');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        $crawler = $client->request('GET', '/resourceC');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome")'));

        // Test paths as queries (matched by routes)
        $crawler = $client->request('GET', '/?do=resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        $crawler = $client->request('GET', '/forwardA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        // Test matched by extenions
        $crawler = $client->request('GET', '/my/resourceA.do');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        // Test the module
        $crawler = $client->request('GET', '/moduleA/resourceD');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        $crawler = $client->request('GET', '/moduleA/resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        // Test merging of configs
        $crawler = $client->request('GET', '/moduleB/resourceA');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));

        $crawler = $client->request('GET', '/moduleB/resourceB');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 2")'));

        $crawler = $client->request('GET', '/moduleB/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome")'));

        $crawler = $client->request('GET', '/moduleB/resourceE');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Resource 1")'));
    }


    public function testActionForms()
    {
        // Test that the 'reset' function is called first
    }

    public function testActionFormValidate()
    {
        // Test that we can implement a validate that will then bail to an input
    }

    public function testExceptionHandler()
    {
        // Test that we can define an exception handler to handle the exception nicely
    }

    public function testFormBeanProperties()
    {
        // Test that we can configure form beans
    }


    public function testCustomRequestProcessor()
    {
        // Test that we can implement our own request processor per module
    }

    public function testPlugin()
    {
        // Test that our plugins are called, and are accessible from the 'application' container
    }

    public function testDataSource()
    {
        // Test that we can run our datasources
    }

    public function testRoles()
    {
        // Test a role on the security framework
    }

    public function testActionMessages()
    {
        // Test we can access action messages
    }

    public function testMessageResources()
    {
        // Test that we can access message resources from the config
    }

    public function testCustomActionConfig()
    {
        // Test that properties can be set from the action config
    }

    public function testTwigExtensions()
    {
        // Test that our twig extensions can be used
    }

    public function createApplication()
    {
        // Create a silex application
        $app = new Silex\Application();

        // Configure test environments
        $app['debug'] = true;
        $app['exception_handler']->disable();
        $app['session.test'] = true;

        // Add in phruts to organise your controllers
        $app->register(new Phruts\Provider\PhrutsServiceProvider(), array(
                // Register our modules and configs
                Phruts\Util\Globals::ACTION_KERNEL_CONFIG => array(
                    // Default module
                    'config' => __DIR__  . '/Resources/module1-config.xml',

                    // A module (single config)
                    'config/moduleA' => __DIR__ . '/Resources/module2-config.xml',

                    // B module (merged config where module1 prevails)
                    'config/moduleB' => __DIR__ . '/Resources/module2-config.xml,' .
                        __DIR__ . '/Resources/module1-config.xml'
                )
            ));

        // Setup the mock file system for caching
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('cacheDir'));
        $app[Phruts\Util\Globals::MODULE_CONFIG_PROVIDER] = $app->share(function () use ($app) {
                $provider = new FileCacheModuleProvider($app);
                $provider->setCachePath(vfsStream::url('cacheDir'));
                return $provider;
            });

        // Add a relevant html
        $app->get('{path}', function($path) use ($app) {
                return new \Symfony\Component\HttpFoundation\Response(file_get_contents(__DIR__ . '/Resources/' . $path));
            })
            ->assert('path', '.+\.html');

        // Add routes to be matched by Phruts
        $app->get('/my/{path}.do', function (Request $request) use ($app) {
                return $app[Phruts\Util\Globals::ACTION_KERNEL]->handle($request, HttpKernelInterface::SUB_REQUEST, false);
            })
            ->assert('path', '.*')
            ->before(function(Request $request) {
                    // Match a ".do" as context path
                    $path = $request->attributes->get('path');
                    if(!empty($path)) {
                        $request->attributes->set(\Phruts\Action\RequestProcessor::INCLUDE_PATH_INFO, $path);
                    }
                });

        // Add routes to be matched by Phruts
        $app->get('{path}', function (Request $request) use ($app) {
                return $app[Phruts\Util\Globals::ACTION_KERNEL]->handle($request, HttpKernelInterface::SUB_REQUEST, false);
            })
            ->assert('path', '.*')
            ->value('path', '/') // Set the welcome path
            ->before(function(Request $request) {
                    $do = $request->get('do');
                    if(!empty($do)) {
                        $rewritePath = $request->attributes->get('_rewrite_path'); // tmp var as attributes
                        if(empty($rewritePath)) {
                            $request->attributes->set(\Phruts\Action\RequestProcessor::INCLUDE_PATH_INFO, $do);
                            $request->attributes->set('_rewrite_path', 1);
                        } else {
                            $request->attributes->set(\Phruts\Action\RequestProcessor::INCLUDE_PATH_INFO, null);
                        }
                    }
                });



        return $app;
    }
}
 