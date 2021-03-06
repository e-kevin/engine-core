<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m210402_135250_create_config_table extends Migration
{
    
    public function safeUp()
    {
        $this->createTable($this->createTableNameWithCode('config'), [
            'unique_id'   => $this->char(32)->unsigned()->notNull()->comment('ID'),
            'unique_name' => $this->string(50)->notNull()->comment('扩展完整名称，开发者名+扩展名'),
            'app'         => $this->string(10)->notNull()->comment('所属应用'),
            'is_system'   => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('系统扩展 0:否 1:是'),
            'status'      => $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('状态 0:禁用 1:启用'),
            'run'         => $this->boolean()->unsigned()->notNull()->defaultValue(0)
                                  ->comment('运行模式 0:系统扩展 1:开发者扩展'),
            'version'     => $this->string(30)->notNull()->comment('版本'),
            'category'    => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('扩展分类'),
        ], $this->tableOptions . $this->buildTableComment('系统配置扩展'));
        
        $this->addPrimaryKey(
            'uniqueId',
            $this->createTableNameWithCode('config'),
            ['unique_id', 'app']
        );
        $this->createIndexWithCode('config', 'app');
        $this->createIndexWithCode('config', 'unique_id');
    }
    
    public function safeDown()
    {
        $this->dropTable($this->createTableNameWithCode('config'));
    }
    
}