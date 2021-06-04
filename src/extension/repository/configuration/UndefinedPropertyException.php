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
 * 仓库配置的属性未定义
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
final class UndefinedPropertyException extends Exception implements ConfigurationExceptionInterface
{
    
    /**
     * UndefinedPropertyException constructor.
     *
     * @param string         $property
     * @param Exception|null $previous
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct($property, Exception $previous = null)
    {
        $this->_property = $property;
        
        parent::__construct(
            sprintf(
                "Undefined property '%s' in extension configuration.",
                $this->getProperty()
            ),
            0,
            $previous
        );
    }
    
    private $_property;
    
    /**
     * 属性名
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->_property;
    }
    
}