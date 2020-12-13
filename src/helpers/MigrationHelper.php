<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\helpers;

use yii\base\BaseObject;

/**
 * 数据库迁移助手类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MigrationHelper extends BaseObject
{
    
    /**
     * 创建带表前缀的数据表名
     *
     * @param string $table
     * @param string $randCode
     *
     * @return string
     */
    public static function createTableName(string $table, string $randCode)
    {
        return '{{%' . $randCode . $table . '}}';
    }
    
    /**
     * 创建带表前缀的索引名
     *
     * @param string       $table
     * @param string|array $columns
     * @param string       $name
     * @param string       $randCode
     *
     * @return string
     */
    public static function createIndexName(string $table, $columns, $name = null, string $randCode)
    {
        return 'idx-' . $randCode . $table . '-' . ($name ?: implode('-', (array)$columns));
    }
    
}