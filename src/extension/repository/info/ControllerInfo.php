<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\info;

/**
 * 控制器扩展信息类
 *
 * @property string $moduleId      扩展所属模块ID，读写属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ControllerInfo extends ExtensionInfo
{

    /**
     * @var string 控制器ID
     */
    protected $id;
    
    /**
     * @var string 扩展所属模块ID，默认空值为当前应用扩展，在Yii中，应用本身就是一个顶级模块
     */
    protected $moduleId = '';
    
    /**
     * {@inheritdoc}
     */
    final public function getType(): string
    {
        return self::TYPE_CONTROLLER;
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->name = $this->getModuleId() ? "/{$this->getModuleId()}/{$this->id}" : "{$this->id}";
    }
    
    /**
     * 获取扩展所属模块ID
     *
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }
    
    /**
     * 获取扩展所属模块ID
     *
     * @param string $id
     */
    public function setModuleId(string $id)
    {
        $this->moduleId = $id;
    }
    
}