<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m200715_125750_create_theme_table extends Migration
{
    
    public function safeUp()
    {
        $this->createTable($this->createTableNameWithCode('theme'), [
            'unique_id'   => $this->char(32)->unsigned()->notNull()->comment('ID'),
            'unique_name' => $this->string(50)->notNull()->comment('扩展完整名称，开发者名+扩展名'),
            'app'         => $this->string(10)->notNull()->comment('所属应用'),
            'theme_id'    => $this->string(15)->notNull()->comment('主题ID'),
            'is_system'   => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('系统扩展 0:否 1:是'),
            'status'      => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('状态 0:禁用 1:启用'),
            'run'         => $this->boolean()->unsigned()->notNull()->defaultValue(0)
                                  ->comment('运行模式 0:系统扩展 1:开发者扩展'),
        ], $this->tableOptions . $this->buildTableComment('系统主题扩展'));
        
        $this->addPrimaryKey(
            'uniqueId',
            $this->createTableNameWithCode('theme'),
            ['unique_id', 'app', 'theme_id']
        );
        $this->createIndexWithCode('theme', 'app');
        $this->createIndexWithCode('theme', 'unique_id');
    }
    
    public function safeDown()
    {
        $this->dropTable($this->createTableNameWithCode('theme'));
    }
    
}