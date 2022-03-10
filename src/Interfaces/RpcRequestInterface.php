<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/12/02 11:06,
 * @LastEditTime: 2021/12/02 11:06
 */

namespace Liujun\Auth\Interfaces;

/**
 * Interface RpcInterface
 * @package Kabel\Interfaces
 * rpc 统一接口
 */
interface RpcRequestInterface
{
    /**
     * 设置接口名称
     * @param string|null $apiName 接口名称
     * @return RpcRequestInterface
     */
    public function setApiName(?string $apiName): RpcRequestInterface;

    /**
     * 发送请求
     * @param string $uri uri地址
     * @param array $params 请求参数
     * @param int|null $cacheTime 缓存时间(如果没传直接调用接口，如果传了先查缓存再调接口)
     * @return mixed
     * @author liujun
     */
    public function send(string $uri, array $params = [], ?int $cacheTime = null);

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
     * @author liujun
     */
    public function uploadFile(string $uri, array &$fileParams, array $rqParams = []);
}
