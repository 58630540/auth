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
    'sign'=>[//签名信息
        'app_key' => env('KABEL_APP_KEY',''),
        'secret' => env('KABEL_APP_SCRET',''),
    ],
    'exception_code'=>[//异常码
        'no_login' => [100,'请登录！']
    ],
    'api_host'=>[//外部依赖接口域名
        'saas_api' =>  env('SAAS_API_HOST', 'https://api.paiplus.cn')
    ],
    'user_service' => null,//服务实现类
    'user'=>[//saas B端用户配置
        'jwt'=>[//JWT 相关配置
            'conf' => [
                //该JWT的签发者
                'iss' => 'paijia_jwt',
                //签发时间
                'iat' => time(),
                //过期时间
                'exp' => time() + 3600 * 24 * 30,
                //该时间之前不接收处理该Token
                'nbf' => time(),
                'ticket' => md5(uniqid('PJ') . time()) //该Token唯一标识
            ],
            'key' =>env('JWT_USER_KEY', 'paijia@2021'),
            'auto_renewal_threshold' => 10,//对应过期五秒内的token自运续期
            'leeway' => 5,//token允许过期的容错值
            'allowed' => ['alg' => 'HS256'],
        ],
        'cache_key' => [
            'key' =>'user:loginCache:' . '%s',//%s为用户ID
            'expire' => 3600 * 24 * 30
        ],
    ],
    'cUser'=>[//C端商城用户
        'jwt'=>[//JWT 相关配置
            'conf' => [
                //该JWT的签发者
                'iss' => 'paijia_cjwt',
                //签发时间
                'iat' => time(),
                //过期时间
                'exp' => time() + 3600 * 24 * 30,
                //该时间之前不接收处理该Token
                'nbf' => time(),
                'ticket' => md5(uniqid('PJC') . time()) //该Token唯一标识
            ],
            'key' => env('JWT_CUSER_KEY', 'paijia@2022'),
            'auto_renewal_threshold' => 10,//对应过期五秒内的token自运续期
            'leeway' => 5,//token允许过期的容错值
            'allowed' => ['alg' => 'HS256'],
        ],
        'cache_key' => [
            'key' =>'cUser:loginCache:' . '%s',//%s为用户ID
            'expire' => 3600 * 24 * 30
        ],
    ],
    'aUser'=>[//C端活动用户
        'jwt'=>[//JWT 相关配置
            'conf' => [
                //该JWT的签发者
                'iss' => 'paijia_ajwt',
                //签发时间
                'iat' => time(),
                //过期时间
                'exp' => time() + 3600 * 24 * 30,
                //该时间之前不接收处理该Token
                'nbf' => time(),
                'ticket' => md5(uniqid('PJA') . time()) //该Token唯一标识
            ],
            'key' =>env('JWT_AUSER_KEY', 'paijia@2022'),
            'auto_renewal_threshold' => 10,//对应过期五秒内的token自运续期
            'leeway' => 5,//token允许过期的容错值
            'allowed' => ['alg' => 'HS256'],
            ],
        'cache_key' => [
            'key' =>'aUser:loginCache:' . '%s',//%s为用户ID
            'expire' => 3600 * 24 * 30
        ],
    ]
];
