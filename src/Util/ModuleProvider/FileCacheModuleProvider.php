<?php
namespace Phruts\Util\ModuleProvider;

use Phruts\Config\ModuleConfig;
use Phigester\Digester;

class FileCacheModuleProvider implements ModuleProviderInterface
{
    /**
     * @var Digester
     */
    protected $application;

    protected $cachePath;

    public function __construct(\Silex\Application $application)
    {
        $this->application = $application;
    }

    public function getModuleConfig($prefix = '', $config)
    {
        $configPaths = preg_split('/,/', $config);
        $rebuild = false;

        // Determine if a cache file is present
        $cacheFile = $this->cachePath . '/phruts' . (strlen($prefix) > 0 ? '-' : '') . $prefix . '.data';
        $mtime = file_exists($cacheFile) ? filemtime($cacheFile) : false;

        // Check the ages of the config paths against the age of the cache file
        if ($mtime !== false) {
            foreach ($configPaths as $path) {
                $cmtime = filemtime($path);
                if ($cmtime == false) {
                    throw new \Phruts\Exception('Unable to locate the specified Phruts configuration file');
                }
                if ($cmtime > $mtime) {
                    $rebuild = true;
                    break;
                }
            }
        } else {
            // We couldn't find the modified time on the cache file
            $rebuild = true;
        }

        // Get the module config
        if ($rebuild == true) {
            // (re)Build the cache
            if (!is_writable(dirname($cacheFile)) || (is_file($cacheFile) && !is_writable($cacheFile))) {
                throw new \Phruts\Exception('Unable to write to the cache');
            }

            // Digest the config
            $moduleConfig = new ModuleConfig($prefix);
            $digester = $this->application[\Phruts\Util\Globals::DIGESTER];
            if (empty($digester)) {
                throw new \Phruts\Exception('Digester is not present in the application, unable to process the configruation file');
            }

            $digester->clear();
            $digester->push($moduleConfig);
            foreach ($configPaths as $path) {
                $digester->parse($path);
            }
            $moduleConfig->freeze();

            // Write out the cache
            file_put_contents($cacheFile, serialize($moduleConfig));
        } else {
            // Obtain from the cache
            $serialised = file_get_contents($cacheFile);
            $moduleConfig = unserialize($serialised);
        }

        return $moduleConfig;
    }

    public function setCachePath($path)
    {
        $this->cachePath = $path;
    }
}
