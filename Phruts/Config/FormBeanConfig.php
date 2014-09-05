<?php

namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of a <form-bean> element
 * in a PHruts application configuration file
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com> (Phruts Contributor)
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class FormBeanConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
     * The set of FormProperty elements defining dynamic form properties for
     * this form bean, keyed by property name.
     */
    protected $formProperties = array();

    /**
	 * Freeze the configuration of this component.
	 */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
	 * The module configuration with which this form bean definition
	 * is associated.
	 *
	 * @var ModuleConfig
	 */
    protected $moduleConfig = null;

    /**
	 * Return the module configuration with which this form bean definition
	 * is associated.
	 *
	 * @return ModuleConfig
	 */
    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    /**
	 * Set the module configuration with which this form bean definition
	 * is associated.
	 *
	 * @param ModuleConfig $moduleConfig The new ModuleConfig or
	 * null to disassociate this form bean configuration from any module
	 * @throws \Phruts\Exception\IllegalState
	 * @todo Check if the parameter is a ModuleConfig object.
	 */
    public function setModuleConfig($moduleConfig)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->moduleConfig = $moduleConfig;
    }

    /**
	 * The unique identifier of this form bean.
	 *
	 * It is used to reference this bean in \Phruts\Config\ActionConfig instances as well
	 * as for the name of the request or session attribute under which the
	 * corresponding form bean instance is created or accessed.
	 * @var string
	 */
    protected $name = null;

    /**
	 * @return string
	 */
    public function getName()
    {
        return $this->name;
    }

    /**
	 * @param string $name
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setName($name)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->name = (string) $name;
    }

    /**
	 * The fully qualified PHP class name of the implementation class
	 * to be used or generated.
	 *
	 * @var string
	 */
    protected $type = null;

    /**
	 * @return string
	 */
    public function getType()
    {
        return $this->type;
    }

    /**
	 * @param string $type
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setType($type)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->type = (string) $type;
    }

     /**
     * Add a new <code>FormPropertyConfig</code> instance to the set associated
     * with this module.
     *
     * @param config The new configuration instance to be added
     *
     * @exception \Phruts\Exception\IllegalArgument if this property name has already
     *  been defined
     */
    public function addFormPropertyConfig(\Phruts\Config\FormPropertyConfig $config)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState("Configuration is frozen");
        }
        if (!empty($this->formProperties[$config->getName()])) {
            throw new \Phruts\Exception\IllegalArgument("Property " + config.getName() + " already defined");
        }
        $this->formProperties[$config->getName()] = $config;
    }

    /**
     * Return the form property configuration for the specified property
     * name, if any; otherwise return <code>null</code>.
     *
     * @param name Form property name to find a configuration for
     * @return FormPropertyConfig
     */
    public function findFormPropertyConfig($name)
    {
        if(!empty($this->formProperties[$name])) return $this->formProperties[$name];

        return null;
    }

    /**
     * Return the form property configurations for this module.  If there
     * are none, a zero-length array is returned.
     * @return array FormPropertyConfig[]
     */
    public function findFormPropertyConfigs()
    {
        return $this->formProperties;
    }

    /**
	 * Return a String representation of this object.
	 *
	 * @return string
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\FormBeanConfig[';
        $sb .= 'name=' . var_export($this->name, true);
        $sb .= ',type=' . var_export($this->type, true);
        $sb .= ',properties=' . var_export(array_keys($this->formProperties), true);
        $sb .= ']';

        return $sb;
    }
}
