<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/12/02 11:48,
 * @LastEditTime: 2021/12/02 11:48
 */
namespace Liujun\Auth\Services;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Liujun\Auth\Exceptions\RpcException;
use Liujun\Auth\Interfaces\RpcRequestInterface;

class HttpRequest implements RpcRequestInterface
{
    /**
     * 响应的编号
     * @var int
     */
    protected int $rspSuccessCode = 0;

    /**
     * 响应的编号的字段
     * @var string
     */
    protected string $rspCodeField = 'code';

    /**
     * 响应的消息字段
     * @var string
     */
    protected string $rspMsgField = 'msg';

    /**
     * 响应的数据字段
     * @var string
     */
    protected string $rspDataField = 'data';

    /**
     * 接口名称
     * @var string|null
     */
    protected ?string $apiName = null;

    /**
     * 是否获取原始数据
     * @var bool
     */
    protected bool $getOriData = false;

    /**
     * 缓存key前缀
     * @var string
     */
    protected string $cachePrefix = 'rpc:';

    /**
     * 请求超时时间
     * @var int
     */
    protected int $timeout = 5;

    /**
     * 请求主机地址
     * @var string|null
     */
    protected ?string $host;

    public function __construct()
    {
        // 设置主机地址
        $this->host = config('kabel_auth.api_host.saas_api');
    }

    /**
     * 设置响应的编号
     * @param int $code 请求编号
     * @return $this
     * @author liujun
     */
    public function setRspSuccessCode(int $code): HttpRequest
    {
        $this->rspSuccessCode = $code;
        return $this;
    }

    /**
     * 设置响应编号字段
     * @param string $field 编号字段
     * @return HttpRequest
     * @author liujun
     */
    public function setRspCodeField(string $field): HttpRequest
    {
        $this->rspCodeField = $field;
        return $this;
    }

    /**
     * 设置响应的数据字段
     * @param string $field 数据字段名
     * @return $this
     * @author liujun
     */
    public function setRspDataField(string $field): HttpRequest
    {
        $this->rspDataField = $field;
        return $this;
    }

    /**
     * 设置响应的消息字段
     * @param string $field 消息字段名
     * @return $this
     * @author liujun
     */
    public function setRspMsgField(string $field): HttpRequest
    {
        $this->rspMsgField = $field;
        return $this;
    }

    /**
     * 设置缓存key前缀
     * @param string $prefix 前缀
     * @return HttpRequest
     * @author liujun
     */
    public function setCachePrefix(string $prefix): HttpRequest
    {
        $this->cachePrefix = $prefix;
        return $this;
    }

    /**
     * 设置是否返回原始数据
     * @param bool $bool
     * @return HttpRequest
     * @author liujun
     */
    public function setOriData(bool $bool): HttpRequest
    {
        $this->getOriData = $bool;
        return $this;
    }

    /**
     * 设置接口名称
     * @param string|null $apiName 接口名称
     * @return $this
     */
    public function setApiName(?string $apiName): HttpRequest
    {
        $this->apiName = $apiName;
        return $this;
    }

    /**
     * 发送请求
     * @param string $uri uri地址
     * @param array $params 请求参数
     *  _host: 请求域名
     *  _method：请求方式，get、post、put...
     *  _token：token参数
     * @param int|null $cacheTime 缓存时间(如果没传直接调用接口，如果传了先查缓存再调接口)
     * @param ?array $fileParams 文件请求参数
     *    必填项：
     *      name：接受文件的名字（相当于 HTML Input 的 name 属性）
     *      contents：文件内容
     *    选填项：
     *      filename: 文件名
     *      headers：请求头数组
     * @return mixed
     * @throws RpcException|\Psr\SimpleCache\InvalidArgumentException
     * @author liujun
     */
    public function send(string $uri, array $params = [], ?int $cacheTime = null)
    {
        // 文件上传不缓存数据
        if ($cacheTime && empty($fileParams)) { // 设置了缓存时间的情况
            // 先从缓存中获取
            $cacheKey = $this->getCacheKey($uri, $params);
            $data = Cache::get($cacheKey);
            if (is_null($data)) {
                $data = $this->handleRequest($uri, $params);
                Cache::set($cacheKey, $data, $cacheTime);
            }
        } else {
            $data = $this->handleRequest($uri, $params, $fileParams);
        }

        return $this->getOriData ? $data : $data[$this->rspDataField];
    }

    /**
     * 文件上传
     * @param string $uri uri地址
     * @param array $fileParams 文件上传的相关参数
     *    必填项：
     *      name：接受文件的名字（相当于 HTML Input 的 name 属性）
     *      contents：文件内容
     *    选填项：
     *      filename: 文件名
     *      headers：请求头数组
     * @param array $rqParams 表单请求参数
     * @return mixed
     * @throws RpcException
     * @author liujun
     */
    public function uploadFile(string $uri, array &$fileParams, array $rqParams = [])
    {
        $data = $this->handleRequest($uri, $rqParams, $fileParams);
        return $this->getOriData ? $data : $data[$this->rspDataField];
    }

    /**
     * 处理请求
     * @param string $uri uri地址
     * @param array $params 请求参数
     *  _host: 请求域名
     *  _method：请求方式，get、post、put...
     *  _token：token参数
     * @param ?array $fileParams 文件请求参数
     *    必填项：
     *      name：接受文件的名字（相当于 HTML Input 的 name 属性）
     *      contents：文件内容
     *    选填项：
     *      filename: 文件名
     *      headers：请求头数组
     * @throws RpcException
     * @author liujun
     */
    protected function handleRequest(string $uri, array &$params, ?array &$fileParams = null)
    {
        $host = $params['_host'] ?? null;
        $token = $params['_token'] ?? null; // 获取token
        $method = $params['_method'] ?? 'get';

        $http = Http::timeout($this->timeout);
        $token && $http->withToken($token); // 设置token

        // 记录开始时间
        $startTime = microtime(true); // 开始时间

        try {
            /**
             * @var $http Response
             */
            $url = $this->getUrl($uri, $host);
            if ($fileParams) {
                $http = $http->withHeaders($fileParams['headers'] ?? [])->attach(
                    $fileParams['name'],
                    $fileParams['contents'],
                    $fileParams['filename'] ?? null
                )->post($url, $params);
            } else {
                $http = $http->$method($url, $params);
            }
        } catch (HttpClientException $e) {
            throw new RpcException($this->getErrMsg($uri, $e->getMessage()));
        }


        if (!$http->ok()) {
            throw new RpcException($this->getErrMsg($uri, 'status code' . $http->status()));
        }

        $response = $http->json();

        // 检查远程接口响应是否正确
        if ($response[$this->rspCodeField] != $this->rspSuccessCode) {
            throw new RpcException($this->getErrMsg($uri, $response[$this->rspMsgField] ?? ''));
        }

        // 记录请求慢的接口
        $endTime = microtime(true) - $startTime;
        if ($endTime > 1) {
            Log::info($uri . '接口请求慢' . $endTime);
        }

        return $response;
    }

    /**
     * 获取请求url
     * @param string|null $uri uri地址
     * @param string|null $host 请求域名
     * @return string
     * @author liujun
     */
    protected function getUrl(?string $uri, ?string $host = null): string
    {
        return ($host ?? (config('kabel_auth.api_host.saas_api'))) . '/' . ltrim($uri, '/');
    }

    /**
     * 获取错误信息
     * @param string $uri 请求uri
     * @param string $errMsg 错误信息
     * @return string
     * @author liujun
     */
    protected function getErrMsg(string $uri, string $errMsg): string
    {
        return '[http error] ' . ($this->apiName ?: $uri) . $errMsg;
    }

    /**
     * 获取缓存key
     * @param string $uri uri地址
     * @param array $params 请求参数
     * @return string
     * @author liujun
     */
    protected function getCacheKey(string $uri, array &$params): string
    {
        return $this->cachePrefix . md5($uri . json_encode($params));
    }
}
