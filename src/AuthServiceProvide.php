<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2021/10/27 16:03,
 * @LastEditTime: 2021/10/27 16:03
 */

namespace Liujun\Auth;


use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Liujun\Auth\Interfaces\RpcRequestInterface;
use Liujun\Auth\Interfaces\UserServiceInterface;
use Liujun\Auth\Services\HttpRequest;
use Liujun\Auth\Services\UserService;

class AuthServiceProvide extends ServiceProvider
{
    protected array $command = [
    ];

    // 迁移文件注册命令
    protected array $migrateRegisterCommands = [
    ];

    public function register()
    {
        // 发布模板文件
        $this->registerPublishing();
        // 注册配置文件
        $this->mergeConfigFrom(
            __DIR__ . '/Config/kabel_auth_cache.php', 'database.redis'
        );
        $this->app->instance(RpcRequestInterface::class, $this->app->make(HttpRequest::class));
        $this->app->instance(UserServiceInterface::class, $this->app->make(UserService::class));
    }

    /**
     * 发布模板文件
     * @author lwz
     */
    protected function registerPublishing()
    {
        // 只有在 console 模式才执行
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/kabel_auth.php' => $this->app->basePath('Config').'/kabel_auth.php',
                __DIR__ . '/Config/kabel_auth_cache.php' => $this->app->basePath('Config').'/kabel_auth_cache.php'
            ]);
        }
    }

}