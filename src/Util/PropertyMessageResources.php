<?php

namespace Phruts\Util;

/**
 * Concrete subclass of MessageResources that reads message keys
 * and corresponding strings from named property resources.
 *
 * <p>The config property defines the base property resource name, and must
 * be specified.</p>
 * <p><b>IMPLEMENTATION NOTE:</b> This class trades memory for
 * speed by caching all messages located via generalizing the Locale
 * under the original locale as well. This results in specific messages being
 * stored in the message cache more than once, but improves response time on
 * subsequent requests for the same locale + key combination.</p>
 * <p>This class searches for a message key for property resources in the
 * following sequence:</p>
 * <pre>
 *   config + "_" + localeLanguage + "_" + localeCountry + "_" + localeVariant
 *   config + "_" + localeLanguage + "_" + localeCountry
 *   config + "_" + localeLanguage
 *   config + "_" + default locale
 *   config
 * </pre>
 */
class PropertyMessageResources extends MessageResources
{
    /**
	 * The set of locale keys for which we have already loaded messages, keyed
	 * by the value calculated in localeKey method.
	 *
	 * @var array
	 */
    protected $locales = array ();

    /**
	 * The cache of messages we have accumulated over time, keyed by the
	 * value calculated in messageKey method.
	 *
	 * @var array
	 */
    protected $messages = array ();

    /**
	 * Construct a new PropertyMessageResources according to the
	 * specified parameters.
	 *
	 * @param string $config The configuration parameter for this
	 * MessageResources.
	 * @param boolean $returnNull The returnNull property we should
	 * initialize with
	 */
    public function __construct($config, $returnNull = false)
    {
        parent::__construct($config, $returnNull);

//		if (//self::$log->isDebugEnabled()) {
//			//self::$log->debug('Initializing, config="' . $config . '"');
//		}
    }

    /**
	 * Returns a text message for the specified key, for the specified
	 * Locale.
	 *
	 * A null string result will be returned by this method if no relevant
	 * message resource is found for this key or Locale, if the returnNull
	 * property is set. Otherwise, an appropriate error message will be returned.
	 *
	 * @param Locale $locale The requested message Locale
	 * @param string $key The message key to look up
	 * @return string
	 */
    protected function getBaseMessage($locale, $key)
    {
//		if (//self::$log->isDebugEnabled()) {
//			//self::$log->debug('getBaseMessage(' . (is_null($locale) ? 'null' : $locale->__toString()) . ',"' . $key . '")');
//		}

        // Initialize variables we will require
        $localeKey = $this->localeKey($locale);
        $originalKey = $this->messageKeyByLocaleKey($localeKey, $key);
        $message = null;

        // Search the specified Locale
        $message = $this->findMessage($locale, $key, $originalKey);
        if (!is_null($message)) {
            return $message;
        }

        if (!is_null($this->defaultLocale) && !$this->defaultLocale->equals($locale)) {
            $localeKey = $this->localeKey($this->defaultLocale);
            $message = $this->findMessageByLocaleKey($localeKey, $key, $originalKey);
        }
        if (!is_null($message)) {
            return $message;
        }

        // Find the message in the default properties file
        $message = $this->findMessageByLocaleKey('', $key, $originalKey);
        if (!is_null($message)) {
            return $message;
        }

        if ($this->returnNull) {
            return null;
        } else {
            return '???' . $this->messageKey($locale, $key) . '???';
        }
    }

    /**
	 * Load the messages associated with the specified Locale key.
	 *
	 * For this implementation, the config property should contain a fully
	 * qualified package and resource name, separated by periods, of a series
	 * of property resources to be loaded from the class loader that created
	 * this PropertyMessageResources instance.
	 *
	 * @param string $localeKey The locale key for the messages to be retrieved
	 */
    protected function loadLocale($localeKey)
    {
//		if (//self::$log->isDebugEnabled()) {
//			//self::$log->debug('loadLocale("' . $localeKey . '")');
//		}

        // Have we already attempted to load messages for this locale?
        if (array_key_exists($localeKey, $this->locales))
            return;

        $this->locales[$localeKey] = $localeKey;

        // Set up to load the property resource for this locale key, if we can
        $name = str_replace(array('::', '_', '\\'), '/', $this->config);

        if (strlen($localeKey) > 0)
            $name .= '_' . $localeKey;
        $name .= '.properties';

        // Load the specified property resource
//		if (//self::$log->isDebugEnabled()) {
//			//self::$log->debug('  Loading resource "' . $name . '"');
//		}
        try {
            $props = new \Phruts\Util\Properties();
            $props->load($name);
        } catch (\Phruts\Exception\IOException $e) {
            //self::$log->error('loadLocale() - ' . $e->getMessage());
        }

        // Copy the corresponding value into our cache
        if ($props->size() < 1) {
            return;
        }

        $keys = $props->keySet();
        foreach ($keys as $key) {
            $localeMsgKey = $this->messageKeyByLocaleKey($localeKey, $key);

//			if (//self::$log->isDebugEnabled()) {
//				//self::$log->debug('  Saving message key "' . $localeMsgKey . '"');
//			}
            $this->messages[$localeMsgKey] = $props->getProperty($key);

        }
    }

    /**
	 * Returns a text message for the specified key, for the specified
	 * Locale.
	 *
	 * A null string result will be returned by this method if no relevant
	 * message resource is found. This method searches through the locale
	 * <i>hierarchy</i> (i.e. variant --> language --> country) for the message.
	 *
	 * @param Locale $locale The requested message Locale
	 * @param string $key The message key to look up
	 * @param string $originalKey The original message key to cache any found
	 * message under
	 * @return string Text message for the specified key and locale
	 */
    private function findMessage($locale, $key, $originalKey)
    {
        // Initialize variables we will require
        $localeKey = $this->localeKey($locale);
        $messageKey = null;
        $message = null;
        $underscore = 0;

        // Loop from specific to general locales looking for this message
        while (true) {
            $message = $this->findMessageByLocaleKey($localeKey, $key, $originalKey);
            if (!is_null($message)) {
                break;
            }

            // Strip trailing modifiers to try a more general locale key
            $underscore = (integer) strrpos($localeKey, '_');

            if ($underscore < 1) {
                break;
            }

            $localeKey = substr($localeKey, 0, $underscore);
        }

        return $message;
    }

    /**
	 * Returns a text message for the specified key, for the specified
	 * Locale.
	 *
	 * A null string result will be returned by this method if no relevant
	 * message resource is found.
	 *
	 * @param string $localeKey The requested key of the Locale
	 * @param string $key The message key to look up
	 * @param string $originalKey The original message key to cache any found
	 * message under
	 * @return string Text message for the specified key and locale
	 */
    private function findMessageByLocaleKey($localeKey, $key, $originalKey)
    {
        // Load this Locale's messages if we have not done so yet
        $this->loadLocale($localeKey);

        // Check if we have this key for the current locale key
        $messageKey = $this->messageKeyByLocaleKey($localeKey, $key);

        // Add if not found under the original key
        $addIt = ($messageKey != $originalKey);

        if (!array_key_exists($messageKey, $this->messages)) {
            $message = null;
        } else {
            $message = $this->messages[$messageKey];
            if ($addIt) {
                $this->messages[$originalKey] = $message;
            }
        }

        return $message;
    }
}
