<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\installation;

/**
 * 扩展管理模块的安装向导接口，扩展管理模块的Info类必须实现该接口
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ExtensionInterface
{
    
    /**
     * 配置扩展仓库模型，只有正确配置扩展仓库模型类，才能查询到扩展在数据库里的信息
     *
     * 安装向导尚未完成时，是无法提前知道哪个扩展管理模块被安装，更无法知道扩展仓库的模型类。而实现接口的
     * `setRepositoryModel()`方法，可以在使用安装向导时，扩展管理模块一旦被安装即可自动实现扩展仓库模型的配置，从而
     * 把需要安装的扩展写入扩展仓库所属的数据库表和获得已被安装的扩展的数据库数据等信息。
     *
     * 扩展管理模块一旦被安装，【配置扩展仓库模型】的自动实现，需要开发者在安装向导里安装扩展的步骤当中自主实现，
     * 大体实现逻辑如下：
     * 1、根据扩展的`Info`信息类里的扩展分类判断出扩展是否属于`扩展管理分类`。
     * @see \EngineCore\extension\repository\info\ExtensionInfo::CATEGORY_EXTENSION
     * 2、标记该扩展，如缓存该扩展的扩展名。
     * 3、调用`setRepositoryModel()`方法。
     *
     * @see \EngineCore\services\extension\ControllerRepository::setModel()
     * @see \EngineCore\services\extension\ModularityRepository::setModel()
     * @see \EngineCore\services\extension\ThemeRepository::setModel()
     * @see \EngineCore\services\extension\ConfigRepository::setModel()
     */
    public function setRepositoryModel();
    
    /**
     * 初始化扩展安装环境
     *
     * 通常情况下，数据库是空的状态，此时进行安装向导时，需要把待安装的扩展数据写进数据库，
     * 我们就必须先在数据库里创建必要的表。
     *
     * 可以通过该方法为数据库创建必要的表来储存已经安装的扩展数据或其他安装环境所需的配置。
     *
     * @return bool
     */
    public function initialize(): bool;
    
}