<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\helpers;

use Yii;
use yii\helpers\Inflector;

/**
 * 命名空间助手类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class NamespaceHelper
{
    
    /**
     * 命名空间转换为实际路径
     *
     * @param string $namespace
     * @param bool $throwException
     *
     * @return bool|string
     */
    public static function namespace2Path($namespace, $throwException = true)
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $namespace), $throwException);
    }
    
    /**
     * 根据命名空间获取相对应的别名键名
     *
     * @example
     * ```php
     * '{namespace}\{class}'
     * // 转换为
     * '@{namespace}/{class}'
     * ```
     *
     * @param string $namespace 命名空间
     *
     * @return int|null|string 别名键名
     */
    public static function getAliasesKeyByNamespace($namespace)
    {
        $has = null;
        $namespace = '@' . str_replace('\\', '/', $namespace);
        foreach (Yii::$aliases as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (($pos = strrpos($namespace, $k)) !== false) {
                        $has = $k;
                        break;
                    }
                }
            } else {
                if (($pos = strrpos($namespace, $key)) !== false) {
                    $has = $key;
                    break;
                }
            }
        }
        
        return $has;
    }
    
    /**
     * 别名转换为命名空间
     *
     * @example
     * ```php
     * '@app/{namespace}/{class}'
     * // 转换为
     * 'app\{namespace}\{class}'
     * ```
     *
     * @param string $aliases
     *
     * @return bool|string
     */
    public static function aliases2Namespace($aliases)
    {
        return substr(str_replace('/', '\\', $aliases), 1);
    }
    
    /**
     * 格式化带'-_'字符的字符串为命名空间所支持的格式
     * @example
     * ```php
     * 'config-manager/view'
     * // 转换为
     * 'ConfigManager\View'
     * ```
     *
     * @param string $string
     *
     * @return string
     */
    public static function normalizeStringForNamespace(string $string): string
    {
        if (strpos($string, '/') !== false) {
            $string = explode('/', $string);
            foreach ($string as &$part) {
                $part = Inflector::camelize($part);
            }
            $string = implode('\\', $string);
        } else {
            $string = Inflector::camelize($string);
        }
        
        return $string;
    }
    
}