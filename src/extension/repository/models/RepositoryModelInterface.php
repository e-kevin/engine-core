<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use EngineCore\extension\ControllerInfo;
use EngineCore\extension\ExtensionInfo;
use EngineCore\extension\ModularityInfo;
use EngineCore\extension\ThemeInfo;
use yii\base\InvalidConfigException;

/**
 * 扩展仓库模型接口类
 *
 * @property array                                   $all 所有已经安装的扩展数据库数据，只读属性
 * @property ModularityInfo|ControllerInfo|ThemeInfo $infoInstance 当前模型所属扩展的信息类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface RepositoryModelInterface
{
    
    /**
     * 扩展模型数据库数据变更（更新、添加）后，执行该事件
     */
    const EVENT_SYNC = 'sync';
    
    /**
     * 获取所有已经安装的扩展数据库数据
     *
     * @return array
     */
    public function getAll();
    
    /**
     * 获取当前模型所属扩展的信息类
     *
     * @return ModularityInfo|ControllerInfo|ThemeInfo
     */
    public function getInfoInstance();
    
    /**
     * 设置当前模型所属扩展的信息类
     *
     * @param ModularityInfo|ControllerInfo|ThemeInfo|ExtensionInfo $info
     *
     * @throws InvalidConfigException 扩展信息类不符合指定类型时抛出异常
     */
    public function setInfoInstance(ExtensionInfo $info);
    
}