<?php

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phruts\Action
     */
    protected $action;

    /**
     * @var \Phruts\Action\ActionKernel
     */
    protected $actionKernel;

    protected function setUp()
    {
        $this->action = new \Phruts\Action();

        $this->actionKernel = $this->getMockBuilder('\Phruts\Action\ActionKernel')->disableOriginalConstructor()->getMock();

        $this->action->setActionKernel($this->actionKernel);
    }

    public function testMutatorAccessors()
    {
        $this->assertNotEmpty($this->action->getActionKernel());
    }


    public function testGetDataSource()
    {
        $getDataSource = self::getMethod('getDataSource');

        $this->actionKernel->method('getDataSource')->willReturn(new PDO('sqlite:memory:'));

//        getDataSource($request, $key);

        $datasource = $getDataSource->invokeArgs($this->action, array(new \Symfony\Component\HttpFoundation\Request(), 'key'));
        $this->assertNotEmpty($datasource);
        $this->assertTrue($datasource instanceof PDO);
    }

    public function testGetLocale()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();
        $getLocale = self::getMethod('getLocale');

        $request->setDefaultLocale('fr');
        $this->actionKernel->method('getApplication')->willReturn(array('locale' => 'fr'));

        // Source from the default space in the application
        $this->assertEquals('fr', $getLocale->invokeArgs($this->action, array($request)));

        // Source from the session if set
        $request->setLocale('en');
        $this->assertEquals('en', $getLocale->invokeArgs($this->action, array($request)));
    }

    public function testGetResources()
    {
        // TODO: Test
    }

    public function testIsCancelled()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $isCancelled = self::getMethod('isCancelled');
        $this->assertEquals(false, $isCancelled->invokeArgs($this->action, array($request)));

        $request->attributes->set(\Phruts\Globals::CANCEL_KEY, true);
        $this->assertEquals(true, $isCancelled->invokeArgs($this->action, array($request)));
    }

    public function testSaveErrors()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $saveErrors = self::getMethod('saveErrors');

        $saveErrors->invokeArgs($this->action, array($request, null));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $errors = new \Phruts\Action\ActionErrors();

        $saveErrors->invokeArgs($this->action, array($request, $errors));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $errors->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveErrors->invokeArgs($this->action, array($request, $errors));
        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $saveErrors->invokeArgs($this->action, array($request, null));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));
    }

    public function testSaveErrorsSessions()
    {
        $storage = new  Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);

        $saveErrors = self::getMethod('saveErrorsSession');

        $saveErrors->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Globals::ERROR_KEY));

        $errors = new \Phruts\Action\ActionErrors();

        $saveErrors->invokeArgs($this->action, array($session, $errors));
        $this->assertEmpty($session->get(\Phruts\Globals::ERROR_KEY));

        $errors->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveErrors->invokeArgs($this->action, array($session, $errors));
        $this->assertNotEmpty($session->get(\Phruts\Globals::ERROR_KEY));

        $saveErrors->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Globals::ERROR_KEY));
    }

    public function testAddErrors()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $errors = new \Phruts\Action\ActionErrors();
        $errors->add('key', new \Phruts\Action\ActionError('message'));

        $saveErrors = self::getMethod('addErrors');
        $saveErrors->invokeArgs($this->action, array($request, $errors));

        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));
        $errorsRequest = $request->attributes->get(\Phruts\Globals::ERROR_KEY);
        $this->assertEquals(1, $errorsRequest->size());

        $errors2 = new \Phruts\Action\ActionErrors();
        $errors2->add('key2', new \Phruts\Action\ActionError('message'));
        $saveErrors->invokeArgs($this->action, array($request, $errors2));

        $errorsRequest = $request->attributes->get(\Phruts\Globals::ERROR_KEY);
        $this->assertEquals(2, $errorsRequest->size());

        $errors = new \Phruts\Action\ActionErrors();
        $request->attributes->set(\Phruts\Globals::ERROR_KEY, $errors);
        $saveErrors->invokeArgs($this->action, array($request, $errors));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));
    }

    public function testGetErrors()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $errors = new \Phruts\Action\ActionErrors();
        $errors->add('key1', new \Phruts\Action\ActionError('error'));
        $errors->add('key2', new \Phruts\Action\ActionError('error'));

        $saveErrors = self::getMethod('saveErrors');
        $saveErrors->invokeArgs($this->action, array($request, $errors));

        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::ERROR_KEY));

        $getErrors = self::getMethod('getErrors');
        $result = $getErrors->invokeArgs($this->action, array($request));

        $this->assertNotEmpty($result);
        $this->assertEquals(2, $result->size());
    }

    public function testSaveMessages()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $saveMessages = self::getMethod('saveMessages');

        $saveMessages->invokeArgs($this->action, array($request, null));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));

        $messages = new \Phruts\Action\ActionMessages();

        $saveMessages->invokeArgs($this->action, array($request, $messages));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));

        $messages->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveMessages->invokeArgs($this->action, array($request, $messages));
        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));

        $saveMessages->invokeArgs($this->action, array($request, null));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));
    }

    public function testSaveMessagesSessions()
    {
        $storage = new  Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);

        $saveMessages = self::getMethod('saveMessagesSession');

        $saveMessages->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Globals::MESSAGE_KEY));

        $messages = new \Phruts\Action\ActionMessages();

        $saveMessages->invokeArgs($this->action, array($session, $messages));
        $this->assertEmpty($session->get(\Phruts\Globals::MESSAGE_KEY));

        $messages->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveMessages->invokeArgs($this->action, array($session, $messages));
        $this->assertNotEmpty($session->get(\Phruts\Globals::MESSAGE_KEY));

        $saveMessages->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Globals::MESSAGE_KEY));
    }

    public function testAddMessages()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $messages = new \Phruts\Action\ActionMessages();
        $messages->add('key', new \Phruts\Action\ActionMessage('message'));

        $saveMessages = self::getMethod('addMessages');
        $saveMessages->invokeArgs($this->action, array($request, $messages));

        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));
        $messagesRequest = $request->attributes->get(\Phruts\Globals::MESSAGE_KEY);
        $this->assertEquals(1, $messagesRequest->size());

        $messages2 = new \Phruts\Action\ActionMessages();
        $messages2->add('key2', new \Phruts\Action\ActionMessage('message'));
        $saveMessages->invokeArgs($this->action, array($request, $messages2));

        $messagesRequest = $request->attributes->get(\Phruts\Globals::MESSAGE_KEY);
        $this->assertEquals(2, $messagesRequest->size());

        $messages = new \Phruts\Action\ActionMessages();
        $request->attributes->set(\Phruts\Globals::MESSAGE_KEY, $messages);
        $saveMessages->invokeArgs($this->action, array($request, $messages));
        $this->assertEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));
    }

    public function testGetMessages()
    {
        $request = new \Symfony\Component\HttpFoundation\Request();

        $messages = new \Phruts\Action\ActionMessages();
        $messages->add('key1', new \Phruts\Action\ActionMessage('message'));
        $messages->add('key2', new \Phruts\Action\ActionMessage('message'));

        $saveMessages = self::getMethod('saveMessages');
        $saveMessages->invokeArgs($this->action, array($request, $messages));

        $this->assertNotEmpty($request->attributes->get(\Phruts\Globals::MESSAGE_KEY));

        $getMessages = self::getMethod('getMessages');
        $result = $getMessages->invokeArgs($this->action, array($request));

        $this->assertNotEmpty($result);
        $this->assertEquals(2, $result->size());
    }

    public function testSetLocale()
    {
        $setLocale = self::getMethod('setLocale');
    }

    public function testGenerateToken()
    {
        $generateLocale = self::getMethod('generateToken');
    }

    public function testIsTokenValid()
    {
        $isTokenValid = self::getMethod('isTokenValid');
    }

    public function testResetToken()
    {
        $resetToken = self::getMethod('resetToken');
    }

    public function testSaveToken()
    {
        $isTokenValid = self::getMethod('isTokenValid');
    }

    public function testExecute()
    {
        $this->assertNull($this->action->execute(new \Phruts\Config\ActionMapping(), null, new \Symfony\Component\HttpFoundation\Request(), new \Symfony\Component\HttpFoundation\Response()));
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('\Phruts\Action');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


}