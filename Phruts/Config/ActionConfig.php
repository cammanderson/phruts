<?php
/* Copyright 2010 Phruts-project
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA
 *
 * This file incorporates work covered by the following copyright and
 * permissions notice:
 *
 * Copyright (C) 2008 PHruts
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 * Copyright 2004 The Apache Software Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of
 * an <action> element from a PHruts module configuration file.
 *
 * @author Cameron MANDERSON <cameronmanderson@gmail.com> (Phruts contributor)
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class ActionConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
	 * Freeze the configuration of this action.
	 */
    public function freeze()
    {
        $this->configured = true;

        $fconfigs = $this->findForwardConfigs();
        foreach ($fconfigs as $fconfig) {
            $fconfig->freeze();
        }
    }

    /**
	 * The module configuration with which we are associated.
	 *
	 * @var ModuleConfig
	 */
    protected $moduleConfig = null;

    /**
	 * The module configuration with which we are associated.
	 *
	 * @return ModuleConfig
	 */
    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    /**
	 * The module configuration with which we are associated.
	 *
	 * @param ModuleConfig $moduleConfig
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setModuleConfig(\Phruts\Config\ModuleConfig $moduleConfig)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->moduleConfig = $moduleConfig;
    }

    /**
	 * The request-scope or session-scope attribute name under which our
	 * form bean is accessed, if it is different from the form bean's
	 * specified name.
	 *
	 * @var string
	 */
    protected $attribute = null;

    /**
	 * Return the request-scope or session-scope attribute name under which our
	 * form bean is accessed, if it is different from the form bean's
	 * specified name.
	 *
	 * @return string Attribute name under which our form bean is accessed.
	 */
    public function getAttribute()
    {
        if (is_null($this->attribute)) {
            return $this->name;
        } else {
            return $this->attribute;
        }
    }

    /**
	 * Set the request-scope or session-scope attribute name under which our
	 * form bean is accessed, if it is different from the form bean's
	 * specified name.
	 *
	 * @param string $attribute The request-scope or session-scope attribute
	 * name under which our form bean is accessed.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setAttribute($attribute)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->attribute = (string) $attribute;
    }

    /**
	 * Context-relative path of the web application resource that will process
	 * this request via RequestDispatcher->doForward(), instead of
	 * instantiating and calling the Action class specified by "type".
	 *
	 * Exactly one of forward, include, or type must be specified.
	 *
	 * @var string
	 */
    protected $forward = null;

    /**
	 * Return context-relative path of the web application resource that will
	 * process this request.
	 *
	 * @return string Context-relative path of the web application resource that
	 * will process this request.
	 */
    public function getForward()
    {
        return $this->forward;
    }

    /**
	 * Set the context-relative path of the web application resource that will
	 * process this request.
	 *
	 * Exactly one of forward, include or type must be specified.
	 *
	 * @param string $forward Context-relative path of the web application
	 * resource that will process this request.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setForward($forward)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->forward = (string) $forward;
    }

    /**
	 * Context-relative path of the web application resource that will process
	 * this request via RequestDispatcher->doInclude(), instead of
	 * instantiating and calling the Action class specified by "type".
	 *
	 * Exactly one of forward, include, or type must be specified.
	 *
	 * @var string
	 */
    protected $include = null;

    /**
	 * Context-relative path of the web application resource that will process
	 * this request.
	 *
	 * @return string Context-relative path of the web application resource that
	 * will process this request.
	 */
    public function getInclude()
    {
        return $this->include;
    }

    /**
	 * Set context-relative path of the web application resource that will process
	 * this request.
	 *
	 * Exactly one of forward, include or type must be specified.
	 *
	 * @param string $include Context-relative path of the web application
	 * resource that will process this request.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setInclude($include)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->include = (string) $include;
    }

    /**
	 * Context-relative path of the input form to which control should be
	 * returned if a validation error is encountered.
	 *
	 * Required if "name" is specified and the input bean returns
	 * validation errors.
	 *
	 * @var string
	 */
    protected $input = null;

    /**
	 * Get the context-relative path of the input form to which control should
	 * be returned if a validation error is encountered.
	 *
	 * @return string Context-relative path of the input form to which control
	 * should be returned if a validation error is encountered.
	 */
    public function getInput()
    {
        return $this->input;
    }

    /**
	 * Set the context-relative path of the input form to which control should
	 * be returned if a validation error is encountered.
	 *
	 * Required if "name" is specified and the input bean returns validation
	 * errors.
	 *
	 * @param string $input Context-relative path of the input form to which
	 * control should be returned if a validation error is encountered.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setInput($input)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->input = (string) $input;
    }

    /**
	 * Name of the form bean, if any, associated with this Action.
	 *
	 * @var string
	 */
    protected $name = null;

    /**
	 * Return name of the form bean, if any, associated with this Action.
	 *
	 * @return string
	 */
    public function getName()
    {
        return $this->name;
    }

    /**
	 * Set the name of the form bean, if any, associated with this Action.
	 *
	 * @param string $name Name of the bean associated with this Action.
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
	 * General purpose configuration parameter that can be used to pass
	 * extra information to the Action instance selected by this Action.
	 *
	 * PHruts does not itself use this value in any way.
	 *
	 * @var string
	 */
    protected $parameter = null;

    /**
	 * Return general purpose configuration parameter that can be used to pass
	 * extra information to the Action instance selected by this Action.
	 *
	 * PHruts does not itself use this value in any way.
	 *
	 * @return string
	 */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
	 * General purpose configuration parameter that can be used to pass extra
	 * information to the Action instance selected by this Action.
	 *
	 * PHruts does not itself use this value in any way.
	 *
	 * @param string $parameter General purpose configuration parameter.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setParameter($parameter)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->parameter = (string) $parameter;
    }

    /**
	 * Context-relative path of the submitted request, starting with a
	 * slash ("/") character.
	 *
	 * @var string
	 */
    protected $path = null;

    /**
	 * Return context-relative path of the submitted request, starting with
	 * a slash ("/") character.
	 *
	 * @return string
	 */
    public function getPath()
    {
        return $this->path;
    }

    /**
	 * Set context-relative path of the submitted request, starting with
	 * a slash ("/") character.
	 *
	 * @param string $path Context-relative path of the submitted request.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setPath($path)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $path = (string) $path;
        // Add in the starting slash if missing
        if(substr($path, 0, 1) != '/') $path = '/' . $path;
        $this->path = $path;
    }

    /**
	 * Prefix used to match request parameter names to form bean property
	 * names, if any.
	 *
	 * @var string
	 */
    protected $prefix = null;

    /**
	 * Return prefix used to match request parameter names to form bean
	 * property names, if any.
	 *
	 * @return string
	 */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
	 * Set prefix used to match request parameter names to form bean
	 * property names, if any.
	 *
	 * @param string $prefix Prefix used to match request parameter names to
	 * form bean property names, if any.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setPrefix($prefix)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->prefix = (string) $prefix;
    }

    /**
	 * Comma-delimited list of security role names allowed to request
	 * this Action.
	 *
	 * @var string
	 */
    protected $roles = null;

    /**
	 * @return string
	 */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
	 * @param string $roles
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setRoles($roles)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $roles = (string) $roles;
        $this->roles = $roles;

        $list = array ();
        while (true) {
            $comma = strpos($roles, ',');
            if ($comma === false) {
                break;
            }
            $list[] = trim(substr($roles, 0, $comma));
            $roles = substr($roles, $comma +1);
        }

        $roles = trim($roles);
        if (strlen($roles) > 0) {
            $list[] = $roles;
        }

        $this->roleNames = $list;
    }

    /**
	 * The set of security role names used to authorize access to this
	 * Action, as an array for faster access.
	 *
	 * @var array
	 */
    protected $roleNames = array ();

    /**
	 * Get array of security role names used to authorize access to this Action.
	 *
	 * @return array
	 */
    public function getRoleNames()
    {
        return $this->roleNames;
    }

    /**
	 * Identifier of the scope ("request" or "session") within which
	 * our form bean is accessed, if any.
	 *
	 * @var string
	 */
    protected $scope = 'session';

    /**
	 * Get the scope ("request" or "session") within which our form bean
	 * is accessed, if any.
	 *
	 * @return string
	 */
    public function getScope()
    {
        return $this->scope;
    }

    /**
	 * Set the scope ("request" or "session") within which our form bean
	 * is accessed, if any.
	 *
	 * @param string $scope Scope ("request" or "session") within which our form
	 * bean is accessed, if any.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setScope($scope)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->scope = (string) $scope;
    }

    /**
	 * Suffix used to match request parameter names to form bean property
	 * names, if any.
	 *
	 * @var string
	 */
    protected $suffix = null;

    /**
	 * Return suffix used to match request parameter names to form bean property
	 * names, if any.
	 *
	 * @return string
	 */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
	 * Set suffix used to match request parameter names to form bean property
	 * names, if any.
	 *
	 * @param string $suffix Suffix used to match request parameter names to
	 * form bean property names, if any.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setSuffix($suffix)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->suffix = (string) $suffix;
    }

    /**
	 * Fully qualified PHP class name of the Action class to be used to
	 * process requests for this mapping if the forward and include properties
	 * are not set.
	 *
	 * Exactly one of forward, include, or type must be specified.
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
	 * Indicate Action be configured as default one for this module, when true.
	 *
	 * @var boolean
	 */
    protected $unknown = false;

    /**
	 * Determine whether Action is configured as the default one for this module.
	 *
	 * @return boolean
	 */
    public function getUnknown()
    {
        return $this->unknown;
    }

    /**
	 * Set whether Action is configured as the default one for this module.
	 *
	 * @param boolean $unknown Indicates Action is configured as the default
	 * one for this module, when true.
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setUnknown($unknown)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $temp = strtolower($unknown);
        if ($temp === 'false' || $temp === 'no') {
            $this->unknown = false;
        } else {
            $this->unknown = (boolean) $unknown;
        }
    }

    /**
	 * Should the validate method of the form bean associated with this action
	 * be called?
	 *
	 * @var boolean
	 */
    protected $validate = true;

    /**
	 * @return boolean
	 */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
	 * @param boolean $validate
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function setValidate($validate)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $temp = strtolower($validate);
        if ($temp === 'false' || $temp === 'no') {
            $this->validate = false;
        } else {
            $this->validate = (boolean) $validate;
        }
    }

    /**
	 * The set of local forward configurations for this action, if any,
	 * keyed by the name property.
	 *
	 * @var array
	 */
    protected $forwards = array ();

    /**
	 * Add a new ForwardConfig instance to the set of global forwards
	 * associated with this action.
	 *
	 * @param ForwardConfig $config The new configuration instance
	 * to be added
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function addForwardConfig(\Phruts\Config\ForwardConfig $config)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        $this->forwards[$config->getName()] = $config;
    }

    /**
	 * Return the forward configuration for the specified key, if any;
	 * otherwise return null.
	 *
	 * @param string $name Name of the forward configuration to return
	 * @return ForwardConfig
	 */
    public function findForwardConfig($name)
    {
        $name = (string) $name;

        if (array_key_exists($name, $this->forwards)) {
            return $this->forwards[$name];
        } else {
            return $this->moduleConfig->findForwardConfig($name);
        }
    }

    /**
	 * Return all forward configurations for this Action.
	 *
	 * If there are none, a zero-length array is returned.
	 *
	 * @return array
	 */
    public function findForwardConfigs()
    {
        $temps = array_merge($this->moduleConfig->findForwardConfigs(), $this->forwards);

        return array_values($temps);
    }

    /**
	 * Remove the specified forward configuration instance.
	 *
	 * @param ForwardConfig $config ForwardConfig instance to be removed
	 * @throws \Phruts\Exception\IllegalState
	 */
    public function removeForwardConfig(\Phruts\Config\ForwardConfig $config)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState('Configuration is frozen');
        }
        unset ($this->forwards[$config->getName()]);
    }

    /**
     * The set of exception handling configurations for this
     * action, if any, keyed by the <code>type</code> property.
     */
    protected $exceptions = array();

    /**
     * Add a new <code>ExceptionConfig</code> instance to the set associated
     * with this action.
     *
     * @param config The new configuration instance to be added
     *
     * @exception \Phruts\Exception\IllegalState if this module configuration
     *  has been frozen
     */
    public function addExceptionConfig(\Phruts\Config\ExceptionConfig $config)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState("Configuration is frozen");
        }
        $this->exceptions[$config->getType()] = $config;
    }

    /**
     * Remove the specified exception configuration instance.
     *
     * @param config ExceptionConfig instance to be removed
     *
     * @exception \Phruts\Exception\IllegalState if this module configuration
     *  has been frozen
     */
    public function removeExceptionConfig(\Phruts\Config\ExceptionConfig $config)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalState("Configuration is frozen");
        }
        unset($this->exceptions[config.getType()]);
    }

    /**
     * Return the exception configuration for the specified type, if any;
     * otherwise return <code>null</code>.
     * @return ExceptionConfig
     * @param type Exception class name to find a configuration for
     */
    public function findExceptionConfig($type)
    {
        if(!empty($this->exceptions[$type])) return $this->exceptions[$type];

        return null;
    }

    /**
     * Return the exception configurations for this action.  If there
     * are none, a zero-length array is returned.
     */
    public function findExceptionConfigs()
    {
        return $this->exceptions;
    }

    /**
	 * Return a string representation of this object.
	 *
	 * @return string
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\ActionConfig[';
        $sb .= 'path=' . var_export($this->path, true);
        if (!is_null($this->forward)) {
            $sb .= ',forward=' . var_export($this->forward, true);
        }
        if (!is_null($this->include)) {
            $sb .= ',include=' . var_export($this->include, true);
        }
        if (!is_null($this->type)) {
            $sb .= ',type=' . var_export($this->type, true);
        }
        if (!is_null($this->name)) {
            $sb .= ',name=' . var_export($this->name, true);
        }
        if (!is_null($this->scope)) {
            $sb .= ',scope=' . var_export($this->scope, true);
        }
        if (!is_null($this->attribute)) {
            $sb .= ',attribute=' . var_export($this->attribute, true);
        }
        if (!is_null($this->prefix)) {
            $sb .= ',prefix=' . var_export($this->prefix, true);
        }
        if (!is_null($this->suffix)) {
            $sb .= ',suffix=' . var_export($this->suffix, true);
        }
        $sb .= ',validate=' . var_export($this->validate, true);
        if (!is_null($this->input)) {
            $sb .= ',input=' . var_export($this->input, true);
        }
        if (!is_null($this->roles)) {
            $sb .= ',roles=' . var_export($this->roles, true);
        }
        if (!is_null($this->parameter)) {
            $sb .= ',parameter=' . var_export($this->parameter, true);
        }
        $sb .= ',unknown=' . var_export($this->unknown, true);
        $sb .= ']';

        return $sb;
    }
}
