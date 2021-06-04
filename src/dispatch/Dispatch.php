<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
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
     * {@inheritdoc}
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
     */
    public function getResponse()
    {
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
        $this->_response = Ec::createObject($response, [$this],
            $this->controller->getDispatchManager()->isSupportRender()
                ? WebDispatchResponse::class
                : DispatchResponse::class
        );
    }
    
}