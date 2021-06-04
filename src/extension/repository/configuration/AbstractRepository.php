<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

use yii\base\BaseObject;

/**
 * 扩展仓库资源抽象类
 *
 * @property mixed $rawData
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class AbstractRepository extends BaseObject implements RepositoryInterface
{
    
    /**
     * AbstractRepository constructor.
     *
     * @param string $type
     * @param mixed  $rawData
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct($type, $rawData = null, $config = [])
    {
        $this->_type = $type;
        $this->_rawData = $rawData;
        
        parent::__construct($config);
    }
    
    private $_type;
    
    /**
     * 获取仓库资源类型
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
    
    private $_rawData;
    
    /**
     * 获取仓库资源原始数据
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->_rawData;
    }
    
}