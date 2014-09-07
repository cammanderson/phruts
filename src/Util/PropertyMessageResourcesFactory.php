<?php

namespace Phruts\Util;

/**
 * Factory for PropertyMessageResources instances.
 */
class PropertyMessageResourcesFactory extends MessageResourcesFactory
{
    /**
	 * Create and return a newly instansiated MessageResources.
	 *
	 * @param string $config Configuration parameter(s) for the requested bundle
	 * @return MessageResources
	 */
    public function createResources($config)
    {
        $config = (string) $config;

        return new \Phruts\Util\PropertyMessageResources($config, $this->returnNull);
    }
}
