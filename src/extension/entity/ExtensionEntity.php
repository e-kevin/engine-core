<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\entity;

use EngineCore\Ec;

/**
 * 获取指定对象所属的扩展实体
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionEntity extends BaseExtensionEntity
{
    
    /**
     * @inheritdoc
     */
    public function getInfo()
    {
        $uniqueName = Ec::$service->getExtension()
                                  ->getRepository()->getFinder()
                                  ->getNamespaceMap()[$this->getNamespace()] ?? '';
        
        return empty($uniqueName) || !$this->isSystemExtension()
            ? $this->defaultExtension()->getInfo()
            : Ec::$service->getExtension()->getRepository()->getConfigurationByApp()[$uniqueName];
    }
    
    /**
     * @inheritdoc
     */
    public function getDbConfig(): array
    {
        return !$this->isSystemExtension()
            ? $this->defaultExtension()->getDbConfig()
            : (Ec::$service->getExtension()->getRepository()->getDbConfiguration()[$this->getInfo()->getUniqueName()] ?? parent::getDbConfig());
    }
    
}