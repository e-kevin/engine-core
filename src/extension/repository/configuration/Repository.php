<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

/**
 * 仓库资源类
 *
 * @property string $uri
 *
 * @see https://docs.phpcomposer.com/05-repositories.html
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Repository extends AbstractRepository
{
    
    /**
     * Repository constructor.
     *
     * @param string $type
     * @param null   $uri
     * @param mixed  $rawData
     * @param array  $config
     *
     * @internal param array $config
     *
     * @author   E-Kevin <e-kevin@qq.com>
     */
    public function __construct($type, $uri = null, $rawData = null, $config = [])
    {
        $this->_uri = $uri;
        
        parent::__construct($type, $rawData, $config);
    }
    
    private $_uri;
    
    /**
     * 获取资源URI
     *
     * @return string|null
     */
    public function getUri()
    {
        return $this->_uri;
    }
    
}