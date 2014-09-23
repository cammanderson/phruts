<?php
/**
 * Created by Cam MANDERSON <cameronmanderson@gmail.com>
 */

namespace Phruts\Config\Digester;

/**
 * Class that calls addProperty for the top object on the stack, which must be
 * a DataSourceConfig.
 */
final class AddDataSourcePropertyRule extends \Phigester\AbstractRule
{
    /**
     * @param array $attributes
     */
    public function begin(array $attributes)
    {
        $dataSourceConfig = $this->digester->peek();
        $dataSourceConfig->addProperty($attributes['property'], $attributes['value']);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'AddDataSourcePropertyRule[]';
    }
}
