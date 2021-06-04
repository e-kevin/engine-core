<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m210515_143620_add_created_at_to_config extends Migration
{
    
    public function safeUp()
    {
        $this->addColumn($this->createTableNameWithCode('config'), 'created_at',
            $this->integer()->unsigned()->notNull()->comment('安装时间')
        );
    }
    
    public function safeDown()
    {
        $this->dropColumn($this->createTableNameWithCode('config'), 'created_at');
    }
    
}