<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

use EngineCore\db\Migration;
use EngineCore\Ec;
use EngineCore\extension\setting\FileProvider;

class m200822_023219_create_setting_table extends Migration
{
    
    public function safeUp()
    {
        $provider = Ec::$service->getSystem()->getSetting()->getProvider();
        $this->createTable($this->createTableNameWithCode('setting'), [
            'id'                             => $this->primaryKey()->unsigned()->comment('ID'),
            $provider->getNameField()        => $this->string(30)->unique()->notNull()->comment('标识'),
            $provider->getTitleField()       => $this->string(20)->notNull()->comment('配置标题'),
            $provider->getExtraField()       => $this->string(255)
                                                     ->notNull()->defaultValue('')->comment('配置数据'),
            $provider->getDescriptionField() => $this->string(255)
                                                     ->notNull()->defaultValue('')->comment('配置说明'),
            $provider->getValueField()       => $this->text()->notNull()->comment('默认值'),
            $provider->getTypeField()        => $this->boolean()->unsigned()->notNull()->defaultValue($provider::TYPE_STRING)
                                                     ->comment('设置类型'),
            $provider->getCategoryField()    => $this->boolean()->unsigned()->notNull()->defaultValue($provider::CATEGORY_NONE)
                                                     ->comment('设置分组'),
        ], $this->tableOptions . $this->buildTableComment('系统设置表'));
        
        // 转存数据
        $this->transferToDb();
    }
    
    public function safeDown()
    {
        // 转存数据
        if ($this->transferToFile()) {
            // 清除旧的缓存数据
            Ec::$service->getSystem()->getSetting()->clearCache();
            // 数据转存成功后，临时调用文件方式的数据提供器
            Ec::$service->getSystem()->getSetting()->setProvider(FileProvider::class);
            $this->dropTable($this->createTableNameWithCode('setting'));
        } else {
            throw new \Exception('Failed to transfer configuration file.');
        }
    }
    
    /**
     * 转存数据到数据库
     */
    protected function transferToDb()
    {
        $provider = Ec::$service->getSystem()->getSetting()->getProvider();
        $data = [];
        $fieldMap = $provider->getFieldMap();
        foreach ($provider->getAll() as $row) {
            $arr = [];
            foreach ($row as $field => $value) {
                // 只转存默认字段的数值
                if (isset($fieldMap[$field])) {
                    $arr[$fieldMap[$field]] = $value;
                }
            }
            $data[] = $arr;
        }
        
        $this->batchInsert($this->createTableNameWithCode('setting'), array_keys($data[0]), $data);
    }
    
    /**
     * 转存数据到文件
     *
     * @return bool
     */
    protected function transferToFile(): bool
    {
        $config = [];
        $fields = array_keys(Ec::$service->getSystem()->getSetting()->getProvider()->getFieldMap());
        $settings = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->settingFile);
        $settings = require("$settings");
        foreach (Ec::$service->getSystem()->getSetting()->getAll() as $key => $row) {
            $arr = [];
            foreach ($row as $field => $value) {
                // 只转存默认字段的数值
                if (in_array($field, $fields)) {
                    // 只储存和扩展设置不一样的数据
                    if (isset($settings[$key][$field])) {
                        if ($settings[$key][$field] != $value) {
                            $arr[$field] = $value;
                        }
                    } else {
                        $arr[$field] = $value;
                    }
                }
            }
            if (!empty($arr)) {
                $config[$key] = $arr;
            }
        }
        
        return Ec::$service->getExtension()->getEnvironment()->flushUserSettingFile($config);
    }
    
}