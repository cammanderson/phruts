<?php
namespace ActionTest;

use Symfony\Component\HttpFoundation\Request;

class ActionFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phruts\Action\AbstractActionForm
     */
    protected $form;

    public function setUp()
    {
        $this->form = $this->getMockForAbstractClass('\Phruts\Action\AbstractActionForm');
    }

    public function testInstantiate()
    {
        $this->assertTrue($this->form instanceof \Phruts\Action\AbstractActionForm);
    }

    public function testAccessorMutator()
    {
        // We should be able to access our kernel
        $actionKernel = $this->getMockBuilder('\Phruts\Action\ActionKernel')->disableOriginalConstructor()->getMock();
        $this->assertNotNull($actionKernel);
        $this->form->setActionKernel($actionKernel);
        $returnedActionKernel = $this->form->getActionKernel();
        $this->assertNotNull($returnedActionKernel);
        $this->assertEquals($actionKernel, $returnedActionKernel);
    }

    public function testReset()
    {
        // Default implementation does nothing..
        $this->assertEmpty($this->form->reset(new \Phruts\Config\ActionConfig(), new Request()));
    }

    public function testValidate()
    {
        // Default implementation does nothing..
        $this->assertNull($this->form->validate(new \Phruts\Config\ActionConfig(), new Request()));
    }
}
