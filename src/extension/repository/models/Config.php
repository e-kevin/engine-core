<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\models;

use EngineCore\Ec;

/**
 * This is the model class for table "{{%viMJHk_config}}".
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Config extends BaseExtensionModel implements ConfigModelInterface
{

    public static function tableName()
    {
        return '{{%viMJHk_config}}';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            // uniqueId rules
            'uniqueIdUnique' => [
                ['unique_name', 'app']
                , 'unique', 'targetAttribute' => ['unique_name', 'app'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert || isset($changedAttributes['status'])) {
            // 清理缓存
            Ec::$service->getExtension()->getRepository()->clearCache();
            Ec::$service->getMenu()->getConfig()->clearCache();
            Ec::$service->getSystem()->getSetting()->clearCache();
            // 刷新配置
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        }
    }

}