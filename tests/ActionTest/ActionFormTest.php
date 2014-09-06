<?php
namespace ActionTest;

class ActionFormTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiate()
    {
        $object = new MockForm();
        $this->assertTrue($object instanceof \Phruts\Action\AbstractActionForm);
    }

    public function testAccessorMutator()
    {

    }
}

class MockForm extends \Phruts\Action\AbstractActionForm
{

}
 