<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\filters;

use Yii;
use yii\base\ActionFilter;

/**
 * 记录动作执行时间日志
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class LogActionTime extends ActionFilter
{
    
    private $_startTime;
    
    public function beforeAction($action)
    {
        $this->_startTime = microtime(true);
        
        return parent::beforeAction($action);
    }
    
    public function afterAction($action, $result)
    {
        $time = microtime(true) - $this->_startTime;
        $time = number_format($time, 4);
        Yii::debug("Action '{$action->getUniqueId()}' spent $time second.");
        
        return parent::afterAction($action, $result);
    }
    
}