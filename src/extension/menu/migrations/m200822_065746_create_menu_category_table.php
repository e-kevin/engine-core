<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m200822_065746_create_menu_category_table extends Migration
{
    
    public function safeUp()
    {
        $this->createTable($this->createTableNameWithCode('menu_category'), [
            'id'          => $this->primaryKey()->unsigned()->comment('ID'),
            'name'        => $this->string(64)->notNull()->comment('菜单名'),
            'description' => $this->string(512)->comment('菜单描述'),
        ], $this->tableOptions . $this->buildTableComment('菜单分类表'));
        
        $this->batchInsert($this->createTableNameWithCode('menu_category'), ['id', 'name'], [
            ['backend', '后台菜单'],
            ['frontend', '前台菜单'],
            ['footer', '底部菜单'],
        ]);
    }
    
    public function safeDown()
    {
        $this->dropTable($this->createTableNameWithCode('menu_category'));
    }
    
}