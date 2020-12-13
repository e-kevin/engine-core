<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\configuration;

/**
 * 包资源类
 *
 * @property array $packageOptions
 *
 * @see    https://docs.phpcomposer.com/05-repositories.html#Package
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class PackageRepository extends AbstractRepository
{
    
    /**
     * PackageRepository constructor.
     *
     * @param array $packageOptions
     * @param mixed $rawData
     * @param array $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(array $packageOptions, $rawData = null, $config = [])
    {
        $this->_packageOptions = $packageOptions;
        
        parent::__construct('package', $rawData, $config);
    }
    
    private $_packageOptions;
    
    /**
     * 获取包资源定义数据
     *
     * @return array
     */
    public function getPackageOptions()
    {
        return $this->_packageOptions;
    }
    
}