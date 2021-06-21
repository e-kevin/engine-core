<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\db;

use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\helpers\MigrationHelper;

/**
 * Class Migration
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Migration extends \yii\db\Migration
{
    
    /**
     * @var string 数据库表前缀随机码
     */
    protected $randCode = ExtensionInfo::EXT_RAND_CODE;
    
    /**
     * @var string the table options
     */
    protected $tableOptions = '';
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
    }
    
    /**
     * 设置表注释
     *
     * @param string $comment 表注释
     *
     * @return string
     */
    protected function buildTableComment(string $comment = ''): string
    {
        return $comment !== '' ? ' COMMENT = ' . $this->db->quoteValue($comment) : '';
    }
    
    /**
     * 设置外键约束
     *
     * @param boolean $check 默认为`false`，取消约束
     */
    protected function setForeignKeyCheck($check = false)
    {
        $this->execute('SET foreign_key_checks = ' . (int)$check . ';');
    }
    
    /**
     * 创建带随机码的数据库表名
     *
     * @param string $table
     *
     * @return string
     */
    public function createTableNameWithCode(string $table): string
    {
        return MigrationHelper::createTableName($table, $this->randCode);
    }
    
    /**
     * 创建带随机码的索引名
     *
     * @param string       $table
     * @param string|array $columns
     * @param string       $name
     * @param bool         $unique
     */
    public function createIndexWithCode(string $table, $columns, $name = null, $unique = false)
    {
        $name = MigrationHelper::createIndexName($table, $columns, $name, $this->randCode);
        parent::createIndex($name, $this->createTableNameWithCode($table), $columns, $unique);
    }
    
}