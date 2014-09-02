<?php

namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of a <forward> element
 * from a PHruts application configuration file.
 *
 * @author Olivier HENRY <oliv.henry@gmail.com> (PHP5 port of Struts)
 * @author John WILDENAUER <jwilde@users.sourceforge.net> (PHP4 port of Struts) */
class ForwardConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
	 * Is the redirect to be context relative
	 * @var boolean
	 */
    protected $contextRelative = false;

    protected $nextActionPath = null;

    /**
	 * Freeze the configuration of this component.
	 */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
	 * The unique identifier of this forward, which is used to reference it
	 * in Action classes.
	 *
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
	 * @throws \Serphlet\Exception_IllegalState
	 */
    public function setName($name)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception_IllegalState('Configuration is frozen');
        }
        $this->name = (string) $name;
    }

    /**
	 * Set the context relative
	 * @param string $contextRelative
	 * @throws \Serphlet\Exception_IllegalState
	 */
    public function setContextRelative($contextRelative)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception_IllegalState('Configuration is frozen');
        }
        $temp = strtolower($contextRelative);
        if ($temp === 'false' || $temp === 'no') {
            $this->contextRelative = false;
        } else {
            $this->contextRelative = (boolean) $contextRelative;
        }
    }

    /**
	 * Is the forward to be context relative to the current servlet
	 * @return boolean true for context relative
	 */
    public function getContextRelative()
    {
        return $this->contextRelative;
    }

    /**
	 * The URL to which this ForwardConfig entry points.
	 *
	 * @var string
	 */
    protected $path = null;

    /**
	 * @return string
	 */
    public function getPath()
    {
        return $this->path;
    }

    /**
	 * @param string $path
	 * @throws \Serphlet\Exception_IllegalState
	 */
    public function setPath($path)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception_IllegalState('Configuration is frozen');
        }
        $this->path = (string) $path;
    }

    /**
	 * Should a redirect be used to transfer control to the specified path?
	 *
	 * @var boolean
	 */
    protected $redirect = false;

    /**
	 * @return boolean
	 */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
	 * @param boolean $redirect
	 * @throws \Serphlet\Exception_IllegalState
	 */
    public function setRedirect($redirect)
    {
        if ($this->configured) {
            throw new \Serphlet\Exception_IllegalState('Configuration is frozen');
        }
        $temp = strtolower($redirect);
        if ($temp === 'false' || $temp === 'no') {
            $this->redirect = false;
        } else {
            $this->redirect = (boolean) $redirect;
        }
    }

    public function getNextActionPath()
    {
        return $this->nextActionPath;
    }
    public function setNextActionPath($path)
    {
        $this->nextActionPath = $path;
    }

    /**
	 * Return a String representation of this object.
	 *
	 * @return string
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\ForwardConfig[';
        $sb .= 'name=' . var_export($this->name, true);
        $sb .= ',path=' . var_export($this->path, true);
        $sb .= ',redirect=' . var_export($this->redirect, true);
        $sb .= ']';

        return $sb;
    }
}
