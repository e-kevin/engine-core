<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m210326_063004_add_category_to_module extends Migration
{
    
    public function safeUp()
    {
        $this->addColumn($this->createTableNameWithCode('module'), 'category',
            $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)->comment('扩展分类')
        );
    }
    
    public function safeDown()
    {
        $this->dropColumn($this->createTableNameWithCode('module'), 'category');
    }
    
}