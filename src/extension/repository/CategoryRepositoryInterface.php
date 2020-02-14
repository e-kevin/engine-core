<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository;

use EngineCore\db\ActiveRecord;
use EngineCore\extension\repository\models\RepositoryModelInterface;

/**
 * 扩展仓库接口类
 *
 * @property array                                 $all 所有已经安装的扩展数据库数据，只读属性
 * @property ActiveRecord|RepositoryModelInterface $model 扩展模型，读写属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface CategoryRepositoryInterface
{
    
    /**
     * 获取扩展详情
     *
     * @param string $extensionName 扩展名称
     *
     * @return ActiveRecord|RepositoryModelInterface|null
     */
    public function getInfo($extensionName): RepositoryModelInterface;
    
    /**
     * 获取所有已经安装的扩展数据库数据
     *
     * 注意：
     * 必须返回以扩展名为索引格式的数组
     *
     * @return array
     * [
     *  {uniqueName} => [],
     * ]
     */
    public function getAll();
    
    /**
     * 获取扩展模型
     *
     * @return ActiveRecord|RepositoryModelInterface
     */
    public function getModel(): RepositoryModelInterface;
    
    /**
     * 设置扩展模型
     *
     * 注意：
     * 扩展模型必须包含'run'字段
     *
     * @param null|string|array $config
     */
    public function setModel($config = []);
    
}