<?php
/*
 * Author; Cameron Manderson <cameronmanderson@gmail.com>
 */

class ActionMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phruts\Config\ModuleConfig
     */
    protected $moduleConfig;
    /**
     * @var \Phruts\Action\ActionMapping
     */
    protected $actionMapping;

    public function setUp()
    {
        $this->moduleConfig = new \Phruts\Config\ModuleConfig('example');
        $forwardConfig1 = new \Phruts\Config\ForwardConfig();
        $forwardConfig1->setName('path1');
        $this->moduleConfig->addForwardConfig($forwardConfig1);
        $this->actionMapping = new \Phruts\Action\ActionMapping();
        $forwardConfig2 = new \Phruts\Config\ForwardConfig();
        $forwardConfig2->setName('path2');
        $this->actionMapping->addForwardConfig($forwardConfig2);
        $this->actionMapping->setModuleConfig($this->moduleConfig);
    }

    public function testFindForward()
    {
        $this->assertNotEmpty($this->actionMapping->findForward('path1'));
        $this->assertNotEmpty($this->actionMapping->findForward('path2'));
        $this->assertEmpty($this->actionMapping->findForward('path3'));
    }

    public function testFindForwards()
    {
        $this->assertEquals(2, count($this->actionMapping->findForwards()));
    }

    public function testGetInputForward()
    {
        // Input forward
        $this->actionMapping->setInput('aPath');
        $forward = $this->actionMapping->getInputForward();
        $this->assertNotEmpty($forward);
        $this->assertEquals('aPath', $forward->getPath());

        // Controller based input forward
        $controllerConfig = new \Phruts\Config\ControllerConfig();
        $controllerConfig->setInputForward(true);
        $this->actionMapping->setInput('path1');
        $this->moduleConfig->setControllerConfig($controllerConfig);
        $forward = $this->actionMapping->getInputForward();
        $this->assertNotEmpty($forward);
        $this->assertEquals('path1', $forward->getName());
    }
}
 