<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/4/12 15:34,
 * @LastEditTime: 2021/4/12 15:34,
 * @Copyright: 2020 Kabel Inc. 保留所有权利。
 */

namespace Liujun\Auth\Traits;

use Liujun\Auth\Exceptions\UnauthorizedException;
use Liujun\Auth\Middleware\CheckUserToken;

trait AUserCacheTrait
{
    /**
     * 获取token
     * @return mixed
     * @author liujun
     */
    protected function _getToken()
    {
        return request()->attributes->get('token');
    }

    /**
     * 获取用户id
     * @return int|string
     * @throws UnauthorizedException
     */
    protected function _getUserId($isException = true)
    {
        $user = $this->_getUser($isException);
        return $user['id']??0;
    }

    /**
     * 获取用户id
     * @return int|string
     * @throws UnauthorizedException
     */
    protected function _getUuId($isException = true)
    {
        $user = $this->_getUser($isException);
        return $user['uuid']??0;
    }

    /**
     * 获取用户信息
     * @return array
     * @throws UnauthorizedException
     */
    protected function _getUser($isException = true)
    {
        $user = request()->attributes->get('user');
        if (!$user) {
            if ($isException) {
                throw new UnauthorizedException();
            }else{
                $Authorization = request()->header('Authorization');
                if(!$Authorization){
                    return  [];
                }
                $check = new CheckUserToken();
                try {
                    $user = $check->checkToken($Authorization,'aUser');
                }catch (\Exception $exception){
                }
                return $user??[];
            }
        }
        return $user;
    }
}
