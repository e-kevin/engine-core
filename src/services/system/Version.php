<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\base\Service;
use EngineCore\services\System;
use Jelix\Version\VersionComparator;
use OutOfBoundsException;
use PackageVersions\Versions;

/**
 * 版本验证服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Version extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * 验证版本是否满足
     *
     * @param string $currentVersion 当前版本，通常为特定的版本号，如：`dev-main`,`dev-branch`,`@dev`,`0.1.1`,`0.1-patch`等
     * @param string $ruleVersion 规则版本，通常包含有特定符号的版本规则，如：`~0.1.1`,`^0.1.1`,`@dev`,`*`等
     *
     * @return bool
     */
    public function compare($currentVersion, $ruleVersion): bool
    {
        if ('*' === $ruleVersion || false !== strpos($ruleVersion, 'dev')) {
            return true;
        }
        if (false !== strpos($currentVersion, 'dev')) {
            return false;
        }
        
        return VersionComparator::compareVersionRange($currentVersion, $ruleVersion);
    }
    
    /**
     * 获取composer包的版本
     *
     * @param string $name composer包名，如：e-kevin/engine-core
     *
     * @return string
     */
    public function getComposerVersion($name)
    {
        try {
            $version = Versions::getVersion($name);
            list($version, $branch) = explode('@', $version);
            if (preg_match('{^[vV]}', $version)) {
                $version = substr($version, 1);
            }
        } catch (OutOfBoundsException $e) {
            $version = '';
        }
        
        return $version;
    }
    
}