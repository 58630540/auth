<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2022/01/10 10:07,
 * @LastEditTime: 2022/01/10 10:07
 */

/**
 * 用户统一认证组件相关配置
 */
return [
    'authCache' => [ //缓存驱动配置
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'predis'),
            'prefix' => '',
        ],
    ]
];
