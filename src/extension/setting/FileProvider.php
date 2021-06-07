<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use Yii;
use yii\base\BaseObject;

/**
 * 文件方式的设置数据提供器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class FileProvider extends BaseObject implements SettingProviderInterface
{
    
    use SettingProviderTrait;
    
    private $_all;
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        if (null === $this->_all) {
            $settingFile = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->settingFile);
            $userSettingFile = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->userSettingFile);
            $this->_all = ArrayHelper::superMerge(
                require("$settingFile"),
                require("$userSettingFile"),
                Yii::$app->params[self::SETTING_KEY] ?? []
            );
        }

        return $this->_all;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_all = null;
    }
    
}