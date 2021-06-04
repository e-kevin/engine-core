<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\entity;

use EngineCore\extension\repository\info\ExtensionInfo;

/**
 * 获取指定对象所属的扩展实体接口
 *
 * 扩展实体包含有扩展配置信息类、扩展数据库信息等有关扩展的信息
 *
 * @property string             $namespace           对象所属扩展的根命名空间，只读属性
 * @property ExtensionInfo|null $info                对象所属扩展的本地配置信息类，只读属性
 * @property array              $dbConfig            对象所属扩展的数据库配置信息，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ExtensionEntityInterface
{
    
    /**
     * ExtensionEntityInterface constructor.
     *
     * @param object $object 被检测的对象
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct($object, array $config = []);
    
    /**
     * 对象所属扩展的根命名空间
     *
     * @return string
     */
    public function getNamespace(): string;
    
    /**
     * 对象是否属于系统扩展
     *
     * 判断符合以下其中一条即可：
     *  - 对象位于'@extensions'目录下
     *  - 对象别名配置的键值位于'@extensions'目录下
     *
     * 约定：
     *  - 系统扩展，位于项目内'@extensions'目录下的扩展
     *  - 开发者扩展，位于项目内'@developer'目录下的扩展
     *  - 用户自定义扩展，位于项目内任何地方，如'@backend/extensions'、'@frontend/extensions'目录下的扩展
     *
     * @return bool
     */
    public function isSystemExtension(): bool;
    
    /**
     * 对象所属扩展的本地配置信息类
     *
     * @return ExtensionInfo|null
     */
    public function getInfo();
    
    /**
     * 加载扩展信息类里的配置
     */
    public function loadConfig();
    
    /**
     * 对象所属扩展的数据库配置信息，目前主要是获取扩展当前的运行模式
     *
     * 数据库配置信息里必须包含以下字段：
     *  - `run`: 扩展运行模式
     * @see \EngineCore\extension\repository\models\RepositoryModelInterface
     *
     * @return array
     */
    public function getDbConfig(): array;
    
    /**
     * 对象不属于任何一个扩展时，可用该方法为对象指定一个默认扩展
     *
     * @return self
     */
    public function defaultExtension();
    
}