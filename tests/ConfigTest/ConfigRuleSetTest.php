<?php
namespace ConfigTest;

use Phigester\Digester;
use Phruts\Config\ConfigRuleSet;
use Phruts\Config\ModuleConfig;

class ConfigRuleSetTest extends \PHPUnit_Framework_TestCase
{
    public function testRuleSets()
    {
        $digester = new Digester();
        $digester->addRuleSet(new ConfigRuleSet('phruts-config'));
        $moduleConfig = new ModuleConfig('');
        $digester->push($moduleConfig);
        $digester->parse(__DIR__ . '/full-config.xml');
    }
}
 