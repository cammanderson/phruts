<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Config\Digester;

/**
 * Class that records the name and value of a configuration property to
 * be used in configuring a PlugIn instance when instantiated.
 */
final class PlugInSetPropertyRule extends \Phigester\AbstractRule
{
    /**
     * @param array $attributes
     */
    public function begin(array $attributes)
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
