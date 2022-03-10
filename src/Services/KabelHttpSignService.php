<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/12/6 21:49,
 * @LastEditTime: 2021/12/6 21:49
 */

namespace Liujun\Auth\Services;

use Illuminate\Support\Facades\Http;
use Liujun\Auth\Interfaces\RpcRequestInterface;
use Liujun\Auth\Traits\KabelSignTrait;

/**
 * Class KabelHttpService
 * @package Kabel
 * @author liujun
 * kabel 签名请求接口
 */
class KabelHttpSignService
{
    use KabelSignTrait;

    /**
     * 接口请求地址
     * @var string|null
     */
    protected ?string $apiHost = null;

    /**
     * RPC请求类
     * @var RpcRequestInterface
     */
    protected RpcRequestInterface $request;

    public function __construct(RpcRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * 发送请求
     * @param array $params 请求参数
     * @param string $apiUri 接口uri
     * @param string|null $apiName 接口名称
     * @param int|null $cacheTime 缓存时间
     * @throws Exceptions\ValidateException
     * @author liujun
     */
    protected function sendRequest(array $params, string $apiUri, ?string $apiName = null, ?int $cacheTime = null)
    {
        $this->setParamsApiHost($params); // 设置host地址
        return $this->request->setApiName($apiName)
            ->send($apiUri, $this->getSignParams($params), $cacheTime);
    }

    /**
     * 设置参数接口域名
     * @param array $params 请求参数
     */
    protected function setParamsApiHost(array &$params)
    {
        $host = $this->apiHost ?: config('kabel_auth.api_host.saas_api');
        $params['_host'] = $host;
    }
}
