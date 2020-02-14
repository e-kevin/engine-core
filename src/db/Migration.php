<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\db;

/**
 * Class Migration
 */
class Migration extends \yii\db\Migration
{
    /**
     * @var string the table options
     */
    protected $tableOptions = '';
    
    /**
     * @inheritdoc
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
    protected function buildTableComment($comment = '')
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
    
}
