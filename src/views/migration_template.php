<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/* @var $className string the new migration class name */

echo "<?php\n";
?>
use EngineCore\db\Migration;

class <?= $className ?> extends Migration
{

    public function safeUp()
    {
    }

    public function safeDown()
    {
    }

}
