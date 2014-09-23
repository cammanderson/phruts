<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Config\Digester;

/**
 * An object creation factory which creates form property config instances.
 */
final class FormPropertyConfigFactory extends \Phigester\AbstractObjectCreationFactory
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
            $className = '\Phruts\Config\FormPropertyConfig';
        }

        // Instantiate the new object and return it
        $config = null;
        try {
            $config = \Phruts\Util\ClassLoader::newInstance($className, '\Phruts\Config\FormPropertyConfig');
        } catch (\Exception $e) {
            $this->digester->getLogger()->error('FormPropertyConfigFactory->createObject(): ' . $e->getMessage());
        }

        return $config;
    }
}
