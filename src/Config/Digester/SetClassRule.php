<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Config\Digester;

/**
 * Class that sets the name of the class to use when creating action config
 * instances.
 *
 * The value is set on the object on the top of the stack, which
 * must be a ModuleConfig.
 *
 */
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
