<?php
namespace ActionTest;

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

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    protected function setUp()
    {
        $this->action = new \Phruts\Action\Action();

        $this->actionKernel = $this->getMockBuilder('\Phruts\Action\ActionKernel')->disableOriginalConstructor()->getMock();

        $this->action->setActionKernel($this->actionKernel);

        $this->request = new \Symfony\Component\HttpFoundation\Request();
        $storage = new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage();
        $session = new \Symfony\Component\HttpFoundation\Session\Session($storage);
//        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')->disableOriginalConstructor()->getMock();
        $this->request->setSession($session);

    }

    public function testMutatorAccessors()
    {
        $this->assertNotEmpty($this->action->getActionKernel());
    }


    public function testGetDataSource()
    {
        $getDataSource = self::getMethod('getDataSource');

        $this->actionKernel->method('getDataSource')->willReturn(new \PDO('sqlite::memory:'));

//        getDataSource($this->request, $key);

        $datasource = $getDataSource->invokeArgs($this->action, array(new \Symfony\Component\HttpFoundation\Request(), 'key'));
        $this->assertNotEmpty($datasource);
        $this->assertTrue($datasource instanceof \PDO);

        $this->actionKernel->method('getDataSource')->will($this->throwException(new \Exception));
        $this->setExpectedException('\Exception');
        $datasource = $getDataSource->invokeArgs($this->action, array(new \Symfony\Component\HttpFoundation\Request(), 'key'));
    }

    public function testGetResources()
    {
        $getDataSource = self::getMethod('getResources');

        // Get from the request
        $messages = new \Phruts\Util\PropertyMessageResources(__DIR__ .'/UtilTest/Example');
        $this->request->attributes->set(\Phruts\Util\Globals::MESSAGES_KEY, $messages);
        $result = $getDataSource->invokeArgs($this->action, array($this->request));
        $this->assertNotEmpty($result);

        // Get from the application
        $request = \Symfony\Component\HttpFoundation\Request::create('http://localhost/test', 'GET', array(), array(), array(), array('PATH_INFO' => '/test'));

        $application = new \Silex\Application;
        $application[\Phruts\Util\Globals::PREFIXES_KEY] = array();
        $moduleConfig = new \Phruts\Config\ModuleConfig('');
        $application[\Phruts\Util\Globals::MODULE_KEY] = $moduleConfig;
        \Phruts\Util\RequestUtils::selectModule($request, $application);

        $key = 'key';
        $application[$key] = $messages;
        $actionKernel = new \Phruts\Action\ActionKernel($application);
        $this->action->setActionKernel($actionKernel);

        $result = $getDataSource->invokeArgs($this->action, array($this->request, $key));
        $this->assertNotEmpty($result);
    }

    public function testLocale()
    {
        $getLocale = self::getMethod('getLocale');
        $setLocale = self::getMethod('setLocale');

        $this->request->setDefaultLocale('fr');
        $this->actionKernel->method('getApplication')->willReturn(array('locale' => 'fr'));

        // Source from the default space in the application
        $this->assertEquals('fr', $getLocale->invokeArgs($this->action, array($this->request)));

        // Source from the session if set
        $setLocale->invokeArgs($this->action, array($this->request, 'gb'));
        $this->assertEquals('gb', $getLocale->invokeArgs($this->action, array($this->request)));

        $setLocale->invokeArgs($this->action, array($this->request, null));
        $this->assertEquals('fr', $getLocale->invokeArgs($this->action, array($this->request)));
    }

    public function testIsCancelled()
    {
        $isCancelled = self::getMethod('isCancelled');
        $this->assertEquals(false, $isCancelled->invokeArgs($this->action, array($this->request)));

        $this->request->attributes->set(\Phruts\Util\Globals::CANCEL_KEY, true);
        $this->assertEquals(true, $isCancelled->invokeArgs($this->action, array($this->request)));
    }

    public function testSaveErrors()
    {
        $saveErrors = self::getMethod('saveErrors');

        $saveErrors->invokeArgs($this->action, array($this->request, null));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));

        $errors = new \Phruts\Action\ActionErrors();

        $saveErrors->invokeArgs($this->action, array($this->request, $errors));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));

        $errors->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveErrors->invokeArgs($this->action, array($this->request, $errors));
        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));

        $saveErrors->invokeArgs($this->action, array($this->request, null));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));
    }

    public function testSaveErrorsSessions()
    {
        $saveErrors = self::getMethod('saveErrorsSession');
        $session = $this->request->getSession();

        $saveErrors->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Util\Globals::ERROR_KEY));

        $errors = new \Phruts\Action\ActionErrors();

        $saveErrors->invokeArgs($this->action, array($session, $errors));
        $this->assertEmpty($session->get(\Phruts\Util\Globals::ERROR_KEY));

        $errors->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveErrors->invokeArgs($this->action, array($session, $errors));
        $this->assertNotEmpty($session->get(\Phruts\Util\Globals::ERROR_KEY));

        $saveErrors->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Util\Globals::ERROR_KEY));
    }

    public function testAddErrors()
    {
        $this->request = new \Symfony\Component\HttpFoundation\Request();

        $errors = new \Phruts\Action\ActionErrors();
        $errors->add('key', new \Phruts\Action\ActionError('message'));

        $saveErrors = self::getMethod('addErrors');
        $this->assertEmpty($saveErrors->invokeArgs($this->action, array($this->request, null)));
        $saveErrors->invokeArgs($this->action, array($this->request, $errors));

        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));
        $errorsRequest = $this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY);
        $this->assertEquals(1, $errorsRequest->size());

        $errors2 = new \Phruts\Action\ActionErrors();
        $errors2->add('key2', new \Phruts\Action\ActionError('message'));
        $saveErrors->invokeArgs($this->action, array($this->request, $errors2));

        $errorsRequest = $this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY);
        $this->assertEquals(2, $errorsRequest->size());

        $errors = new \Phruts\Action\ActionErrors();
        $this->request->attributes->set(\Phruts\Util\Globals::ERROR_KEY, $errors);
        $saveErrors->invokeArgs($this->action, array($this->request, $errors));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));
    }

    public function testGetErrors()
    {
        $getErrors = self::getMethod('getErrors');

        $errors = $getErrors->invokeArgs($this->action, array($this->request));
        $this->assertEquals(0, $errors->size());

        $errors = new \Phruts\Action\ActionErrors();
        $errors->add('key1', new \Phruts\Action\ActionError('error'));
        $errors->add('key2', new \Phruts\Action\ActionError('error'));

        $saveErrors = self::getMethod('saveErrors');
        $saveErrors->invokeArgs($this->action, array($this->request, $errors));

        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::ERROR_KEY));

        $result = $getErrors->invokeArgs($this->action, array($this->request));

        $this->assertNotEmpty($result);
        $this->assertEquals(2, $result->size());
    }

    public function testSaveMessages()
    {
        $saveMessages = self::getMethod('saveMessages');

        $saveMessages->invokeArgs($this->action, array($this->request, null));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $messages = new \Phruts\Action\ActionMessages();

        $saveMessages->invokeArgs($this->action, array($this->request, $messages));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $messages->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveMessages->invokeArgs($this->action, array($this->request, $messages));
        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $saveMessages->invokeArgs($this->action, array($this->request, null));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));
    }

    public function testSaveMessagesSessions()
    {
        $session = $this->request->getSession();
        $saveMessages = self::getMethod('saveMessagesSession');

        $saveMessages->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $messages = new \Phruts\Action\ActionMessages();

        $saveMessages->invokeArgs($this->action, array($session, $messages));
        $this->assertEmpty($session->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $messages->add('1', new \Phruts\Action\ActionMessage('abc'));
        $saveMessages->invokeArgs($this->action, array($session, $messages));
        $this->assertNotEmpty($session->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $saveMessages->invokeArgs($this->action, array($session, null));
        $this->assertEmpty($session->get(\Phruts\Util\Globals::MESSAGE_KEY));
    }

    public function testAddMessages()
    {
        $messages = new \Phruts\Action\ActionMessages();
        $messages->add('key', new \Phruts\Action\ActionMessage('message'));

        $saveMessages = self::getMethod('addMessages');
        $saveMessages->invokeArgs($this->action, array($this->request, $messages));

        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));
        $messagesRequest = $this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY);
        $this->assertEquals(1, $messagesRequest->size());

        $messages2 = new \Phruts\Action\ActionMessages();
        $messages2->add('key2', new \Phruts\Action\ActionMessage('message'));
        $saveMessages->invokeArgs($this->action, array($this->request, $messages2));

        $messagesRequest = $this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY);
        $this->assertEquals(2, $messagesRequest->size());

        $messages = new \Phruts\Action\ActionMessages();
        $this->request->attributes->set(\Phruts\Util\Globals::MESSAGE_KEY, $messages);
        $saveMessages->invokeArgs($this->action, array($this->request, $messages));
        $this->assertEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));
    }

    public function testGetMessages()
    {
        $getMessages = self::getMethod('getMessages');

        $messages = $getMessages->invokeArgs($this->action, array($this->request));
        $this->assertEquals(0, $messages->size());

        $messages = new \Phruts\Action\ActionMessages();
        $messages->add('key1', new \Phruts\Action\ActionMessage('message'));
        $messages->add('key2', new \Phruts\Action\ActionMessage('message'));

        $saveMessages = self::getMethod('saveMessages');
        $saveMessages->invokeArgs($this->action, array($this->request, $messages));

        $this->assertNotEmpty($this->request->attributes->get(\Phruts\Util\Globals::MESSAGE_KEY));

        $result = $getMessages->invokeArgs($this->action, array($this->request));

        $this->assertNotEmpty($result);
        $this->assertEquals(2, $result->size());
    }

    public function testGenerateToken()
    {
        $generateToken = self::getMethod('generateToken');

        $result = $generateToken->invokeArgs($this->action, array($this->request));
        $this->assertNotEmpty($result);
    }

    public function testIsTokenValid()
    {
        $saveToken = self::getMethod('saveToken');
        $isTokenValid = self::getMethod('isTokenValid');
        $saveToken->invokeArgs($this->action, array($this->request));
        $token = $this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY);
        $this->request->query->set(\Phruts\Util\Globals::TOKEN_KEY, $token);
        $this->assertTrue($isTokenValid->invokeArgs($this->action, array($this->request)));
    }

    public function testResetToken()
    {
        $saveToken = self::getMethod('saveToken');
        $resetToken = self::getMethod('resetToken');
        $saveToken->invokeArgs($this->action, array($this->request));
        $this->assertNotEmpty($this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY));
        $resetToken->invokeArgs($this->action, array($this->request));
        $this->assertEmpty($this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY));
    }

    public function testSaveToken()
    {
        $saveToken = self::getMethod('saveToken');
        $saveToken->invokeArgs($this->action, array($this->request));
        $this->assertNotEmpty($this->request->getSession()->get(\Phruts\Util\Globals::TRANSACTION_TOKEN_KEY));
    }

    public function testExecute()
    {
        $this->assertNull($this->action->execute(new \Phruts\Action\ActionMapping(), null, new \Symfony\Component\HttpFoundation\Request(), new \Symfony\Component\HttpFoundation\Response()));
    }

    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('\Phruts\Action\Action');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }


}