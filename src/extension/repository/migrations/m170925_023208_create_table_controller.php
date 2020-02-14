<?php
/**
 * @link https://github.com/EngineCore/module-extension
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m170925_023208_create_table_controller extends Migration
{
    
    public function safeUp()
    {
        $this->createTable('{{%viMJHk_controller}}', [
            'id'             => $this->string(64)->notNull()->comment('扩展ID'),
            'extension_name' => $this->char(255)->notNull()->comment('扩展名称'),
            'module_id'      => $this->char(15)->notNull()->comment('模块ID'),
            'controller_id'  => $this->char(64)->notNull()->comment('控制器ID'),
            'is_system'      => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('系统扩展 0:否 1:是'),
            'status'         => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('状态 0:禁用 1:启用'),
            'run'            => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('运行模式 0:系统扩展 1:开发者扩展'),
        ], $this->tableOptions . $this->buildTableComment('系统控制器扩展'));
        
        $this->addPrimaryKey('unique', '{{%viMJHk_controller}}', 'id');
        
        $this->createIndex('idx-viMJHk_controller-extension_name', '{{%viMJHk_controller}}', 'extension_name');
    }
    
    public function safeDown()
    {
        $this->dropTable('{{%viMJHk_controller}}');
    }
    
}
