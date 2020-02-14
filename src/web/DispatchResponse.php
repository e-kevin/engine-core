<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\web;

use Yii;
use yii\helpers\Url;

/**
 * 调度响应器，用于更改反馈给WEB客户端的表现形式
 *
 * @property int $waitSecond 页面跳转停留时间
 * @property mixed $jumpUrl 页面跳转地址
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DispatchResponse extends \EngineCore\dispatch\DispatchResponse implements DispatchResponseInterface
{
    
    /**
     * @var string 需要渲染的视图文件，配置格式参见：
     * @see \yii\base\View::render() 的'$view'参数
     * 默认情况下，该值由调度器生成器在创建调度器时自动设置。
     * @see \EngineCore\dispatch\Generator::initConfig()
     * @see \EngineCore\dispatch\Generator::_setViewFile()
     */
    public $viewFile;
    
    /**
     * @inheritdoc
     * @return \yii\web\Response
     */
    public function success($message = '', $data = null)
    {
        return $this->dispatchJump($message ?: Yii::t('Ec/app', 'Operation successful.'), $data, 1);
    }
    
    /**
     * @inheritdoc
     * @return \yii\web\Response
     */
    public function error($message = '', $data = null)
    {
        return $this->dispatchJump($message ?: Yii::t('Ec/app', 'Operation failure.'), $data, 0);
    }
    
    /**
     * 处理跳转操作，支持错误跳转和正确跳转。
     *
     * @param string|array $message 提示信息
     * @param mixed $data 跳转地址，该值设置遵照以下原则：
     *  - string: 当为字符串时，则自动跳转到该地址。
     *  - array: 数组形式的路由地址。
     *  - 空值: 为空时('', [])则跳转到当前请求地址，即刷新当前页面。
     *  - null: 不跳转。
     * @param integer $status 状态 1:success 0:error
     *
     * @return \yii\web\Response 默认返回包含以下键名的数据到客户端
     * ```php
     * [
     *      'waitSecond',
     *      'jumpUrl',
     *      'info',
     *      'status',
     *      ...,
     * ]
     * ```
     */
    protected function dispatchJump($message = '', $data = [], $status = 1)
    {
        $request = Yii::$app->getRequest();
        // 设置跳转时间
        if (null === $this->getWaitSecond()) {
            $this->setWaitSecond($status ? 1 : 3);
        }
        // 设置跳转地址
        if (null === $this->getJumpUrl()) {
            $this->setJumpUrl($data);
        }
        $this->setAssign([
            'status' => $status,
            'info' => $message,
        ]);
        
        // 全页面加载
        if ($request->getIsPjax() || !$request->getIsAjax()) {
            // 使用闪存储存信息
            Yii::$app->getSession()->setFlash($status ? 'success' : 'error', $this->getAssign('info'));
            if (null !== $this->getJumpUrl()) {
                $this->dispatch->controller->redirect($this->getJumpUrl());
                Yii::$app->end();
            }
        } // AJAX请求方式
        else {
            $this->dispatch->controller->asJson($this->getAssign());
            Yii::$app->end();
        }
        
        return Yii::$app->getResponse();
    }
    
    /**
     * @inheritdoc
     */
    public function render($view = null, array $assign = [])
    {
        return $this->setAssign($assign)->dispatch->controller->render(
            $view ?: ($this->viewFile ?: $this->dispatch->id),
            $this->getAssign()
        );
    }
    
    /**
     * @inheritdoc
     * @return self
     */
    final public function setWaitSecond($second = 3)
    {
        return $this->setAssign('waitSecond', intval($second));
    }
    
    /**
     * @inheritdoc
     */
    final public function getWaitSecond()
    {
        return $this->getAssign('waitSecond');
    }
    
    /**
     * 设置页面跳转地址，默认为`null`，即不跳转。
     * 该值设置遵照以下原则：
     *  - string: 当为字符串时，则自动跳转到该地址，为空字符串时则跳转到当前请求地址，即刷新当前页面。
     *  - array: 数组形式的路由地址。
     *  - null: 不需要跳转。
     *
     * @param mixed $url
     *
     * @return self
     */
    public function setJumpUrl($url = null)
    {
        // $url为空值时('', [])则设为当前请求地址。
        if (null !== $url && empty($url)) {
            $url = '';
        }
        
        return $this->setAssign('jumpUrl', $url === null ? null : Url::to($url));
    }
    
    /**
     * @inheritdoc
     */
    final public function getJumpUrl()
    {
        return $this->getAssign('jumpUrl');
    }
    
}