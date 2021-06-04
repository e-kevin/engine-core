<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use yii\base\InvalidConfigException;

/**
 * 调度器生成器接口
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchGeneratorInterface
{
    
    /**
     * 根据当前请求调度器ID的调度器配置，生成所需的调度器
     *
     * @param array $config 当前请求调度器ID的调度器配置
     *
     * @return Dispatch|null
     * @throws InvalidConfigException
     */
    public function createDispatch(array $config);
    
}