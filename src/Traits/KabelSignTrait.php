<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/12/8 23:29,
 * @LastEditTime: 2021/12/8 23:29
 */

namespace Liujun\Auth\Traits;


use Kabel\Sign\Services\SignService;
/**
 * Trait KabelSignTrait
 * @package Kabel\Rpc\RemoteServices\Traits
 * 卡百利签名
 */
trait KabelSignTrait
{
    /**
     * 获取签名参数
     * @param  array  $params  请求参数
     * @param  string  $singType
     * @return array
     */
    protected function getSignParams(array &$params, string $singType = 'authSign'): array
    {
        return SignService::setParams($params, $singType);
    }
}
