<?php

namespace Phruts\Config;

/**
 * The set of Digester rules required to parse a PHruts configuration file
 * (phruts-config.xml).
 *
 */
class ConfigRuleSet extends \Phigester\RuleSetBase
{
    private $configPrefix;
    public function __construct($configPrefix = 'phruts-config')
    {
        // Set the config prefix
        $this->configPrefix = $configPrefix;
    }

    /**
     * Add the set of Rule instances defined in this RuleSet to the
     * specified Digester instance.
     *
     * This method should only be called by a Digester instance. These
     * rules assume that an instance of ModuleConfig is
     * pushed onto the evaluation stack before parsing begins.
     *
     * @param \Phigester\Digester $digester Digester instance to which the
     * new Rule instances should be added.
     */
    public function addRuleInstances(\Phigester\Digester $digester)
    {
        $digester->addFactoryCreate($this->configPrefix . '/data-sources/data-source', new \Phruts\Config\Digester\DataSourceConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/data-sources/data-source');
        $digester->addSetNext($this->configPrefix . '/data-sources/data-source', 'addDataSourceConfig');
        $digester->addRule($this->configPrefix . '/data-sources/data-source/set-property', new \Phruts\Config\Digester\AddDataSourcePropertyRule());

        $digester->addRule($this->configPrefix . '/action-mappings', new \Phruts\Config\Digester\SetClassRule());

        $digester->addFactoryCreate($this->configPrefix . '/action-mappings/action', new \Phruts\Config\Digester\ActionConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/action-mappings/action');
        $digester->addSetNext($this->configPrefix . '/action-mappings/action', 'addActionConfig');
        $digester->addSetProperty($this->configPrefix . '/action-mappings/action/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/action-mappings/action/exception', new \Phruts\Config\Digester\ExceptionConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/action-mappings/action/exception');
        $digester->addSetNext($this->configPrefix . '/action-mappings/action/exception', 'addExceptionConfig');
        $digester->addSetProperty('struts-config/action-mappings/action/exception/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/action-mappings/action/forward', new \Phruts\Config\Digester\ForwardConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/action-mappings/action/forward');
        $digester->addSetNext($this->configPrefix . '/action-mappings/action/forward', 'addForwardConfig');
        $digester->addSetProperty($this->configPrefix . '/action-mappings/action/forward/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/controller', new \Phruts\Config\Digester\ControllerConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/controller');
        $digester->addSetNext($this->configPrefix . '/controller', 'setControllerConfig');
        $digester->addSetProperty($this->configPrefix . '/controller/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/form-beans/form-bean', new \Phruts\Config\Digester\FormBeanConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/form-beans/form-bean');
        $digester->addSetNext($this->configPrefix . '/form-beans/form-bean', 'addFormBeanConfig');
        $digester->addSetProperty($this->configPrefix . '/form-beans/form-bean/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/form-beans/form-bean/form-property', new \Phruts\Config\Digester\FormPropertyConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/form-beans/form-bean/form-property');
        $digester->addSetNext($this->configPrefix . '/form-beans/form-bean/form-property', 'addFormPropertyConfig');
        $digester->addSetProperty($this->configPrefix . '/form-beans/form-bean/form-property/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/global-exceptions/exception', new \Phruts\Config\Digester\ExceptionConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/global-exceptions/exception');
        $digester->addSetNext($this->configPrefix . '/global-exceptions/exception', 'addExceptionConfig');
        $digester->addSetProperty($this->configPrefix . '/global-exceptions/exception/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/global-forwards/forward', new \Phruts\Config\Digester\ForwardConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/global-forwards/forward');
        $digester->addSetNext($this->configPrefix . '/global-forwards/forward', 'addForwardConfig');
        $digester->addSetProperty($this->configPrefix . '/global-forwards/forward/set-property', 'property', 'value');

        $digester->addFactoryCreate($this->configPrefix . '/message-resources', new \Phruts\Config\Digester\MessageResourcesConfigFactory());
        $digester->addSetProperties($this->configPrefix . '/message-resources');
        $digester->addSetNext($this->configPrefix . '/message-resources', 'addMessageResourcesConfig');
        $digester->addSetProperty($this->configPrefix . '/message-resources/set-property', 'property', 'value');

        $digester->addObjectCreate($this->configPrefix . '/plug-in', '\Phruts\Config\PlugInConfig');
        $digester->addSetProperties($this->configPrefix . '/plug-in');
        $digester->addSetNext($this->configPrefix . '/plug-in', 'addPlugInConfig');
        $digester->addRule($this->configPrefix . '/plug-in/set-property', new \Phruts\Config\Digester\PlugInSetPropertyRule());
    }
}
