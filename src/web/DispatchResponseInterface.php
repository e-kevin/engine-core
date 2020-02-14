<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\web;

/**
 * 调度响应器接口类，用于更改反馈给WEB客户端的表现形式
 *
 * @property int $waitSecond 页面跳转停留时间
 * @property mixed $jumpUrl 页面跳转地址
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchResponseInterface extends \EngineCore\dispatch\DispatchResponseInterface
{
    
    /**
     * 渲染页面
     *
     * @param string|null $view 需要渲染的视图文件
     * @param array $assign 视图模板赋值数据
     *
     * @return mixed
     */
    public function render($view = null, array $assign = []);
    
    /**
     * 设置页面跳转停留时间，默认为3妙
     *
     * @param int $second
     *
     * @return DispatchResponseInterface
     */
    public function setWaitSecond($second = 3);
    
    /**
     * 获取页面跳转停留时间
     *
     * @return int
     */
    public function getWaitSecond();
    
    /**
     * 设置页面跳转地址
     *
     * @param mixed $url
     *
     * @return DispatchResponseInterface
     */
    public function setJumpUrl($url = null);
    
    /**
     * 获取页面跳转地址
     *
     * @return string|array|null
     */
    public function getJumpUrl();
    
}