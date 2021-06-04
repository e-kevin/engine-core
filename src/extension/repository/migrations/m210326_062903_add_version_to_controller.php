<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m210326_062903_add_version_to_controller extends Migration
{
    
    public function safeUp()
    {
        $this->addColumn($this->createTableNameWithCode('controller'), 'version',
            $this->string(30)->notNull()->comment('版本')
        );
    }
    
    public function safeDown()
    {
        $this->dropColumn($this->createTableNameWithCode('controller'), 'version');
    }
    
}