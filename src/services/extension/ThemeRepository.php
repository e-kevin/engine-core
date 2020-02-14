<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\repository\CategoryRepositoryInterface;
use EngineCore\extension\Repository\ThemeRepositoryInterface;
use EngineCore\extension\Repository\ThemeRepository as Repository;
use EngineCore\extension\ThemeInfo;

/**
 * 主题仓库管理服务类
 *
 * @property ThemeRepositoryInterface $repository
 * @property string                   $currentTheme 当前主题名，只读属性
 * @property array                    $allActiveTheme 所有激活的主题，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ThemeRepository extends BaseCategoryRepository
{
    
    /**
     * @var string 扩展信息类
     */
    protected $extensionInfo = ThemeInfo::class;
    
    private $_repository;
    
    /**
     * 获取扩展仓库，主要由该仓库处理一些和数据库结构相关的业务逻辑
     *
     * @inheritdoc
     * @return ThemeRepositoryInterface
     */
    public function getRepository(): CategoryRepositoryInterface
    {
        if (null === $this->_repository) {
            $this->setRepository(Repository::class);
        }
        
        return $this->_repository;
    }
    
    /**
     * 设置扩展仓库
     *
     * @param string|array|callable $config
     */
    public function setRepository($config)
    {
        $this->_repository = Ec::createObject($config, [], ThemeRepositoryInterface::class);
    }
    
    /**
     * 获取当前主题名
     *
     * @return string
     */
    public function getCurrentTheme(): string
    {
        return $this->getRepository()->getCurrentTheme();
    }
    
    /**
     * 获取所有激活的主题
     *
     * 注意：
     * 必须返回以扩展名为索引格式的数组
     *
     * @return array
     * [
     *  {uniqueName} => [],
     * ]
     */
    public function getAllActiveTheme(): array
    {
        return $this->getRepository()->getAllActiveTheme();
    }
    
}