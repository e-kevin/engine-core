<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

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
     * @var string 需要搜索的配置文件名
     */
    public $searchFileName;
    
    /**
     * @var int|false 缓存时间
     */
    public $cacheDuration;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (null === $this->searchFileName) {
            throw new InvalidConfigException('The `searchFileName` property must be set.');
        }
    }
    
    /**
     * 搜索本地目录，获取所有扩展配置文件信息
     *
     * @return array
     */
    protected function getConfigFiles(): array
    {
        $files = FileHelper::findFiles(Yii::getAlias('@extensions'), [
            'only' => [$this->searchFileName],
        ]);
        
        return $files ?: [];
    }
    
    private $_configuration;
    
    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        if (null === $this->_configuration) {
            $this->_configuration = Ec::$service->getSystem()->getCache()->getOrSet(
                ConfigurationFinderInterface::CACHE_LOCAL_EXTENSION_CONFIGURATION,
                function () {
                    $data = [];
                    foreach ($this->getConfigFiles() as $file) {
                        if (null === $config = $this->getConfigurationByFile($file, false)) {
                            continue;
                        }
                        $data[$config->getName()] = $config;
                    }
                    
                    return $data;
                },
                $this->cacheDuration
            );
        }
        
        return $this->_configuration;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConfigurationByFile($file, $throwException = true)
    {
        $config = $this->read($file);
        if (empty($config) || empty($config['name'])) {
            if ($throwException) {
                throw new ConfigurationReadException($file);
            } else {
                return null;
            }
        }
        // 出于安全问题，这里把实际路径转换为别名路径
        $vendorDir = dirname($file);
        $config['vendorDir'] = '@extensions' . substr_replace(
                $vendorDir,
                '',
                0,
                strlen(Yii::getAlias('@extensions'))
            );
        
        return $this->createConfiguration($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        $aliases = [];
        foreach ($this->getConfiguration() as $uniqueName => $configuration) {
            foreach ($configuration->getAutoloadPsr4() as $row) {
                $alias = '@' . str_replace('\\', '/', rtrim($row['namespace'], '\\'));
                $aliases[$alias] = $row['path'];
            }
        }
        
        return $aliases;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getNamespaceMap(): array
    {
        $map = [];
        foreach ($this->getConfiguration() as $uniqueName => $configuration) {
            foreach ($configuration->getAutoloadPsr4() as $row) {
                $namespace = rtrim($row['namespace'], '\\');
                $map[$namespace] = $uniqueName;
            }
        }
        
        return $map;
    }
    
    /**
     * 读取配置文件
     *
     * @param string $file 配置文件
     *
     * @return array 处理后的扩展配置信息
     */
    abstract protected function read($file);
    
    /**
     * {@inheritdoc}
     */
    abstract public function readInstalledFile($file);
    
    /**
     * 生成扩展配置数据
     *
     * @param array $data
     *
     * @return Configuration
     */
    protected function createConfiguration(array $data)
    {
        return new Configuration(
            $data['vendorDir'],
            $data['name'],
            $data['type'] ?? null,
            $data['description'] ?? null,
            $this->getVersion($data),
            $data['keywords'] ?? null,
            $data['homepage'] ?? null,
            $this->createAuthors($data['authors'] ?? null),
            $this->createSupport($data['support'] ?? null),
            $data['require'] ?? [],
            $data['require-dev'] ?? [],
            $data['suggest'] ?? null,
            $this->createAutoloadPsr($data['autoload']['psr-0'] ?? null, $data['vendorDir']),
            $this->createAutoloadPsr($data['autoload']['psr-4'] ?? null, $data['vendorDir']),
            $this->createRepositories($data['repositories'] ?? null),
            $data['extra'] ?? null
        );
    }
    
    /**
     * 获取版本号
     *
     * @param array $config
     *
     * @return string
     */
    protected function getVersion($config)
    {
        /*
         * EngineCore默认使用'engine-core/installer-plugin' composer插件安装ec扩展，
         * 故扩展具体版本可从已安装的composer列表中获取。
         */
        return Ec::$service->getSystem()->getVersion()->getComposerVersion($config['name']) ?: (
            $config['extra']['branch-alias']['dev-main'] ?? 'dev-main'
        );
    }
    
    /**
     * 创建作者列表
     *
     * @param array $authors
     *
     * @return Author[]
     */
    protected function createAuthors($authors)
    {
        if (null !== $authors) {
            foreach ($authors as $key => $author) {
                $authors[$key] = $this->createAuthor($author);
            }
        }
        
        return $authors;
    }
    
    /**
     * 创建作者
     *
     * @param array $author
     *
     * @return Author
     * @throws UndefinedPropertyException
     */
    protected function createAuthor($author)
    {
        if (!isset($author['name']) && empty($author['name'])) {
            throw new UndefinedPropertyException('name');
        }
        
        return new Author(
            $author['name'],
            $author['email'] ?? null,
            $author['homepage'] ?? null,
            $author['role'] ?? null
        );
    }
    
    /**
     * 创建支持信息数据
     *
     * @param array|null $support
     *
     * @return SupportInformation
     */
    protected function createSupport($support = null)
    {
        if (null !== $support) {
            $support = new SupportInformation(
                $support['email'] ?? null,
                $support['issues'] ?? null,
                $support['forum'] ?? null,
                $support['wiki'] ?? null,
                $support['source'] ?? null,
                $support['irc'] ?? null
            );
        }
        
        return $support;
    }
    
    /**
     * 创建PSR格式自动加载信息
     *
     * @param array|null $autoloadPsr
     * @param string     $vendorDir
     *
     * @return array|null
     */
    protected function createAutoloadPsr($autoloadPsr = null, $vendorDir)
    {
        if (null !== $autoloadPsr) {
            foreach ($autoloadPsr as $namespace => $path) {
                if (is_array($path)) {
                    continue;
                }
                if (!FileHelper::isAbsolutePath($path)) {
                    $path = $vendorDir . DIRECTORY_SEPARATOR . $path;
                }
                unset($autoloadPsr[$namespace]);
                $autoloadPsr[] = [
                    'namespace' => $namespace,
                    'path'      => $path,
                ];
            }
        }
        
        return $autoloadPsr;
    }
    
    /**
     * 创建仓库列表数据
     *
     * @param array|null $repositories
     *
     * @return array|null
     */
    protected function createRepositories(array $repositories = null)
    {
        if (null !== $repositories) {
            foreach ($repositories as $index => $repository) {
                $repositories[$index] = $this->createRepository($repository);
            }
        }
        
        return $repositories;
    }
    
    /**
     * 创建仓库数据
     *
     * @param array $repository
     *
     * @return RepositoryInterface
     * @throws UndefinedPropertyException
     */
    protected function createRepository($repository)
    {
        if (!isset($repository['type']) && empty($repository['type'])) {
            throw new UndefinedPropertyException('type');
        }
        $type = $repository['type'];
        if ('package' === $type) {
            if (!isset($repository['package']) && empty($repository['package'])) {
                throw new UndefinedPropertyException('package');
            }
            $repository = new PackageRepository(
                $repository['package'],
                $repository
            );
        } else {
            $repository = new Repository(
                $type,
                $repository['url'] ?? null,
                $repository
            );
        }
        
        return $repository;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        Ec::$service->getSystem()->getCache()->getComponent()->delete(ConfigurationFinderInterface::CACHE_LOCAL_EXTENSION_CONFIGURATION);
        $this->_configuration = null;
    }
    
}