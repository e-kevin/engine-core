<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\info;

/**
 * 系统配置信息类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ConfigInfo extends ExtensionInfo
{
    
    /**
     * {@inheritdoc}
     */
    final public function getType(): string
    {
        return self::TYPE_CONFIG;
    }
    
}