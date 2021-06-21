<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\db;

use EngineCore\base\CacheDurationTrait;
use EngineCore\base\DataCacheInterface;
use EngineCore\base\ExtendModelTrait;
use yii\db\ActiveRecord as baseActiveRecord;

/**
 * 基础ActiveRecord类
 *
 * 注意：
 * 任何验证操作后需要反馈相关信息给客户端的，如：前置操作（以beforeXyz()这样格式命名的方法），在显示提示信息时，
 * 建议用[[ExtendModelTrait::getErrorService()->addModelOtherErrors()]]方法来储存信息，剩下的呈现问题统一交由
 * 调度响应器负责。
 * @see    \EngineCore\services\system\Error::addModelOtherErrors() 描述
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ActiveRecord extends baseActiveRecord implements DataCacheInterface
{
    
    use ExtendModelTrait, CacheDurationTrait;
    
    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->clearCache();
    }
    
    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->clearCache();
    }
    
    public function getAll()
    {
    }
    
    public function clearCache()
    {
    }
    
}