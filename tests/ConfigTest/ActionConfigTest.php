<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace ConfigTest;

use Phruts\Config\ActionConfig;
use Phruts\Config\ExceptionConfig;
use Phruts\Config\ForwardConfig;
use Phruts\Config\ModuleConfig;

class ActionConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ActionConfig;
     */
    protected $actionConfig;

    public function setUp()
    {
        $this->actionConfig = new ActionConfig();
        $this->actionConfig->setModuleConfig(new ModuleConfig(''));
        $this->actionConfig->freeze();
    }

    public function testAddExceptionConfigFreeze()
    {
        $this->setExpectedException('\Exception');
        $this->actionConfig->addExceptionConfig(new ExceptionConfig());
    }

    public function testAddForwardConfigFreeze()
    {
        $this->setExpectedException('\Exception');
        $this->actionConfig->addForwardConfig(new ForwardConfig());
    }

    public function testRemoveExceptionConfigFreeze()
    {
        $this->setExpectedException('\Exception');
        $this->actionConfig->removeExceptionConfig(new ExceptionConfig());
    }
}
 