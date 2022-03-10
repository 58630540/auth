<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/12/6 21:40,
 * @LastEditTime: 2021/12/6 21:40
 */

namespace Liujun\Auth\Services;

use Liujun\Auth\Exceptions\UnauthorizedException;
use Liujun\Auth\Interfaces\RpcRequestInterface;
use Liujun\Auth\Interfaces\UserServiceInterface;

class UserService extends KabelHttpSignService implements UserServiceInterface
{
    public function __construct(RpcRequestInterface $request)
    {
        parent::__construct($request);
        $this->apiHost = config('kabel_auth.api_host.saas_api');
    }

    /**
     * 重新获取登录用户缓存
     * @param  string  $token
     * @param  string  $action
     * @return mixed
     * @throws UnauthorizedException
     */
    public function getLoginUser(string $token,string $action)
    {
        try {
            return $this->sendRequest(['token'=>$token],'/account/'.$action.'/rpc/auth/getLoginUser');
        }catch (\Exception $exception){
            throw new UnauthorizedException();
        }
    }

    /**
     * 续期更新用户TOKEN
     * @param $oldToken
     * @param $token
     * @param $action
     * @return mixed
     * @throws UnauthorizedException
     */
    public function updateToken($oldToken, $token,$action)
    {
        try {
            return $this->sendRequest(['token'=>$token,'old_token'=>$oldToken,'_method'=>'post'],'/account/'.$action.'/rpc/auth/updateToken');
        }catch (\Exception $exception){
            throw new UnauthorizedException();
        }
    }

    /**
     * 获取用户权限数组
     * @param  int  $userId
     * @param  int  $appId
     * @return mixed
     * @throws UnauthorizedException
     */
    public function getPermissions(int $userId,int $appId)
    {
        try {
            return $this->sendRequest(['user_id'=>$userId,'app_id'=>$appId],'/account/user/rpc/role/getApiPermissions');
        }catch (\Exception $exception){
            throw new UnauthorizedException();
        }
    }

    /**
     * 获取对应产品的所有权限
     * @param  int  $productId  产品ID
     * @return mixed
     * @throws UnauthorizedException
     */
    public function getProductPermissions(int $productId)
    {
        try {
            return $this->sendRequest(['product_id'=>$productId],'/account/app/rpc/app/getProductPermissions');
        }catch (\Exception $exception){
            throw new UnauthorizedException();
        }
    }
}
