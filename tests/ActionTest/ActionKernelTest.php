<?php
namespace ActionTest;

use Phruts\Action\ActionKernel;
use Silex\Application;

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
        $this->application = new Application();
        $this->actionKernel = new \Phruts\Action\ActionKernel($this->application);
    }

    public function testInstantiate()
    {
        $this->assertTrue($this->actionKernel instanceof \Phruts\Action\ActionKernel);
    }

    public function testAccessorMutators()
    {
        $this->assertNotEmpty($this->actionKernel->getApplication());
    }

    public function testHandle()
    {

    }
}
 