<?php

namespace Phruts\Config;

/**
 * A PHPBean representing the configuration information of a <controller>
 * element in a PHruts configuration file.
 *
 * // TODO: Add in tmpdir/pagepattern etc
 *
 */
class ControllerConfig
{
    /**
	 * Has this component been completely configured?
	 *
	 * @var boolean
	 */
    protected $configured = false;

    /**
	 * Freeze the configuration of this component.
	 */
    public function freeze()
    {
        $this->configured = true;
    }

    /**
	 * The fully qualified class name of the RequestProcessor
	 * implementation class to be used for this module.
	 *
	 * @var string
	 */
    protected $processorClass = '\Phruts\RequestProcessor';

    /**
	 * @return string
	 */
    public function getProcessorClass()
    {
        return $this->processorClass;
    }

    /**
	 * @param string $processorClass
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setProcessorClass($processorClass)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $this->processorClass = (string) $processorClass;
    }

    /**
	 * The content type and character encoding to be set on each response.
	 *
	 * @var string
	 */
    protected $contentType = 'text/html';

    /**
	 * @return string
	 */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
	 * @param string $contentType
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setContentType($contentType)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $this->contentType = (string) $contentType;
    }

    /**
	 * Should we set no-cache HTTP headers on each response?
	 *
	 * @var boolean
	 */
    protected $nocache = false;

    /**
	 * @return boolean
	 */
    public function getNocache()
    {
        return $this->nocache;
    }

    /**
	 * @param boolean $nocache
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setNocache($nocache)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $temp = strtolower($nocache);
        if ($temp === 'false' || $temp === 'no') {
            $this->nocache = false;
        } else {
            $this->nocache = (boolean) $nocache;
        }
    }

    /**
	 * Should the input property of \Phruts\Config\ActionConfig instances associated with
	 * this module be treated as the name of a corresponding ForwardConfig.
	 *
	 * A false value treats them as a context-relative path.
	 *
	 * @var boolean
	 */
    protected $inputForward = false;

    /**
	 * @return boolean
	 */
    public function getInputForward()
    {
        return $this->inputForward;
    }

    /**
	 * @param boolean $inputForward
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setInputForward($inputForward)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $temp = strtolower($inputForward);
        if ($temp === 'false' || $temp === 'no') {
            $this->inputForward = false;
        } else {
            $this->inputForward = (boolean) $inputForward;
        }
    }

    /**
	 * Should we store a Locale object in the user's session if needed?
	 *
	 * @var boolean
	 */
    protected $locale = true;

    /**
	 * @return boolean
	 */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
	 * @param boolean $locale
	 * @throws \Phruts\Exception\IllegalStateException
	 */
    public function setLocale($locale)
    {
        if ($this->configured) {
            throw new \Phruts\Exception\IllegalStateException('Configuration is frozen');
        }
        $temp = strtolower($locale);
        if ($temp === 'false' || $temp === 'no') {
            $this->locale = false;
        } else {
            $this->locale = (boolean) $locale;
        }
    }

    /**
	 * Return a string representation of this object.
	 *
	 * @return string
	 */
    public function __toString()
    {
        $sb = '\Phruts\Config\ControllerConfig[';
        $sb .= 'processorClass=' . var_export($this->processorClass, true);
        if (!is_null($this->contentType)) {
            $sb .= ',contentType=' . var_export($this->contentType, true);
        }
        if (!is_null($this->nocache)) {
            $sb .= ',nocache=' . var_export($this->nocache, true);
        }
        if (!is_null($this->inputForward)) {
            $sb .= ',inputForward=' . var_export($this->inputForward, true);
        }
        if (!is_null($this->locale)) {
            $sb .= ',locale=' . var_export($this->locale, true);
        }
        $sb .= ']';

        return $sb;
    }
}
