<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

use EngineCore\Ec;

/**
 * 当前控制器所属的扩展
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class RunningExtension extends BaseRunningExtension
{
    
    /**
     * @inheritdoc
     */
    public function getInfo()
    {
        $uniqueName = Ec::$service->getExtension()->getRepository()->getFinder()->getNamespaceMap()[$this->getNamespace()];
        
        return !$this->isExtensionController()
            ? $this->defaultExtension()->getInfo()
            : Ec::$service->getExtension()->getRepository()->getConfigurationByApp()[$uniqueName];
    }
    
    /**
     * @inheritdoc
     */
    public function getDbConfig(): array
    {
        return !$this->isExtensionController()
            ? $this->defaultExtension()->getDbConfig()
            : Ec::$service->getExtension()->getRepository()->getDbConfiguration()[$this->getInfo()->getUniqueName()]
            ?? parent::getDbConfig();
    }
    
}