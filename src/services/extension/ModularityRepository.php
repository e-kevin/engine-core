<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\ModularityInfo;
use EngineCore\extension\repository\CategoryRepositoryInterface;
use EngineCore\extension\repository\ModularityRepositoryInterface;
use EngineCore\extension\Repository\ModularityRepository as Repository;
use yii\helpers\ArrayHelper;

/**
 * 模块仓库管理服务类
 *
 * @property ModularityRepositoryInterface $repository
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ModularityRepository extends BaseCategoryRepository
{
    
    /**
     * @var string 扩展信息类
     */
    protected $extensionInfo = ModularityInfo::class;
    
    private $_repository;
    
    /**
     * 获取扩展仓库，主要由该仓库处理一些和数据库结构相关的业务逻辑
     *
     * @inheritdoc
     * @return ModularityRepositoryInterface
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
        $this->_repository = Ec::createObject($config, [], ModularityRepositoryInterface::class);
    }
    
    /**
     * 获取当前应用本地【所有|已安装】的模块名称，主要用于列表筛选
     *
     * @param bool $installed 是否获取已安装的扩展，默认为`true`，获取
     *
     * @return array e.g.
     * [
     * 'EngineCore/module-account' => '账户管理',
     * 'EngineCore/module-rbac' => '权限管理',
     * ]
     */
    public function getInstalledSelectListByApp($installed = true)
    {
        return ArrayHelper::getColumn(
            $this->getConfigurationByApp($installed),
            'infoInstance.name'
        );
    }
    
    /**
     * 获取当前应用未安装的模块ID
     *
     * @return array 未安装的模块ID
     */
    public function getUninstalledModuleIdByApp()
    {
        $installed = $this->getInstalled();
        $configuration = $this->getConfigurationByApp();
        // 获取未安装的模块扩展配置
        foreach ($configuration as $uniqueName => $row) {
            // 剔除已安装的模块
            if (isset($installed[$uniqueName])) {
                unset($configuration[$uniqueName]);
                continue;
            }
        }
        
        return ArrayHelper::getColumn($configuration, 'infoInstance.id');
    }
    
}