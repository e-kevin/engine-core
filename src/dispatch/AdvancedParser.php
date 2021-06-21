<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

/**
 * 调度器配置解析器，支持带':'冒号字符的解析
 *
 * 主要把各种格式的配置数据转换为调度管理器（DispatchManager）能够理解的数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class AdvancedParser extends SimpleParser
{
    
    /**
     * 标准化调度器配置数据，支持带':'冒号字符的解析
     *
     * 注意：调度配置为数组，支持以下键值配置或键名-键值对配置
     * 键值配置：
     * ```php
     * ['index', 'bootstrap-v3/index', 'index:home', 'bootstrap-v3/index:home']
     * ```
     * 键名-键值对配置：
     *  - `class` string: 使用该类创建调度器，该类必须继承`\EngineCore\dispatch\Dispatch`。
     *  - `map` string: 使用其他调度器进行映射，目前仅支持同一控制器下的调度器映射。
     *  注意：当'class'被明确指定后，`map`配置将不生效。
     *  - `response` array: 调度响应器配置 @see \EngineCore\dispatch\DispatchResponse
     *
     * ```php
     * [
     *      'index' => [],
     *      'index' => '{namespace}\{className}',
     *      'index' => 'home',
     *      'bootstrap-v3/index' => [],
     *      'bootstrap-v3/index' => '{namespace}\{className}',
     *      'bootstrap-v3/index' => 'home',
     *      'index' => 'home:home', // 用'Home'调度器代替'Index'调度器，并用'home'视图文件渲染页面
     *      'bootstrap-v3/index' => 'home:home', bootstrap-v3主题启用时，用'Home'调度器代替'Index'调度器，并用'home'视图文件渲染页面
     *      'index' => ':home', // 'Index'调度器用'home'视图文件渲染页面
     *      'bootstrap-v3/index' => ':home', bootstrap-v3主题启用时，'Index'调度器用'home'视图文件渲染页面
     * ]
     * ```
     *
     * @param array $config 调度器配置数据
     *
     * @return array
     */
    public function normalize(array $config): array
    {
        return parent::normalize($config);
    }
    
    /**
     * 格式化字符串配置格式的配置数据
     *
     * ```php
     * ['index', 'bootstrap-v3/index', 'index:home', 'bootstrap-v3/index:home']
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
        if ($this->dm->getTheme()->isEnableTheme() && strpos($config, '/') !== false) {
            list($themeName, $dispatchId) = explode('/', $config);
            if (strpos($dispatchId, ':') !== false) {
                /**
                 * 'bootstrap-v3/index:home'
                 * 转换为
                 * [
                 *      '@bootstrap-v3' => [
                 *          'index' => [
                 *              'response' => [
                 *                  'viewFile' => 'home',
                 *              ],
                 *          ],
                 *      ],
                 * ]
                 */
                $arr['@' . $themeName] = $this->normalizeStringConfigWithColon($dispatchId);
            } else {
                /**
                 * 'bootstrap-v3/index'
                 * 转换为
                 * [
                 *      '@bootstrap-v3' => [
                 *          'index' => [],
                 *      ],
                 * ]
                 */
                $arr['@' . $themeName][$dispatchId] = [];
            }
        } else {
            if (strpos($config, ':') !== false) {
                /**
                 * 'index:home'
                 * 转换为
                 * [
                 *      'index' => [
                 *          'response' => [
                 *              'viewFile' => 'home',
                 *          ],
                 *      ],
                 * ]
                 */
                $arr = $this->normalizeStringConfigWithColon($config);
            } else {
                /**
                 * 'index'
                 * 转换为
                 * [
                 *      'index' => [],
                 * ]
                 */
                $arr[$config] = [];
            }
        }
        
        return $arr;
    }
    
    /**
     * 格式化带冒号格式的字符串配置数据
     *
     * ```php
     * ['index:home']
     * // 转换为
     * [
     *      'index' => [
     *          'response' => [
     *              'viewFile' => 'home',
     *          ],
     *      ],
     * ]
     * ```
     *
     * @param string $config
     *
     * @return array
     */
    protected function normalizeStringConfigWithColon(string $config)
    {
        list($dispatchId, $viewFile) = explode(':', $config);
        
        return [
            $dispatchId => [
                'response' => [
                    'viewFile' => $viewFile,
                ],
            ],
        ];
    }
    
    /**
     * 格式化数组配置格式的配置数据
     *
     * ```php
     * [
     *      'index' => [],
     *      'index' => '{namespace}\{className}',
     *      'index' => 'home',
     *      'bootstrap-v3/index' => [],
     *      'bootstrap-v3/index' => '{namespace}\{className}',
     *      'bootstrap-v3/index' => 'home',
     *      'index' => 'home:home', // 用'Home'调度器代替'Index'调度器，并用'home'视图文件渲染页面
     *      'bootstrap-v3/index' => 'home:home', bootstrap-v3主题启用时，用'Home'调度器代替'Index'调度器，并用'home'视图文件渲染页面
     *      'index' => ':home', // 'Index'调度器用'home'视图文件渲染页面
     *      'bootstrap-v3/index' => ':home', bootstrap-v3主题启用时，'Index'调度器用'home'视图文件渲染页面
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
        if ($this->dm->getTheme()->isEnableTheme() && strpos($key, '/') !== false) {
            list($themeName, $dispatchId) = explode('/', $key);
            if (is_array($value)) {
                /**
                 * 'bootstrap-v3/index' => []
                 * 转换为
                 * [
                 *      '@bootstrap-v3' => [
                 *          'index' => [],
                 *      ],
                 * ]
                 */
                $arr['@' . $themeName][$dispatchId] = $value;
            } elseif (strpos($value, '\\') !== false) {
                /**
                 * 'bootstrap-v3/index' => '{namespace}\{className}'
                 * 转换为
                 * [
                 *      '@bootstrap-v3' => [
                 *          'index' => [
                 *              'class' => '{namespace}\{className}',
                 *          ],
                 *      ],
                 * ]
                 */
                $arr['@' . $themeName][$dispatchId]['class'] = $value;
            } elseif (is_string($value)) {
                if (strpos($value, ':') !== false) {
                    /**
                     * 'bootstrap-v3/index' => 'home:home'
                     * 转换为
                     * [
                     *      '@bootstrap-v3' => [
                     *          'index' => [
                     *              'map' => 'home',
                     *              'response' => [
                     *                  'viewFile' => 'home',
                     *              ],
                     *          ],
                     *      ],
                     * ]
                     */
                    $arr['@' . $themeName] = $this->normalizeStringConfigWithColonForMap($dispatchId, $value);
                } else {
                    /**
                     * 'bootstrap-v3/index' => 'home'
                     * 转换为
                     * [
                     *      '@bootstrap-v3' => [
                     *          'index' => [
                     *              'map' => 'home',
                     *          ],
                     *      ],
                     * ]
                     */
                    $arr['@' . $themeName][$key]['map'] = $value;
                }
            } else {
                $this->normalizeOtherValueConfig($arr, $key, $value);
            }
        } else {
            if (is_array($value)) {
                /**
                 * 'index' => []
                 * 转换为
                 * [
                 *      'index' => [],
                 * ]
                 */
                $arr[$key] = $value;
            } elseif (strpos($value, '\\') !== false) {
                /**
                 * 'index' => '{namespace}\{className}'
                 * 转换为
                 * [
                 *      'index' => [
                 *          'class' => '{namespace}\{className}',
                 *      ],
                 * ]
                 */
                $arr[$key]['class'] = $value;
            } elseif (is_string($value)) {
                if (strpos($value, ':') !== false) {
                    /**
                     * 'index' => 'home:home'
                     * 转换为
                     * [
                     *      'index' => [
                     *          'map' => 'home',
                     *          'response' => [
                     *              'viewFile' => 'home',
                     *          ],
                     *      ],
                     * ]
                     */
                    $arr = $this->normalizeStringConfigWithColonForMap($key, $value);
                } else {
                    /**
                     * 'index' => 'home'
                     * [
                     *      'index' => [
                     *          'map' => 'home',
                     *      ],
                     * ]
                     */
                    $arr[$key]['map'] = $value;
                }
            } else {
                $this->normalizeOtherValueConfig($arr, $key, $value);
            }
        }
        
        return $arr;
    }
    
    /**
     * 格式化键值带冒号格式的数组配置数据
     *
     * ```php
     * ['index' => 'index:home']
     * // 转换为
     * [
     *  'index' => [
     *      'map' => 'index',
     *      'response' => [
     *          'viewFile' => 'home',
     *      ],
     *  ],
     * ]
     * ```
     *
     * @param string $dispatchId
     * @param string $config
     *
     * @return array
     */
    protected function normalizeStringConfigWithColonForMap(string $dispatchId, string $config)
    {
        list($mapDispatchId, $viewFile) = explode(':', $config);
        
        return [
            $dispatchId => [
                'map'      => $mapDispatchId ?: $dispatchId,
                'response' => [
                    'viewFile' => $viewFile,
                ],
            ],
        ];
    }
    
}