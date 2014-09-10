<?php
namespace ActionTest;

use Phruts\Action\ActionError;
use Phruts\Action\ExceptionHandler;
use Phruts\Action\ActionMapping;
use Phruts\Config\ExceptionConfig;
use Phruts\Config\ForwardConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $exceptionHandler;

    public function setUp()
    {
        $this->exceptionHandler = new ExceptionHandler();
    }

    public function testInstantiate()
    {
        $this->assertTrue($this->exceptionHandler instanceof \Phruts\Action\ExceptionHandler);
    }

    public function testExecute()
    {
        // Add an exception config
        $exceptionConfig = new ExceptionConfig();
        $exceptionConfig->setPath('/exception.php');
        $exceptionConfig->setKey('example.exception');
        $exceptionConfig->setType('\Exception');

        // Action Mapping
        $actionMapping = new ActionMapping();
        $actionMapping->setType('\Phruts\Actions\ForwardAction');
        $actionMapping->addExceptionConfig($exceptionConfig);
        $actionMapping->setPath('throw');

        $formInstance = null;

        $request = new Request();
        $response = new Response();

        $forward = $this->exceptionHandler->execute(
            new \Exception('Example Error'),
            $exceptionConfig,
            $actionMapping,
            $formInstance,
            $request,
            $response
        );

        $this->assertTrue($forward instanceof \Phruts\Config\ForwardConfig);
    }

    public function testStoreException()
    {

        $storeException = self::getMethod('storeException');
        $request = new Request();
        $property = 'prop';
        $error = new ActionError('example');
        $forwardConfig = new ForwardConfig();
        $forwardConfig->setPath("path");
        $forwardConfig->setName("name");

        $scope = "request";
        $storeException->invokeArgs($this->exceptionHandler, array($request, $property, $error, $forwardConfig, $scope));
        $this->assertNotEmpty($request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));
        $this->assertTrue($request->attributes->get(\Phruts\Util\Globals::ERROR_KEY) instanceof \Phruts\Action\ActionErrors);

        $scope = "session";
        $storage = new  \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);
        $request->setSession($session);
        $storeException->invokeArgs($this->exceptionHandler, array($request, $property, $error, $forwardConfig, $scope));
        $this->assertNotEmpty($request->getSession()->get(\Phruts\Util\Globals::ERROR_KEY));
        $this->assertTrue($request->getSession()->get(\Phruts\Util\Globals::ERROR_KEY) instanceof \Phruts\Action\ActionErrors);
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\Phruts\Action\ExceptionHandler');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
 