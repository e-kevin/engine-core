<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\migration;

use yii\base\BaseObject;

/**
 * Class Controller
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Controller extends BaseObject
{
    
    /**
     * @param $string
     *
     * @return mixed
     */
    public function stdout(string $string)
    {
        return ;
    }
    
    public function confirm()
    {
        return true;
    }
    
}