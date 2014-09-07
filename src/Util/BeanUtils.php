<?php
namespace Phruts\Util;

/**
 * Utility methods for populating PHPBeans properties via reflection.
 */
class BeanUtils
{
    /**
	 * Populate the PHPBeans properties of the specified bean, based on
	 * the specified name/value pairs.
	 *
	 * @param object $bean
	 * @param array $properties
	 * @throws \Phruts\Exception\IllegalArgument - If the bean object has not been
	 * specified
	 */
    public static function populate($bean, array $properties)
    {
        if (is_null($bean)) {
            throw new \Phruts\Exception\IllegalArgument('Bean object to populate must be not null.');
        }

        // Loop through the property name/value pairs to be set
        $reflection = new \ReflectionClass(get_class($bean));
        foreach ($properties as $name => $value) {
            // Perform the assignement for this property
            $reflectionProperty = null;
            if (property_exists($bean, $name)) {
                $reflectionProperty = $reflection->getProperty($name);
            }

            if (!empty($reflectionProperty) && $reflectionProperty->isPublic()) {
                $bean->$name = $value;
            } else {
                $propertySetter = 'set' . ucfirst($name);
                if (method_exists($bean, $propertySetter)) {
                    $bean->$propertySetter ($value);
                }
            }
        }
    }
}
