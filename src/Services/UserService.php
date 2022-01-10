<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
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
     * @param $token
     * @return mixed
     * @throws UnauthorizedException
     */
    public function getLoginUser($token)
    {
        try {
            return $this->sendRequest(['token'=>$token],'/account/user/rpc/auth/getLoginUser');
        }catch (\Exception $exception){
            throw new UnauthorizedException();
        }
    }

    /**
     * 续期更新用户TOKEN
     * @param $oldToken
     * @param $token
     * @return mixed
     * @throws UnauthorizedException
     */
    public function updateToken($oldToken, $token)
    {
        try {
            return $this->sendRequest(['token'=>$token,'old_token'=>$oldToken,'_method'=>'post'],'/account/user/rpc/auth/updateToken');
        }catch (\Exception $exception){
            throw new UnauthorizedException();
        }
    }
}
