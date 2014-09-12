<?php
namespace Phruts\Util;

/**
 * The ClassLoader is a way of customizing the way PHP gets its classes
 * and loads them into memory.
 *
 */
class ClassLoader
{

    /**
	 * Check if a fully qualified class name is valid.
	 *
	 * @param string $name Fully qualified name of a class (with packages)
	 * @return boolean Return true if the class name is valid
	 */
    public static function isValidClassName($name)
    {
        // Match the naming of PSR/Namespace/PHP53
        $classPattern = '#^\\\\((([a-z\x7f-\xff][a-z0-9_\x7f-\xff]*)\\\\)*)';
        $classPattern .= '([a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*)$#iU';

        return (boolean) preg_match($classPattern, $name);
    }

    /**
	 * Return only the class name of a fully qualified name.
	 *
	 * @param string $name Fully qualified name of a class (with packages)
	 * @return string
	 */
    public static function getClassName($name)
    {
        try {
            $class = new \ReflectionClass($name);

            return $class->getShortName();
        } catch (\Exception $e) {
            throw new \Phruts\Exception\ClassNotFoundException($e->getMessage());
        }
    }

    /**
	 * Discover whether an instance of the class from would be an instance of the
	 * class as well.
	 *
	 * @param string $class
	 * @param string $classFrom
	 * @return boolean Whether an instance of classFrom would be an instance of
	 * class as well
	 */
    public static function classIsAssignableFrom($class, $classFrom)
    {
        return $class == $classFrom || is_subclass_of($class, $classFrom);
    }

    /**
	 * Load a class.
	 *
	 * @param string $name The fully qualified name of the class (with packages)
	 * @return string Return the only class name
	 * @throws \Phruts\Exception\IllegalArgumentException - If the class name is not valid
	 * @throws \Phruts\Exception\ClassNotFoundException - If the class cannot be found
	 */
    public static function loadClass($name)
    {
        //Check if the fully qualified class name is valid
        if (!self::isValidClassName($name)) {
            throw new \Phruts\Exception\IllegalArgumentException('Illegal class name ' . $name . '.');
        }

        // Have we already loaded this class?
        if (class_exists($name, true)) {
            // Get only the class name
            $className = self::getClassName($name);

            return $className;
        } else {
            $msg = 'PHP class "' . $name . '" does not exist.';
            throw new \Phruts\Exception\ClassNotFoundException($msg);
        }
    }

    /**
	 * Get a new instance of a class by calling the no-required-argument
	 * constructor.
	 *
	 * @param string $name The fully qualified name of the class (with packages)
	 * @param string $parent If is set, the class must be a subclass of the class
	 * which name is equal to "parent"
	 * @return $object New instance of the class
	 * @throws \Phruts\Exception\IllegalArgumentException - If the class name is not valid
	 * @throws \Phruts\Exception\ClassNotFoundException - If the class cannot be found
	 * @throws \Phruts\Exception\InstantiationException - If there is not a
	 * no-required-argument constructor for this class.
	 */
    public static function newInstance($name, $parent = null)
    {
        try {
            // Load the class and get only the class name
            $className = self::loadClass($name);
        } catch (\Exception $e) {
            throw $e;
        }

        // Get reflection information of the class
        $class = new \ReflectionClass($name);
        if ($class->isAbstract()) {
            throw new \Phruts\Exception\InstantiationException('Cannot instantiate abstract class.');
        }
        if (!is_null($parent) && ($name != $parent) && !$class->isSubclassOf($parent)) {
            throw new \Phruts\Exception\InstantiationException('"' . $name . '" is not a subclass of "' . $parent . '".');
        }

        // Get reflection information of the constructor
        $constructor = $class->getConstructor();
        if (!is_null($constructor)) {
            // Check accessibility of the constructor
            if (!$constructor->isPublic()) {
                throw new \Phruts\Exception\InstantiationException('You are not allowed to access' . ' the no-required-argument constructor for this class.');
            }
            // Check the no-required-argument constructor
            if ($constructor->getNumberOfRequiredParameters() > 0) {
                throw new \Phruts\Exception\InstantiationException('There is not a no-required-argument constructor for this class.');
            }
        }

        // Create the new instance of the class
        $instance = new $name();

        return $instance;
    }
}
