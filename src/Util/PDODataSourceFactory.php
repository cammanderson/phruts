<?php
namespace Phruts\Util;

class PDODataSourceFactory extends DataSourceFactory
{
    /**
     * Create a data source object.
     *
     * @return object
     * @throws \Exception
     */
    public function createDataSource()
    {
        $config = $this->getConfig();
        $properties = $config->getProperties();
        if(!empty($properties['dsn'])) {
            return new \PDO($properties['dsn']);
        }
    }

}