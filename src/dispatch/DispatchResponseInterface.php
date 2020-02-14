<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

/**
 * 调度响应器接口类，用于更改反馈给客户端的表现形式
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchResponseInterface
{
    
    /**
     * 操作成功后返回结果至客户端
     *
     * @param string $message 提示信息
     * @param mixed  $data 方法内需要使用的数据
     *
     * @return mixed
     */
    public function success($message = '', $data = []);
    
    /**
     * 操作失败后返回结果至客户端
     *
     * @param string $message 提示信息
     * @param mixed  $data 方法内需要使用的数据
     *
     * @return mixed
     */
    public function error($message = '', $data = []);
    
    /**
     * 储存赋值数据
     *
     * 示例：
     * ```php
     *  $this->setAssign('name1', 'apple');
     *  $this->setAssign('name2', 'orange');
     *  等于
     *  $this->setAssign([
     *      'name1' => 'apple',
     *      'name2' => 'orange'
     *  ]);
     *
     * ```
     * @param string|array $key
     * @param null         $value
     *
     * @return DispatchResponseInterface
     */
    public function setAssign($key, $value = null);
    
    /**
     * 获取赋值数据
     *
     * @param string $key 当'$key'不为空时，返回该键名的赋值数据，赋值数据不存在则返回'$defaultValue'默认值。
     * @param null   $defaultValue
     *
     * @return mixed
     */
    public function getAssign($key = null, $defaultValue = null);
    
}