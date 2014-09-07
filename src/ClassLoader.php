<?php
namespace Phruts;

/**
 * The ClassLoader is a way of customizing the way PHP gets its classes
 * and loads them into memory.
 *
 */
class ClassLoader
{
    /**
	 * The PHP extension file used to store PHP class.
	 *
	 * @var string
	 */
    protected static $phpExtensionFile = '.php';

    /**
	 * @param string $phpExtensionFile The PHP extension file used to store PHP
	 * class
	 */
    public static function setPhpExtensionFile($phpExtensionFile)
    {
        self::$phpExtensionFile = (string) $phpExtensionFile;
    }

    /**
	 * Check if a fully qualified class name is valid.
	 *
	 * @param string $name Fully qualified name of a class (with packages)
	 * @return boolean Return true if the class name is valid
	 */
    public static function isValidClassName($name)
    {
        // TODO: Update to match the naming conventions of PSR/Namespace/PHP53
        $classPattern = '`^((([A-Z]|[a-z]|[0-9]|\_|\-)+\:{2})*)';
        $classPattern .= '(([A-Z]|[a-z]){1}([A-Z]|[a-z]|[0-9]|\_)*)$`';

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
        // TODO: Update to match the naming conventions of PSR/Namespace/PHP53
        $name = str_replace('::', '_', $name);
        $lastDot = strrpos($name, '::');
        if ($lastDot === false) {
            $className = $name;
        } else {
            $className = substr($name, - (strlen($name) - $lastDot -2));
        }

        return $className;
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
        $className = self::getClassName($class);
        $classFromName = self::getClassName($class);
        if ($className == $classFromName) {
            return true;
        } else {
            // Get reflection information of the class from
            $reflectionClass = new \ReflectionClass($classFromName);
            if ($reflectionClass->isSubclassOf($className)) {
                return true;
            } else {
                return false;
            }
        }
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

        // Get only the class name
        $className = self::getClassName($name);

        // Have we already loaded this class?
        if (class_exists($className, true)) {
            return $className;
        } else {
            // TODO: Remove the loading of the class, this is to be done via autoloader
            // Try to load the class
            $pathClassFile = str_replace(array('::', '_'), '/', $name) . self::$phpExtensionFile;
            $fileExists = @fopen($pathClassFile, 'r', true);
            if ($fileExists && fclose($fileExists) && require_once $pathClassFile) { // Removed the '@include_once' as we want to see the FATAL
 if (class_exists($className, false)) { return $className;
                } else {
                    $msg = '"' . $name . '" class does not exist.';
                    throw new \Phruts\Exception\ClassNotFoundException($msg);
                }
            } else {
                $msg = 'PHP class file "' . $pathClassFile . '" does not exist.';
                throw new \Phruts\Exception\ClassNotFoundException($msg);
            }
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
        $class = new \ReflectionClass($className);
        if ($class->isAbstract()) {
            throw new \Phruts\Exception\InstantiationException('Cannot instantiate abstract class.');
        }
        if (!is_null($parent) && ($className != $parent) && !$class->isSubclassOf($parent)) {
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
        $instance = new $className();

        return $instance;
    }
}
