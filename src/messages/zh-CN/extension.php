<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

return [
    // attributes
    'Theme Id' => '主题ID',
    'Unique Name' => '扩展名称',
    'App' => '所属应用',
    'Module Id' => '模块ID',
    'Bootstrap' => '自启模式',
    'Controller Id' => '控制器ID',
    'Is System' => '核心扩展',
    'Run Mode' => '运行模式',
    'Version' => '版本',
    'Status' => '状态',
    'Category' => '扩展分类',
    // hints
    'The system extension cannot be uninstall.' => '系统核心扩展无法卸载',
    'Select which extension configuration to run the current extension.' => '选择哪个扩展配置来运行当前扩展',
    'When empty, the extension is `{app}` application extension.' => '为空时，该扩展则为`{app}`应用扩展',
    'When bootstrap is enabled, the current module will automatically load the bootstrap program after the application starts.'
    => '启用自启模式后，当前模块将在应用启动后自动加载bootstrap程序',
    // common
    'The extension is a system extension and uninstall is not supported for the time being.' => '扩展属于系统扩展，暂不支持卸载。',
    'Please remove the following extended dependencies before performing the current operation:{operation}'
    => "请先解除以下扩展依赖关系再执行当前操作：\n{operation}",
];
