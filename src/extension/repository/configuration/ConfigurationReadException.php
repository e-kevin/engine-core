<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

use Exception;

/**
 * 仓库配置无法读取异常类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
final class ConfigurationReadException extends Exception implements ConfigurationExceptionInterface
{
    
    /**
     * ConfigurationReadException constructor.
     *
     * @param string         $path
     * @param Exception|null $previous
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct($path, Exception $previous = null)
    {
        $this->_path = $path;
        
        parent::__construct(
            sprintf("Unable to read extension configuration from '%s'.", $path),
            0,
            $previous
        );
    }
    
    private $_path;
    
    /**
     * 获取配置文件路径
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
}