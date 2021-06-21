<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

return [
    // attributes
    'Name' => '标识',
    'Title' => '标题',
    'Extra' => '额外数据',
    'Description' => '设置说明',
    'Value' => '默认值',
    'Type' => '类型',
    'Category' => '分组',
    // hints
    'Only English can be used and cannot be repeated.' => '只能使用英文且不能重复',
    'Configuration Title for Background Display.' => '用于后台显示的设置标题',
    'Configuration details.' => '设置详细说明',
    'This item needs to be configured for the type of select, radio and checkbox.' => implode('</br>', [
        '【下拉框、单选框、多选框】类型需要设置该项',
        '多个可用英文符号[`,`, `;`]或换行分隔，如：',
        '逗号 ,',
        'key:value, key1:value1, key2:value2',
        '分号 ;',
        'key:value; key1:value1; key2:value2',
        '换行',
        'key:value',
        'key1:value1',
        'key2:value2',
    ]),
    'The system will analyze the configuration data according to different types.' => '系统会根据不同类型解析设置数据',
    'Settings without grouping will not appear in system settings.' => '没有分组的设置不会显示在系统设置中',
];
