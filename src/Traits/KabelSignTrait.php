<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2021/12/8 23:29,
 * @LastEditTime: 2021/12/8 23:29
 */

namespace Liujun\Auth\Traits;

/**
 * Trait KabelSignTrait
 * @package Kabel\Rpc\RemoteServices\Traits
 * 卡百利签名
 */
trait KabelSignTrait
{
    /**
     * 默认的签名类型
     * @var string
     */
    protected string $defaultSignType = 'kabel';

    /**
     * 获取签名参数
     * @param array $params 请求参数
     * @param string|null $signType 签名类型
     * @return array
     * @throws ValidateException
     */
    protected function getSignParams(array &$params, string $signType = null): array
    {
        $signType = $signType ?? $this->defaultSignType;
        $signConfig = config('kabel_auth.sign');
        if (!$signConfig) {
            throw new \Exception('请配置签名信息' . $signType);
        }

        $params['t'] = time();
        $params['appkey'] = $signConfig['app_key'];
        $params['sign'] = $this->genSign($params, $signConfig['secret']);
        return $params;
    }

    /**
     * @description API规范生成签名的方法
     * @param array $params 加密参数
     * @param string $appSecret 密钥
     * @param int $rfc urlencoded 标准
     * @return string
     */
    protected function genSign(array $params = [], string $appSecret = '', int $rfc = 3986): string
    {
        unset($params['sign']);
        $sign = $this->loopArraySign($params, $rfc);
        $sign .= $appSecret;
        return strtoupper(md5($sign));
    }

    protected function loopArraySign(array $params, int $rfc): string
    {
        $sign = '';
        ksort($params);
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                $sign .= $k;
                $sign .= $this->loopArraySign($v, $rfc);
            } else {
                $v = $rfc == 3986 ? rawurlencode($v) : urlencode($v);
                $sign .= $k . $v;
            }
        }
        return $sign;
    }
}
