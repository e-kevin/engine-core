<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * 调度响应器抽象类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class DispatchResponse extends BaseObject implements DispatchResponseInterface
{
    
    /**
     * 调用调度响应器的调度器
     *
     * @var Dispatch
     */
    protected $dispatch;
    
    /**
     * @param Dispatch $dispatch 调用调度响应器的调度器
     * @param array    $config
     */
    public function __construct(Dispatch $dispatch, array $config = [])
    {
        $this->dispatch = $dispatch;
        parent::__construct($config);
    }
    
    private $_assign = [];
    
    /**
     * {@inheritdoc}
     */
    final public function setAssign($key, $value = null)
    {
        if (is_array($key)) {
            $this->_assign = ArrayHelper::merge($this->_assign, $key);
        } else {
            $this->_assign[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    final public function getAssign($key = null, $defaultValue = null)
    {
        return $key ? ($this->_assign[$key] ?? $defaultValue) : $this->_assign;
    }
    
}