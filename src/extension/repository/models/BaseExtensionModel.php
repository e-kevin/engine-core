<?php
/**
 * @link https://github.com/EngineCore/module-extension
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use EngineCore\db\ActiveRecord;
use EngineCore\Ec;
use EngineCore\extension\ControllerInfo;
use EngineCore\extension\ExtensionInfo;
use EngineCore\extension\ModularityInfo;
use EngineCore\extension\ThemeInfo;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\AfterSaveEvent;

/**
 * 扩展通用模型类，一般主题扩展、模块扩展、控制器扩展需要继承该类
 *
 * @property string  $id
 * @property string  $extension_name
 * @property integer $is_system
 * @property integer $status
 * @property integer $run
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class BaseExtensionModel extends ActiveRecord implements RepositoryModelInterface
{
    
    /**
     * @var ModularityInfo|ControllerInfo|ThemeInfo 扩展信息类
     */
    protected $_infoInstance;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'extension_name'], 'required'],
            [['id', 'extension_name'], 'unique'],
            [['is_system', 'status', 'run'], 'integer'],
            [['id'], 'string', 'max' => 64],
            [['extension_name'], 'string', 'max' => 255],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'extension_name' => Yii::t('ec/extension', 'Extension Name'),
            'is_system'      => Yii::t('ec/extension', 'Is System'),
            'status'         => Yii::t('ec/app', 'Status'),
            'run'            => Yii::t('ec/extension', 'Run Mode'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'is_system' => Yii::t('ec/extension', 'Cannot uninstall after installation.'),
            'run'       => Yii::t('ec/extension', 'Select which extension configuration to run the current extension.'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return self::find()->asArray()->all();
    }
    
    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return $this->_infoInstance;
    }
    
    /**
     * @inheritdoc
     */
    public function setInfoInstance($info)
    {
        if (!is_subclass_of($info, ExtensionInfo::class)) {
            throw new InvalidConfigException('The `info` property must return an object extends `' . ExtensionInfo::class . '`.');
        }
        $this->_infoInstance = $info;
    }
    
    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        // 检查是否满足扩展依赖关系
        if (!Ec::$service->getExtension()->getDependent()->checkDependencies($this->extension_name)) {
            $this->_result = Ec::$service->getExtension()->getDependent()->getInfo();
            
            return false;
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        if (!$this->getInfoInstance()->canUninstall) {
            $this->_result = $this->extension_name . Yii::t('ec/extension',
                    'The extension is a system extension and uninstallation is not supported for the time being.'
                );
            
            return false;
        } else {
            $arr = [];
            $i = 1;
            // 获取已经安装的扩展，检测当前扩展是否存在依赖关系
            foreach (Ec::$service->getExtension()->getRepository()->getInstalled() as $uniqueName => $row) {
                if ($uniqueName == $this->extension_name) {
                    continue;
                }
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = Ec::$service->getExtension()->getRepository()->getLocalConfiguration()[$uniqueName]['infoInstance'];
                // 获取依赖关系
                foreach ($infoInstance->getDepends() as $name => $version) {
                    if ($name == $this->extension_name) {
                        $arr[] = $i++ . ') ' . $uniqueName;
                    }
                }
            }
            
            if ($arr) {
                $this->_result = Yii::t('ec/extension',
                    'Please remove the following extended dependencies before performing the current operation:{operation}',
                    ['operation' => implode("\n", $arr)]
                );
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        //todo 改为事件
        parent::afterDelete();
        $this->getInfoInstance()->uninstall(); // 调用扩展内置卸载方法
        Ec::$service->getMenu()->getConfig()->sync(); // 同步菜单
        Ec::$service->getExtension()->getEnvironment()->flushConfigFiles(false); // 刷新配置
    }
    
    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $res = true;
        if ($insert) {
            $res = $this->getInfoInstance()->install(); // 调用扩展内置安装方法
        }
        if ($res) {
            $this->trigger(self::EVENT_SYNC, new AfterSaveEvent([
                'changedAttributes' => $changedAttributes,
            ]));
        }
    }
    
}
