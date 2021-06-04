<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;

class m210405_143620_add_bootstrap_to_module extends Migration
{
    
    public function safeUp()
    {
        $this->addColumn($this->createTableNameWithCode('module'), 'bootstrap',
            $this->boolean()->unsigned()->notNull()->defaultValue(0)->comment('启用bootstrap 0:不启用 1:启用')
        );
    }
    
    public function safeDown()
    {
        $this->dropColumn($this->createTableNameWithCode('module'), 'bootstrap');
    }
    
}