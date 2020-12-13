<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\web\dispatches;

use EngineCore\{
    base\LoadModelTrait, db\ActiveRecord, Ec
};

/**
 * 根据模型`$modelClass`删除指定的单条数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DeleteOne extends Delete
{
    
    use LoadModelTrait;
    
    /**
     * {@inheritdoc}
     */
    public function run($id)
    {
        /** @var ActiveRecord $model */
        $model = $this->loadModel($this->modelClass, $id);
        
        return $this->getResult(Ec::transaction(function () use ($model) {
            if ($this->markAsDeleted === true) {
                $model->setAttribute($this->deletedMarkAttribute, $this->deletedMarkValue);
        
                return $model->save(false);
            } else {
                return $model->delete();
            }
        }));
    }
    
}