<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/12/6 21:42,
 * @LastEditTime: 2021/12/6 21:42
 */

namespace Liujun\Auth\Interfaces;

/**
 * Interface UserServiceInterface
 * @package Kabel\Rpc\Interfaces
 * 用户信息
 */
interface UserServiceInterface
{
    /**
     * 重新获取登录用户缓存
     * @param  string  $token
     * @param  string  $action  客户端
     * @return mixed
     */
    public function getLoginUser(string $token,string $action);

    /**
     * 续期更新用户TOKEN
     * @param  string  $oldToken
     * @param  string  $token
     * @param  string  $action  客户端
     * @return mixed
     */
    public function updateToken(string $oldToken,string $token,string $action);

    /**
     * 获取用户权限数组
     * @param  int  $userId
     * @param  int  $appId
     * @return mixed
     */
    public function getPermissions(int $userId,int $appId);

    /**
     * 获取对应产品的所有权限
     * @param  int  $productId  产品ID
     * @return mixed
     */
    public function getProductPermissions(int $productId);
}
