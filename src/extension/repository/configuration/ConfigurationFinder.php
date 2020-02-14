<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension\repository\configuration;

use EngineCore\Ec;
use EngineCore\helpers\FileHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * 扩展配置文件搜索器抽象类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class ConfigurationFinder extends BaseObject implements ConfigurationFinderInterface
{
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (null === $this->getSearchFileName()) {
            throw new InvalidConfigException('The `searchFileName` property must be set.');
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getConfigFiles(): array
    {
        $cacheService = Ec::$service->getSystem()->getCache();
        
        return $cacheService->getOrSet(
            ConfigurationFinderInterface::CACHE_LOCAL_EXTENSION_CONFIG_FILE,
            function () {
                $files = FileHelper::findFiles(Yii::getAlias('@extensions'), [
                    'only' => [$this->getSearchFileName()],
                ]);
                if (empty($files)) {
                    return [];
                }
                
                return $this->readConfigFiles($files);
            });
    }
    
    /**
     * 读取配置文件的配置信息
     *
     * @param array $files 配置文件路径
     *
     * @return array 处理后的扩展配置信息
     */
    abstract protected function readConfigFiles($files);
    
    /**
     * 生成扩展配置信息
     *
     * @param array $data 配置数据
     *
     * @return Configuration
     */
    abstract protected function createConfiguration($data);
    
    /**
     * @inheritdoc
     */
    public function clearCache()
    {
        Ec::$service->getSystem()->getCache()->delete(ConfigurationFinderInterface::CACHE_LOCAL_EXTENSION_CONFIG_FILE);
    }
    
    private $_fileName;
    
    /**
     * @inheritdoc
     */
    public function setSearchFileName(string $name)
    {
        $this->_fileName = $name;
    }
    
    /**
     * @inheritdoc
     */
    public function getSearchFileName(): string
    {
        return $this->_fileName;
    }
    
}