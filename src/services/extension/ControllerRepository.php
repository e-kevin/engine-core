<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\ControllerInfo;
use EngineCore\extension\Repository\CategoryRepositoryInterface;
use EngineCore\extension\Repository\ControllerRepositoryInterface;
use EngineCore\extension\Repository\ControllerRepository as Repository;

/**
 * 控制器仓库管理服务类
 *
 * @property ControllerRepositoryInterface $repository
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ControllerRepository extends BaseCategoryRepository
{
    
    /**
     * @var string 扩展信息类
     */
    protected $extensionInfo = ControllerInfo::class;
    
    private $_repository;
    
    /**
     * 获取扩展仓库，主要由该仓库处理一些和数据库结构相关的业务逻辑
     *
     * @inheritdoc
     * @return ControllerRepositoryInterface
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
        $this->_repository = Ec::createObject($config, [], ControllerRepositoryInterface::class);
    }
    
}