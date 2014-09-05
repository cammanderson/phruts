<?php


class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testMutatorAccessors()
    {
        $action = new \Phruts\Action();

        $mockActionKernel = new MockActionKernel();
        $action->setActionKernel($mockActionKernel);
        $this->assertNotEmpty($action->getActionKernel());
    }


    public function testGetDataSource()
    {
        $getDataSource = self::getMethod('getDataSource');
        $action = new \Phruts\Action();
        $mockActionKernel = new MockActionKernel();
        $action->setActionKernel($mockActionKernel);
        $datasource = $getDataSource->invokeArgs($action, array(new \Symfony\Component\HttpFoundation\Request(), 'key'));
        $this->assertNotEmpty($datasource);
    }

    public function testGetLocale()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $action = new \Phruts\Action();
        $actionKernel = new MockActionKernel();
        $getLocale = self::getMethod('getLocale');


        $request->setDefaultLocale('fr');
        $actionKernel->application['locale'] = 'fr';

        // Source from the default space in the application
        $action->setActionKernel($actionKernel);
        $this->assertEquals('fr', $getLocale->invokeArgs($action, array($request)));

        // Source from the session if set
        $request->setLocale('en');
        $this->assertEquals('en', $getLocale->invokeArgs($action, array($request)));
    }

    public function testGetResources()
    {
        // TODO: Test
    }

    public function testIsCancelled()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $action = new \Phruts\Action();

        $isCancelled = self::getMethod('isCancelled');
        $this->assertEquals(false, $isCancelled->invokeArgs($action, array($request)));

        $request->attributes->set(\Phruts\Globals::CANCEL_KEY, true);
        $this->assertEquals(true, $isCancelled->invokeArgs($action, array($request)));
    }

    public function testSaveErrors()
    {
        $action = new \Phruts\Action();
        $request = new \Symfony\Component\HttpFoundation\Request();

        $saveErrors = self::getMethod('saveErrors');

        $saveErrors->invokeArgs($action, array($request, null));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $errors = new \Phruts\Action\ActionErrors();

        $saveErrors->invokeArgs($action, array($request, $errors));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $errors->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveErrors->invokeArgs($action, array($request, $errors));
        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $saveErrors->invokeArgs($action, array($request, null));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));
    }

    public function testSaveErrorsSessions()
    {
        $storage = new  Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);

        $action = new \Phruts\Action();

        $saveErrors = self::getMethod('saveErrorsSession');

        $saveErrors->invokeArgs($action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Globals::ERROR_KEY));

        $errors = new \Phruts\Action\ActionErrors();

        $saveErrors->invokeArgs($action, array($session, $errors));
        $this->assertEmpty($session->get(\Phruts\Globals::ERROR_KEY));

        $errors->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveErrors->invokeArgs($action, array($session, $errors));
        $this->assertNotEmpty($session->get(\Phruts\Globals::ERROR_KEY));

        $saveErrors->invokeArgs($action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Globals::ERROR_KEY));
    }

    public function testAddErrors()
    {

    }

    public function testExecute()
    {
        $action = new \Phruts\Action();
        $this->assertNull($action->execute(new \Phruts\Config\ActionMapping(), null, new \Symfony\Component\HttpFoundation\Request(), new \Symfony\Component\HttpFoundation\Response()));
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('\Phruts\Action');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


}


class MockActionKernel extends \Phruts\Action\ActionKernel
{

    public $application;
    public function getApplication()
    {
        return $this->application;
    }

    function __construct()
    {
    }

    public function getDataSource(\Symfony\Component\HttpFoundation\Request $request, $key)
    {
        return new MockPDO();
    }


}

class MockPDO extends \PDO
{

    function __construct()
    {
    }
}