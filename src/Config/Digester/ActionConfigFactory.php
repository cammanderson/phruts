<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Config\Digester;

/**
 * An object creation factory which creates action config instances, taking
 * into account the default class name, which may have been specified on
 * the parent element and which is made available through the object on
 * the top of the stack, which must be a ModuleConfig.
 *
 */
final class ActionConfigFactory extends \Phigester\AbstractObjectCreationFactory
{
    /**
     * @param  array  $attributes
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
            $actionConfig = \Phruts\Util\ClassLoader::newInstance($className, '\Phruts\Config\ActionConfig');
        } catch (\Exception $e) {
            $this->digester->getLogger()->error('\Phruts\Config\ActionConfigFactory->createObject(): ' . $e->getMessage());
        }

        return $actionConfig;
    }
}
