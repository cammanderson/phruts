<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Config\Digester;

/**
 * An object creation factory which creates message resources config instances.
 */
final class MessageResourcesConfigFactory extends \Phigester\AbstractObjectCreationFactory
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
            $className = '\Phruts\Config\MessageResourcesConfig';
        }

        // Instantiate the new object and return it
        $config = null;
        try {
            $config = \Phruts\Util\ClassLoader::newInstance($className, '\Phruts\Config\MessageResourcesConfig');
        } catch (\Exception $e) {
            $this->digester->getLogger()->error('\Phruts\Config\MessageResourcesConfigFactory->createObject(): ' . $e->getMessage());
        }

        return $config;
    }
}
