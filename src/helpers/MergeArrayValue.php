<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\helpers;

use yii\base\InvalidConfigException;

/**
 * Class MergeArrayValue
 *
 * 合并数组值
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MergeArrayValue
{
    
    /**
     * @var array
     */
    public $value;
    
    
    /**
     * MergeArrayValue constructor.
     *
     * @param $value
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }
    
    /**
     * Restores class state after using `var_export()`.
     *
     * @param array $state
     *
     * @return MergeArrayValue
     * @throws InvalidConfigException when $state property does not contain `value` parameter
     * @see   var_export()
     */
    public static function __set_state($state)
    {
        if (!isset($state['value'])) {
            throw new InvalidConfigException('Failed to instantiate class "MergeArrayValue". Required parameter "value" is missing');
        }
        
        return new self($state['value']);
    }
    
}