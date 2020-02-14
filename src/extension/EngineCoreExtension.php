<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

/**
 * EngineCore核心构架扩展
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class EngineCoreExtension extends BaseRunningExtension
{
    
    /**
     * @inheritdoc
     */
    public function getInfo()
    {
        return $this->defaultExtension();
    }
    
    /**
     * @inheritdoc
     */
    public function getDbConfig(): array
    {
        return [
            'run' => ExtensionInfo::RUN_MODULE_EXTENSION,
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getExtensionUniqueName(): string
    {
        return $this->getInfo()->getUniqueName();
    }
    
}