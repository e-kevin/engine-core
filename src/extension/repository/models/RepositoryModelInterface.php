<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;

/**
 * 扩展仓库模型接口类
 *
 * 注意：扩展仓库模型必须包含'run'字段
 *
 * ==== 数据库字段
 * @property int                                     $id           ID
 * @property string                                  $unique_name  扩展完整名称，开发者名+扩展名
 * @property string                                  $app          所属应用
 * @property int                                     $is_system    系统扩展 0:否 1:是
 * @property int                                     $status       状态 0:禁用 1:启用
 * @property int                                     $run          运行模式 0:系统扩展 1:开发者扩展
 * ====
 * @property array                                   $all          所有已经安装的扩展数据库数据，只读属性
 * @property ModularityInfo|ControllerInfo|ThemeInfo $infoInstance 当前模型所属扩展的信息类
 * @property bool                                    $canUnInstall 是否可以卸载
 * @property bool                                    $canInstall   是否可以安装
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface RepositoryModelInterface
{
    
    /**
     * 获取数据库所有数据，通常结合缓存使用
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
     */
    public function setInfoInstance(ExtensionInfo $info);
    
    /**
     * 是否已经设置模型所属扩展的信息类
     *
     * @return bool
     */
    public function hasInfoInstance(): bool;
    
    /**
     * 根据扩展名获取指定应用的扩展数据
     *
     * @param string $uniqueName
     * @param null   $app
     *
     * @return Theme|Controller|Module|null|\yii\db\ActiveRecord
     */
    public function findByUniqueName($uniqueName, $app = null);
    
    /**
     * 获取扩展是否可以卸载
     *
     * @return bool
     */
    public function getCanUninstall(): bool;
    
    /**
     * 获取扩展是否可以安装
     *
     * @return bool
     */
    public function getCanInstall(): bool;
    
}