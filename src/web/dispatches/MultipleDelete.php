<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\web\dispatches;

use EngineCore\db\ActiveRecord;
use EngineCore\helpers\StringHelper;
use EngineCore\Ec;
use yii\base\InvalidConfigException;

/**
 * 根据模型`$modelClass`删除指定的多条数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MultipleDelete extends Delete
{
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!isset($this->modelClass)) {
            throw new InvalidConfigException("The property `modelClass` should be set in controller `\$defaultDispatches`.");
        }
        if (!class_exists($this->modelClass)) {
            throw new InvalidConfigException("Model class `{$this->modelClass}` does not exists");
        }
        parent::init();
    }
    
    /**
     * {@inheritdoc}
     */
    public function run($id)
    {
        $res = false;
        /** @var ActiveRecord[] $items */
        $items = $this->modelClass::findAll(StringHelper::parseIds($id));
        foreach ($items as $item) {
            $res = Ec::transaction(function () use ($item) {
                if ($this->markAsDeleted === true) {
                    $item->setAttribute($this->deletedMarkAttribute, $this->deletedMarkValue);
                    
                    return $item->save(false);
                } else {
                    return $item->delete();
                }
            });
            if (!$res) {
                break;
            }
        }
        
        return $this->getResult($res);
    }
    
}