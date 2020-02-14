<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\grid;

use yii\{
    grid\DataColumn, helpers\ArrayHelper
};

/**
 * 主要是修复一个显示问题
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DateTimeColumn extends DataColumn
{
    
    public $format = 'datetime';
    
    public function getDataCellValue($model, $key, $index)
    {
        if ($this->value !== null) {
            if (is_string($this->value)) {
                // 不存在则显示返回null，主要是用于更正时间日期的显示，因为时间为0时同样会被格式化
                return ArrayHelper::getValue($model, $this->value) ?: null;
            }
            
            return call_user_func($this->value, $model, $key, $index, $this);
        } elseif ($this->attribute !== null) {
            // 不存在则显示返回null，主要是用于更正时间日期的显示，因为时间为0时同样会被格式化
            return ArrayHelper::getValue($model, $this->attribute) ?: null;
        }
        
        return null;
    }
    
}
