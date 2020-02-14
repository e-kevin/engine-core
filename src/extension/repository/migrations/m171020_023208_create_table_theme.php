<?php
/**
 * @link https://github.com/EngineCore/module-extension
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m171020_023208_create_table_theme extends Migration
{
    
    public function safeUp()
    {
        $this->createTable('{{%viMJHk_theme}}', [
            'id'             => $this->string(64)->notNull()->comment('扩展ID'),
            'extension_name' => $this->char(255)->notNull()->comment('扩展名称'),
            'is_system'      => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('系统扩展 0:否 1:是'),
            'status'         => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('状态 0:禁用 1:启用'),
            'run'            => $this->boolean()->unsigned()->notNull()->defaultValue(0)
                                     ->comment('运行模式 0:系统扩展 1:开发者扩展'),
        ], $this->tableOptions . $this->buildTableComment('系统主题扩展'));
        
        $this->addPrimaryKey('unique', '{{%viMJHk_theme}}', 'id');
        
        $this->createIndex('idx-viMJHk_theme-extension_name', '{{%viMJHk_theme}}', 'extension_name');
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%viMJHk_theme}}');
    }
    
}
