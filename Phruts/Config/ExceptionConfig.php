<?php

namespace Phruts\Config;

/**
 * <p>A PHPBean representing the configuration information of an
 * <code>&lt;exception&gt;</code> element from a Struts
 * configuration file.</p>
 *
 * @author Cameron Manderson (Contributor from Aloi)
 * @author Craig R. McClanahan
 * @since Struts 1.1 */
class ExceptionConfig
{
    /**
     * Has this component been completely configured?
     */
    protected $configured = false;

    /**
     * The servlet context attribute under which the message resources bundle
     * to be used for this exception is located.  If not set, the default
     * message resources for the current module is assumed.
     */
    protected $bundle = null;
    public function getBundle()
    {
        return ($this->bundle);
    }
    public function setBundle($bundle)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception\IllegalState("Configuration is frozen");
        }
        $this->bundle = $bundle;
    }

    /**
     * The fully qualified Java class name of the exception handler class
     * which should be instantiated to handle this exception.
     */
    protected $handler = "\Phruts\Action\ExceptionHandler";
    public function getHandler()
    {
        return ($this->handler);
    }
    public function setHandler($handler)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception\IllegalState("Configuration is frozen");
        }
        $this->handler = $handler;
    }

    /**
     * The message resources key specifying the error message
     * associated with this exception.
     */
    protected $key = null;

    public function getKey()
    {
        return ($this->key);
    }

    public function setKey($key)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception\IllegalState("Configuration is frozen");
        }
        $this->key = $key;
    }

    /**
     * The module-relative path of the resource to forward to if this
     * exception occurs during an <code>Action</code>.
     */
    protected $path = null;

    public function getPath()
    {
        return ($this->path);
    }
    public function setPath($path)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception\IllegalState("Configuration is frozen");
        }
        $this->path = $path;
    }

    /**
     * The scope in which we should expose the \Phruts\Action\Error for this exception
     * handler.
     */
    protected $scope = "request";
    public function getScope()
    {
        return ($this->scope);
    }
    public function setScope($scope)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception\IllegalState("Configuration is frozen");
        }
        $this->scope = $scope;
    }

    /**
     * The fully qualified Java class name of the exception that is to be
     * handled by this handler.
     */
    protected $type = null;

    public function getType()
    {
        return ($this->type);
    }

    public function setType($type)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception\IllegalState("Configuration is frozen");
        }
        $this->type = $type;
    }

    // --------------------------------------------------------- Public Methods


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
        $sb = "\Phruts\Config\ExceptionConfig[";
        $sb .= "type=";
        $sb .= $this->type;
        if ($this->bundle != null) {
            $sb .= ",bundle=";
            $sb .= $this->bundle;
        }
        $sb .= ",key=";
        $sb .= $this->key;
        $sb .= ",path=";
        $sb .= $this->path;
        $sb .= ",scope=";
        $sb .= $this->scope;
        $sb .= "]";

        return $sb;
    }
}
