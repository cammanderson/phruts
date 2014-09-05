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