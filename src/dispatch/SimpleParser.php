<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\helpers\ArrayHelper;
use yii\base\BaseObject;

/**
 * 调度器配置解析器
 *
 * 主要把各种格式的配置数据转换为调度管理器（DispatchManager）能够理解的数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SimpleParser extends BaseObject implements DispatchConfigParserInterface
{
    
    /**
     * @var DispatchManager 调度器管理器
     */
    protected $dm;
    
    /**
     * SimpleParser constructor.
     *
     * @param BaseDispatchManager $dispatchManager
     * @param array               $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(BaseDispatchManager $dispatchManager, array $config = [])
    {
        $this->dm = $dispatchManager;
        parent::__construct($config);
    }
    
    /**
     * 标准化调度器配置数据
     *
     * 注意：调度配置为数组，支持以下键值配置或键名-键值对配置
     * 键值配置：
     * ```php
     * ['index', 'bootstrap-v3/index']
     * ```
     * 键名-键值对配置：
     *  - `class`: 使用该类创建调度器，该类必须继承`\EngineCore\dispatch\Dispatch`。
     *  - `map`: 使用其他调度器进行映射，目前仅支持同一控制器下的调度器映射。
     *  注意：当'class'被明确指定后，该配置将不生效。
     *  - `response`: 调度响应器配置 @see \EngineCore\dispatch\DispatchResponse
     *
     * ```php
     * [
     *  'index' => [],
     *  'index' => '{namespace}\{className}',
     *  'index' => 'home',
     *  'bootstrap-v3/index' => [],
     *  'bootstrap-v3/index' => '{namespace}\{className}',
     *  'bootstrap-v3/index' => 'home',
     * ]
     * ```
     *
     * @param array $config 调度器配置数据
     *
     * @return array
     */
    public function normalize(array $config): array
    {
        $arr = [];
        foreach ($config as $key => $value) {
            if (is_int($key)) {
                $arr = ArrayHelper::merge($arr, $this->normalizeStringConfig($value));
            } else {
                $arr = ArrayHelper::merge($arr, $this->normalizeArrayConfig($key, $value));
            }
        }
        
        return $arr;
    }
    
    /**
     * 格式化字符串配置格式的配置数据
     *
     * ```php
     * ['index', 'bootstrap-v3/index']
     * ```
     *
     * @param string $config
     *
     * @return array
     */
    protected function normalizeStringConfig(string $config): array
    {
        $arr = [];
        // 解析多主题配置
        if ($this->dm->getThemeRule()->isEnableTheme() && strpos($config, '/') !== false) {
            list($themeName, $dispatchId) = explode('/', $config);
            if (strpos($dispatchId, ':') === false) {
                /**
                 * 'bootstrap-v3/index'
                 * 转换为
                 * [
                 *  '@bootstrap-v3' => [
                 *      'index' => [],
                 *  ],
                 * ]
                 */
                $arr['@' . $themeName][$dispatchId] = [];
            }
        } else {
            if (strpos($config, ':') === false) {
                /**
                 * 'index'
                 * 转换为
                 * [
                 *  'index' => [],
                 * ]
                 */
                $arr[$config] = [];
            }
        }
        
        return $arr;
    }
    
    /**
     * 格式化数组配置格式的配置数据
     *
     * ```php
     * [
     *  'index' => [],
     *  'index' => '{namespace}\{className}',
     *  'index' => 'home',
     *  'bootstrap-v3/index' => [],
     *  'bootstrap-v3/index' => '{namespace}\{className}',
     *  'bootstrap-v3/index' => 'home',
     * ]
     * ```
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    protected function normalizeArrayConfig(string $key, $value): array
    {
        $arr = [];
        // 解析多主题配置
        if ($this->dm->getThemeRule()->isEnableTheme() && strpos($key, '/') !== false) {
            list($themeName, $dispatchId) = explode('/', $key);
            if (is_array($value)) {
                /**
                 * 'bootstrap-v3/index' => []
                 * 转换为
                 * [
                 *  '@bootstrap-v3' => [
                 *      'index' => [],
                 *  ],
                 * ]
                 */
                $arr['@' . $themeName][$dispatchId] = $value;
            } elseif (strpos($value, '\\') !== false) {
                /**
                 * 'bootstrap-v3/index' => '{namespace}\{className}'
                 * 转换为
                 * [
                 *  '@bootstrap-v3' => [
                 *      'index' => [
                 *          'class' => '{namespace}\{className}',
                 *      ],
                 *  ],
                 * ]
                 */
                $arr['@' . $themeName][$dispatchId]['class'] = $value;
            } elseif (is_string($value)) {
                if (strpos($value, ':') === false) {
                    /**
                     * 'bootstrap-v3/index' => 'home'
                     * 转换为
                     * [
                     *  '@bootstrap-v3' => [
                     *      'index' => [
                     *          'map' => 'home',
                     *      ],
                     *  ],
                     * ]
                     */
                    $arr['@' . $themeName][$key]['map'] = $value;
                }
            } else {
                $this->normalizeOtherValueConfig($key, $value);
            }
        } else {
            if (is_array($value)) {
                /**
                 * 'index' => []
                 * 转换为
                 * [
                 *  'index' => [],
                 * ]
                 */
                $arr[$key] = $value;
            } elseif (strpos($value, '\\') !== false) {
                /**
                 * 'index' => '{namespace}\{className}'
                 * 转换为
                 * [
                 *  'index' => [
                 *      'class' => '{namespace}\{className}',
                 *  ],
                 * ]
                 */
                $arr[$key]['class'] = $value;
            } elseif (is_string($value)) {
                if (strpos($value, ':') === false) {
                    /**
                     * 'index' => 'home'
                     * [
                     *  'index' => [
                     *      'map' => 'home',
                     *  ],
                     * ]
                     */
                    $arr[$key]['map'] = $value;
                }
            } else {
                $this->normalizeOtherValueConfig($key, $value);
            }
        }
        
        return $arr;
    }
    
    /**
     * 格式化键值为其他配置格式（除数组、字符串、命名空间）的配置数据
     *
     * @param string $key
     * @param mixed  $value
     */
    protected function normalizeOtherValueConfig(string $key, $value)
    {
    }
    
}