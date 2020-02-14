<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

use yii\base\Controller;

/**
 * 当前控制器所属的扩展接口类
 *
 * @property string $namespace 当前控制器的上级命名空间，只读属性
 * @property array  $info 控制器所属扩展的本地配置文件信息，只读属性
 * @property array  $dbConfig 控制器所属扩展的数据库配置信息，只读属性
 * @property string $extensionUniqueName 当前控制器所属的扩展名称，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface RunningExtensionInterface
{
    
    /**
     * RunningExtensionInterface constructor.
     *
     * @param Controller $controller 调用该类的控制器
     * @param array      $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(Controller $controller, array $config = []);
    
    /**
     * 当前控制器的上级命名空间
     *
     * @return string
     */
    public function getNamespace(): string;
    
    /**
     * 是否属于系统扩展控制器
     * @see \EngineCore\dispatch\RunRule 简述里对不同控制器的定义
     *
     * @return bool
     */
    public function isExtensionController(): bool;
    
    /**
     * 控制器所属扩展的本地配置文件信息
     *
     * @return ExtensionInfo|object
     */
    public function getInfo();
    
    /**
     * 控制器所属扩展的数据库配置信息，目前主要是获取扩展当前的运行模式
     *
     * 数据库配置信息里必须包含以下字段：
     *  - `run`: 扩展运行模式
     * @see \EngineCore\extension\repository\CategoryRepositoryInterface::setModel()
     *
     * @return array
     */
    public function getDbConfig(): array;
    
    /**
     * 获取当前控制器所属的扩展名称
     *
     * @return string
     */
    public function getExtensionUniqueName(): string;
    
    /**
     * 控制器不属于任何一个扩展时，可用该方法为控制器指定一个所属扩展
     * EngineCore提供了一个默认的扩展类@see \EngineCore\extension\EngineCoreExtension
     *
     * @return self|object
     */
    public function defaultExtension();
    
}