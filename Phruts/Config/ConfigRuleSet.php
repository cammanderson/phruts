<?php

namespace Phruts\Config {

    /**
     * The set of Digester rules required to parse a PHruts configuration file
     * (phruts-config.xml).
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts)
     * @TODO: Update the reference to the locale in the Controller config to be str
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
            $digester->addFactoryCreate($this->configPrefix . '/data-sources/data-source', new \Phruts\Config\DataSourceConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/data-sources/data-source');
            $digester->addSetNext($this->configPrefix . '/data-sources/data-source', 'addDataSourceConfig');
            $digester->addRule($this->configPrefix . '/data-sources/data-source/set-property', new \Phruts\Config\AddDataSourcePropertyRule());

            $digester->addRule($this->configPrefix . '/action-mappings', new \Phruts\Config\SetClassRule());

            $digester->addFactoryCreate($this->configPrefix . '/action-mappings/action', new \Phruts\Config\ActionConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/action-mappings/action');
            $digester->addSetNext($this->configPrefix . '/action-mappings/action', 'addActionConfig');
            $digester->addSetProperty($this->configPrefix . '/action-mappings/action/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/action-mappings/action/exception', new \Phruts\Config\ExceptionConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/action-mappings/action/exception');
            $digester->addSetNext($this->configPrefix . '/action-mappings/action/exception', 'addExceptionConfig');
            $digester->addSetProperty('struts-config/action-mappings/action/exception/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/action-mappings/action/forward', new \Phruts\Config\ForwardConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/action-mappings/action/forward');
            $digester->addSetNext($this->configPrefix . '/action-mappings/action/forward', 'addForwardConfig');
            $digester->addSetProperty($this->configPrefix . '/action-mappings/action/forward/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/controller', new \Phruts\Config\ControllerConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/controller');
            $digester->addSetNext($this->configPrefix . '/controller', 'setControllerConfig');
            $digester->addSetProperty($this->configPrefix . '/controller/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/form-beans/form-bean', new \Phruts\Config\FormBeanConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/form-beans/form-bean');
            $digester->addSetNext($this->configPrefix . '/form-beans/form-bean', 'addFormBeanConfig');
            $digester->addSetProperty($this->configPrefix . '/form-beans/form-bean/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/form-beans/form-bean/form-property', new \Phruts\Config\FormPropertyConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/form-beans/form-bean/form-property');
            $digester->addSetNext($this->configPrefix . '/form-beans/form-bean/form-property', 'addFormPropertyConfig');
            $digester->addSetProperty($this->configPrefix . '/form-beans/form-bean/form-property/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/global-exceptions/exception', new \Phruts\Config\ExceptionConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/global-exceptions/exception');
            $digester->addSetNext($this->configPrefix . '/global-exceptions/exception', 'addExceptionConfig');
            $digester->addSetProperty($this->configPrefix . '/global-exceptions/exception/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/global-forwards/forward', new \Phruts\Config\ForwardConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/global-forwards/forward');
            $digester->addSetNext($this->configPrefix . '/global-forwards/forward', 'addForwardConfig');
            $digester->addSetProperty($this->configPrefix . '/global-forwards/forward/set-property', 'property', 'value');

            $digester->addFactoryCreate($this->configPrefix . '/message-resources', new \Phruts\Config\MessageResourcesConfigFactory());
            $digester->addSetProperties($this->configPrefix . '/message-resources');
            $digester->addSetNext($this->configPrefix . '/message-resources', 'addMessageResourcesConfig');
            $digester->addSetProperty($this->configPrefix . '/message-resources/set-property', 'property', 'value');

            $digester->addObjectCreate($this->configPrefix . '/plug-in', '\Phruts\Config\PlugInConfig');
            $digester->addSetProperties($this->configPrefix . '/plug-in');
            $digester->addSetNext($this->configPrefix . '/plug-in', 'addPlugInConfig');
            $digester->addRule($this->configPrefix . '/plug-in/set-property', new \Phruts\Config\PlugInSetPropertyRule());
        }
    }

    /**
     * Class that sets the name of the class to use when creating action config
     * instances.
     *
     * The value is set on the object on the top of the stack, which
     * must be a ModuleConfig.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class SetClassRule extends \Phigester\AbstractRule
    {
        /**
         * @param array $attributes
         */
        public function begin(array $attributes)
        {
            if (array_key_exists('type', $attributes)) {
                $className = $attributes['type'];

                $mc = $this->digester->peek();
                $mc->setActionClass($className);
            }
        }

        /**
         * @return string
         */
        public function toString()
        {
            return 'SetActionClassRule[]';
        }
    }

    /**
     * An object creation factory which creates action config instances, taking
     * into account the default class name, which may have been specified on
     * the parent element and which is made available through the object on
     * the top of the stack, which must be a ModuleConfig.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class ActionConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $mc = $this->digester->peek();
                $className = $mc->getActionClass();
            }

            // Instantiate the new object and return it
            $actionConfig = null;
            try {
                $actionConfig = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\ActionConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('\Phruts\Config\ActionConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $actionConfig;
        }
    }

    /**
     * An object creation factory which creates forward config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class ForwardConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\ForwardConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\ForwardConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('ForwardConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * An object creation factory which creates controller config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class ControllerConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\ControllerConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\ControllerConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('\Phruts\Config\ControllerConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * An object creation factory which creates form property config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class FormPropertyConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\FormPropertyConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\FormPropertyConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('FormPropertyConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * An object creation factory which creates form bean config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class FormBeanConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\FormBeanConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\FormBeanConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('\Phruts\Config\FormBeanConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * An object creation factory which creates message resources config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class MessageResourcesConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\MessageResourcesConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\MessageResourcesConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('\Phruts\Config\MessageResourcesConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * An object creation factory which creates data source config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class DataSourceConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\DataSourceConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\DataSourceConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('\Phruts\Config\DataSourceConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * An object creation factory which creates exception config instances.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     */
    final class ExceptionConfigFactory extends \Phigester\AbstractObjectCreationFactory
    {
        /**
         * @param array $attributes
         * @return object
         */
        public function createObject(array $attributes)
        {
            // Identify the name of the class to instantiate
            if (array_key_exists('className', $attributes)) {
                $className = $attributes['className'];
            } else {
                $className = '\Phruts\Config\ExceptionConfig';
            }

            // Instantiate the new object and return it
            $config = null;
            try {
                $config = \Phruts\ClassLoader::newInstance($className, '\Phruts\Config\ExceptionConfig');
            } catch (\Exception $e) {
                $this->digester->getLogger()->error('ExceptionConfigFactory->createObject(): ' . $e->getMessage());
            }

            return $config;
        }
    }

    /**
     * Class that calls addProperty for the top object on the stack, which must be
     * a DataSourceConfig.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class AddDataSourcePropertyRule extends \Phigester\AbstractRule
    {
        /**
         * @param array $attributes
         */
        public function begin($attributes)
        {
            $dataSourceConfig = $this->digester->peek();
            $dataSourceConfig->addProperty($attributes['property'], $attributes['value']);
        }

        /**
         * @return string
         */
        public function toString()
        {
            return 'AddDataSourcePropertyRule[]';
        }
    }

    /**
     * Class that records the name and value of a configuration property to
     * be used in configuring a PlugIn instance when instantiated.
     *
     * @author Cam Manderson <cameronmanderson@gmail.com> (PHP53 port of Struts)
     * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
     * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
    final class PlugInSetPropertyRule extends \Phigester\AbstractRule
    {
        /**
         * @param array $attributes
         */
        public function begin($attributes)
        {
            $plugInConfig = $this->digester->peek();
            $plugInConfig->addProperty($attributes['property'], $attributes['value']);
        }

        /**
         * @return string
         */
        public function toString()
        {
            return 'PlugInSetPropertyRule[]';
        }
    }
}
