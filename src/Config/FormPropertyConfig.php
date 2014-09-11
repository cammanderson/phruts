<?php

namespace Phruts\Config;

/**
 * <p>A PHPBean representing the configuration information of a <code>&lt;form-
 * property&gt;</code> element in a Phruts configuration file.<p>
 * @since Struts 1.1
 */
class FormPropertyConfig
{
    const TYPE_BOOLEAN = 'FormPropertyConfigBoolean';
    const TYPE_STRING = 'FormPropertyConfigString';
    const TYPE_FLOAT = 'FormPropertyConfigFloat';
    const TYPE_INTEGER = 'FormPropertyConfigInteger';
    const TYPE_ARRAY = 'FormPropertyConfigArray';

    protected $index = false;

    // ----------------------------------------------------------- Constructors


    /**
     * Constructor that preconfigures the relevant properties.
     *
     * @param string name Name of this property
     * @param string type Fully qualified class name of this property
     * @param string initial Initial value of this property (if any)
     * @param size Size of the array to be created if this property is an  array
     * with no defined initial value
     */
    public function __construct()
    {
//        $this->setName($name);
//        $this->setType($type);
//        $this->setInitial($initial);
//        $this->setSize($size);
    }

    // ----------------------------------------------------- Instance Variables


    /**
     * Has this component been completely configured?
     */
    protected $configured = false;

    // ------------------------------------------------------------- Properties


    /**
     * String representation of the initial value for this property.
     */
    protected $initial = null;

    public function getInitial()
    {
        return ($this->initial);
    }

    public function setInitial($initial)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException("Configuration is frozen");
        }
        $this->initial = $initial;
    }

    /**
     * The PHPBean property name of the property described by this element.
     */
    protected $name = null;

    public function getName()
    {
        return ($this->name);
    }

    public function setName($name)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException("Configuration is frozen");
        }
        $this->name = $name;
    }

    /**
     * <p>The size of the array to be created if this property is an array
     * type and there is no specified <code>initial</code> value.  This
     * value must be non-negative.</p>
     *
     * @since Struts 1.1
     */
    protected $size = 0;
    public function getSize()
    {
        return ($this->size);
    }
    public function setSize($size)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException("Configuration is frozen");
        }
        if ($this->size < 0) {
            throw new \Phruts\Exception\IllegalArgumentException("size < 0");
        }
        $this->size = $size;
    }

    /**
     * The fully qualified PHP class name of the implementation class
     * of this bean property, optionally followed by <code>[]</code> to
     * indicate that the property is indexed.
     */
    protected $type = null;

    public function getType()
    {
        return ($this->type);
    }

    public function setType($type)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException("Configuration is frozen");
        }
        $this->type = $type;
    }

    /**
     * Return a Class corresponds to the value specified for the
     * <code>type</code> property.
     */
    public function getTypeClass()
    {
        // Identify the base class (in case an array was specified)
        $baseType = $this->getType();
        $indexed = false;
//        if (baseType.endsWith("[]")) {
//            baseType = baseType.substring(0, baseType.length() - 2);
//            indexed = true;
//        }

        // Construct an appropriate Class instance for the base class
        $baseClass = null;
        if ($baseType == "boolean") {
            $baseClass = \Phruts\Config\FormPropertyConfig::TYPE_BOOLEAN;
        } elseif ($baseType == "float") {
            $baseClass = \Phruts\Config\FormPropertyConfig::TYPE_FLOAT;
        } elseif ($baseType == "int") {
            $baseClass = \Phruts\Config\FormPropertyConfig::TYPE_INTEGER;
        } elseif ($baseType == "string" || !trim($baseType)) {
            $baseClass = \Phruts\Config\FormPropertyConfig::TYPE_STRING;
        } elseif ($baseType == "array") {
            $baseClass = \Phruts\Config\FormPropertyConfig::TYPE_ARRAY;
        } else {
            try {
                if (substr($baseType, strlen($baseType) - 2) == '[]') {
                    $baseType = substr($baseType, 0, strlen($baseType) -2);
                    $this->indexed = true;
                }
                \Phruts\Util\ClassLoader::loadClass($baseType);
            } catch (\Exception $e) {
                $baseClass = null;
            }
        }

        // Return the base class or an array appropriately
        return ($baseClass);
    }

    // --------------------------------------------------------- Public Methods


    /**
     * <p>Return an object representing the initial value of this property.
     * This is calculated according to the following algorithm:</p>
     * <ul>
     * <li>If the value you have specified for the <code>type</code>
     *     property represents an array (i.e. it ends with "[]"):
     *     <ul>
     *     <li>If you have specified a value for the <code>initial</code>
     *         property, <code>ConvertUtils.convert()</code> will be
     *         called to convert it into an instance of the specified
     *         array type.</li>
     *     <li>If you have not specified a value for the <code>initial</code>
     *         property, an array of the length specified by the
     *         <code>size</code> property will be created.  Each element
     *         of the array will be instantiated via the zero-args constructor
     *         on the specified class (if any).  Otherwise, <code>null</code>
     *         will be returned.</li>
     *     </ul></li>
     * <li>If the value you have specified for the <code>type</code>
     *     property does not represent an array:
     *     <ul>
     *     <li>If you have specified a value for the <code>initial</code>
     *         property, <code>ConvertUtils.convert()</code>
     *         will be called to convert it into an object instance.</li>
     *     <li>If you have not specified a value for the <code>initial</code>
     *         attribute, Struts will instantiate an instance via the
     *         zero-args constructor on the specified class (if any).
     *         Otherwise, <code>null</code> will be returned.</li>
     *     </ul></li>
     * </ul>
     */
    public function initial()
    {
        $initialValue = null;
        try {
            $className = $this->getTypeClass();
            switch ($className) {
                case \Phruts\Config\FormPropertyConfig::TYPE_ARRAY:
                    if ($this->initial != null) {
                        $initialValue = explode(',', $this->initial);
                    } else {
                        $initialValue = array();
                    }
                    break;
                case \Phruts\Config\FormPropertyConfig::TYPE_BOOLEAN:
                    $value = $this->initial;
                    if ($value == null)
                        $initialValue = true;
                    elseif (strtolower($value) == "true")
                        $initialValue = true;
                    elseif (strtolower($value) == "yes")
                        $initialValue = true;
                    else
                        $initialValue = false;
                    break;
                case \Phruts\Config\FormPropertyConfig::TYPE_STRING:
                    $initialValue = $this->initial;
                    break;
                case \Phruts\Config\FormPropertyConfig::TYPE_FLOAT:
                    if ($this->initial != null) {
                        $initialValue = floatval($this->initial);
                    } else $initialValue = floatval(0);
                    break;
                case \Phruts\Config\FormPropertyConfig::TYPE_INTEGER:
                    if ($this->initial != null) {
                        $initialValue = intval($this->initial);
                    } else $initialValue = intval(0);
                    break;
                default:
                    // Create the class
                    if ($this->indexed) {
                        // Place a indexed set of objects into the form
                        $initialValue = array();
                        $size = intval($this->initial);
                        if ($size > 0) {
                            for ($x = 0; $x < $size; $x++) {
                                $intialValue[] = \Phruts\Util\ClassLoader::newInstance($className);
                            }
                        }
                    } else {
                        $initialValue = \Phruts\Util\ClassLoader::newInstance($className);
                    }
                    break;
            }
        } catch (\Exception $e) {
            $initialValue = null;
        }

        return ($initialValue);
    }

    /**
     * Freeze the configuration of this component.
     */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
     * Return a String representation of this object.
     */
    public function __toString()
    {
        $sb = "\Phruts\Config\FormPropertyConfig[";
        $sb .= "name=";
        $sb .= $this->name;
        $sb .= ",type=";
        $sb .= $this->type;
        $sb .= ",initial=";
        $sb .= $this->initial;
        $sb .= "]";

        return $sb;
    }
}
