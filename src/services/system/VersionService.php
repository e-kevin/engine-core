<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\base\Service;
use EngineCore\services\System;
use Jelix\Version\VersionComparator;

/**
 * 扩展版本验证服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class VersionService extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * 验证版本是否满足
     *
     * @param string $currentVersion 当前版本，通常为特定的版本号，如：`dev-master`,`dev-branch`,`@dev`,`0.1.1`,`0.1-patch`等
     * @param string $ruleVersion 规则版本，通常包含有特定符号的版本规则，如：`~0.1.1`,`^0.1.1`,`@dev`等
     *
     * @return bool
     */
    public function compare($currentVersion, $ruleVersion): bool
    {
        if (('@dev' == substr($ruleVersion, 0, 4)) || (('dev-master' == $ruleVersion))) {
            return true;
        } elseif (('@dev' == substr($currentVersion, 0, 4)) || ('dev-master' == $currentVersion)) {
            return 'dev-master' == $ruleVersion;
        }
        
        return VersionComparator::compareVersionRange($currentVersion, $ruleVersion);
    }
    
}