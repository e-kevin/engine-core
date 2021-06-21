<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\info;

/**
 * 模块扩展信息类
 *
 * @property bool  $bootstrap     是否启用bootstrap，可读写
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ModularityInfo extends ExtensionInfo
{
    
    /**
     * @var string 模块ID
     */
    protected $id;
    
    /**
     * @var boolean 是否启用bootstrap
     */
    protected $bootstrap = false;
    
    /**
     * {@inheritdoc}
     */
    final public function getType(): string
    {
        return self::TYPE_MODULE;
    }
    
    /**
     * 获取是否启用bootstrap
     *
     * @return bool
     */
    public function getBootstrap(): bool
    {
        return $this->bootstrap;
    }
    
    /**
     * 设置是否启用bootstrap
     *
     * @param bool $bootstrap
     */
    public function setBootstrap(bool $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }
    
}