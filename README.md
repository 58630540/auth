### 安装

1. 下载组件包
   ```shell
   composer require kabel-liujun/auth
   ```

2. 发布配置文件

    a.kabel_auth.php: auth配置文件

    b.kabel_auth_cache.php: redis缓存链接配置文件
   
   执行如下命令发布配置文件
   ```shell
   php artisan vendor:publish --provider="Liujun\Auth\AuthServiceProvide"
   ```

###  授权中间件使用
1.在项目根目录/kabel/Kernel/HttpKernel.php中绑定 \Liujun\Auth\Middleware\CheckUserToken::class 中间件
````php

 /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Kabel\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Kabel\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // 绑定用户授权中间件
        'user.token' => \Liujun\Auth\Middleware\CheckUserToken::class,
    ];
````
2.路由绑定中间件
````php
    Route::middleware('user.token')->group(function () {
        //需要登录的路由组
    });
````
3.获取当前登录用户的信息 在要使用的文件件 引入
````php
   use Liujun\Auth\Traits\UserCacheTrait;
    class test
    {
        use UserCacheTrait;
        public function test()
        {
           $this->_getToken();//获取当前用户的token
           //以下所有方法都可以传一下bool直参数用来判断是否抛出异常，适合于可登录也不可登录的场景使用
           $this->_getUser();//获取登录用户所有信息
           $this->_getUserId();//获取用户ID
           $this->_getCompany();//获取当前用户所在企业
           $this->_getAppId();//获取当前用户登录的应用ID
           $this->_getProductId();//获取当前用户登录的产品Id
        }
    }
````

