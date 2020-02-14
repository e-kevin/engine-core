<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\Ec;
use EngineCore\web\DispatchResponse as WebDispatchResponse;
use Yii;
use yii\{
    base\Action, base\InvalidConfigException
};

/**
 * 系统调度器的基础实现类
 *
 * @property DispatchResponseInterface|WebDispatchResponse $response 调度响应器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Dispatch extends Action
{
    
    /**
     * @var \EngineCore\web\Controller|\EngineCore\console\Controller
     */
    public $controller;
    
    /**
     * @inheritdoc
     */
    public function runWithParams($params)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        $args = $this->controller->bindActionParams($this, $params);
        Yii::debug('Running dispatch: ' . get_class($this) . '::run(), invoked by ' . get_class($this->controller), __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }
        if ($this->beforeRun()) {
            $result = call_user_func_array([$this, 'run'], $args);
            $this->afterRun();
            
            return $result;
        } else {
            return null;
        }
    }
    
    private $_response;
    
    /**
     * 获取调度响应器
     *
     * @return DispatchResponseInterface|WebDispatchResponse
     * @throws InvalidConfigException
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            if ($this->controller->getDispatchManager()->getGenerator()->isSupportRender()) {
                $this->setResponse(Ec::getThemeConfig('response')); // 默认为当前主题的'response'
            } else {
                throw new InvalidConfigException('The `response` property must be set.');
            }
        }
        
        return $this->_response;
    }
    
    /**
     * 设置调度响应器
     *
     * @param string|array|callable $response 调度响应器
     *
     * @throws InvalidConfigException
     */
    public function setResponse($response)
    {
        $this->_response = Yii::createObject($response, [$this]);
        if ($this->controller->getDispatchManager()->getGenerator()->isSupportRender()) {
            if (!$this->_response instanceof WebDispatchResponse) {
                throw new InvalidConfigException('`' . get_class($this->_response) . '` class must extend from `' . WebDispatchResponse::class . '`.');
            }
        } else {
            if (!is_subclass_of($this->_response, DispatchResponse::class)) {
                throw new InvalidConfigException('`' . get_class($this->_response) . '` class must extend from `' . DispatchResponse::class . '`.');
            }
        }
    }
    
}