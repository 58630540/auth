<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/4/12 15:34,
 * @LastEditTime: 2021/4/12 15:34,
 * @Copyright: 2020 Kabel Inc. 保留所有权利。
 */

namespace Liujun\Auth\Traits;

use Liujun\Auth\Exceptions\UnauthorizedException;

trait UserCacheTrait
{
    /**
     * 获取token
     * @return mixed
     * @author lwz
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
        if (!$user) return false;
        return $user['id'];
    }

    /**
     * 获取用户信息
     * @return array|\Illuminate\Contracts\Auth\Authenticatable
     * @throws UnauthorizedException
     */
    protected function _getUser($isException = true)
    {
        $user = request()->attributes->get('user');
        if (!$user) {
            if ($isException) {
                throw new UnauthorizedException();
            }
        }
        return $user['user'];
    }

    /**
     * 获取企业信息
     * @param bool $isException
     * @return array
     * @throws UnauthorizedException
     * @Date: 2021/9/14 10:08
     * @Author: UnauthorizedException
     */
    protected function _getCompany($isException = true)
    {
        $user = $this->_getUser($isException);
        return $user['company'];
    }

    /**
     * 获取企业ID
     *
     * @param bool $isException
     * @return int
     * @throws UnauthorizedException
     * @Date: 2021/9/14 10:08
     * @Author: UnauthorizedException
     */
    protected function _getCompanyId($isException = true)
    {
        $user = $this->_getUser($isException);
        return $user['company']['id'];
    }

    /**
     * 获取应用ID
     * @param bool $isException
     * @return mixed
     * @throws UnauthorizedException
     */
    protected function _getAppId($isException = true)
    {
        $user = request()->attributes->get('user');
        if (!$user) {
            if ($isException) {
                throw new UnauthorizedException();
            }
        }
        return $user['app_id'];
    }

    /**
     * 获取当前登录的产品ID
     * @param bool $isException
     * @return mixed
     * @throws UnauthorizedException
     */
    protected function _getProductId($isException = true)
    {
        $user = request()->attributes->get('user');
        if (!$user) {
            if ($isException) {
                throw new UnauthorizedException();
            }
        }
        return $user['app_product_id'];
    }
}
