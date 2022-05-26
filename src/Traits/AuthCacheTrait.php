<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2021/4/12 15:34,
 * @LastEditTime: 2021/4/12 15:34,
 * @Copyright: 2020 Kabel Inc. 保留所有权利。
 */

namespace Liujun\Auth\Traits;

use exception;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

trait AuthCacheTrait
{
    /**
     *  设置缓存
     * @param  int  $id  type为 user、UserApiPermission、blackList 传用户ID,为 productApiPermission 传应用产品ID
     * @param  string  $type  缓存类型:
     *                  user 用户缓存
     *                  UserApiPermission 用户接口权限缓存
     *                  productApiPermission 应用产品接口权限缓存
     *                  blackList 黑名单列表
     * @param  array  $data  缓存的数据
     * @param  string  $action  用户类型 user B端用户  cUser C端用户  aUser 活动端用户
     * @return mixed
     * @throws exception
     */
    public function setAuthCache($id,string $type,array $data,string $action='user')
    {
        return $this->exec('set',$id,$type,$action,$data);
    }

    /**
     * 获取指定产品的API接口权限缓存
     * @param  int  $id type为 user、UserApiPermission、blackList 传用户ID,为 productApiPermission 传应用产品ID
     * @param  string  $type 缓存类型:
     *                      user 用户缓存
     *                      UserApiPermission 用户接口权限
     *                      productApiPermission 应用产品接口权限
     *                      blackList 黑名单列表
     * @param  string  $action 用户类型 user B端用户  cUser C端用户  aUser 活动端用户
     * @return mixed
     * @throws exception
     */
    public function getAuthCache($id,string $type,string $action='user')
    {
        return $this->exec('get',$id,$type,$action);
    }

    /**
     * 清除产品接口权限缓存
     * @param array|int $id  type为 user、UserApiPermission、blackList 传用户ID,为 productApiPermission 传应用产品ID
     * @param  string  $type 缓存类型:
     *                      user 用户缓存
     *                      UserApiPermission 用户接口权限
     *                      productApiPermission 应用产品接口权限
     *                      blackList 黑名单列表
     * @param  string  $action 用户类型 user B端用户  cUser C端用户  aUser 活动端用户
     * @return mixed
     * @throws exception
     */
    public function delAuthCache($id,string $type,string $action='user')
    {
        return $this->exec('del',$id,$type,$action);
    }

    /**
     * 执行redis 命令
     * @param  string  $cmd redis 命令
     * @param  array|int $id   type为 user、UserApiPermission、blackList 传用户ID 传用户ID,为 productApiPermission 传应用产品ID
     * @param  string  $type 缓存类型:
     *                      user 用户缓存
     *                      UserApiPermission 用户接口权限
     *                      productApiPermission 应用产品接口权限
     *                      blackList 黑名单列表
     * @param  array|null  $data $cmd 为 set 传的数据
     * @param  string  $action 用户类型 user B端用户  cUser C端用户  aUser 活动端用户
     * @return mixed
     * @throws exception
     */
    private function exec(string $cmd,$id,string $type,string $action='user',?array $data =[])
    {
        $redis = Redis::connection($action.'Auth');
        $cacheConfig = $this->getCacheConfig($type,$action);
        $key = null;
        if(!is_array($id)){
            $key = sprintf($cacheConfig['key'], $id);
        }else{
            foreach ($id as $v) {
                $key[] = sprintf($cacheConfig['key'], $v);
            }
        }
        $expire = $cacheConfig['expire'];
        switch ($cmd){
            case 'get':
                $res = $redis->get($key);
                $res && $res = json_decode($res,true);
                break;
            case 'set':
                $res = $redis->set($key,json_encode($data),'EX',$expire);
                break;
            case 'del':
                $res = $redis->del($key);
                break;
            default:
                throw new exception('命令参数类型错误！');
        }
        return $res;
    }

    /**
     * 获取缓存key
     * @param  string  $type 缓存类型:
     *                      user 用户缓存
     *                      UserApiPermission 用户接口权限
     *                      productApiPermission 应用产品接口权限
     *                      blackList 黑名单列表
     * @param  string  $action  用户类型 user B端用户  cUser C端用户  aUser 活动端用户
     * @return array
     * @throws exception
     */
    private function getCacheConfig(string $type,string $action='user'): array
    {
        $typeMapping = [
            'productApiPermission'=>'product_all_permissions_cache_key',
            'userApiPermission'=>'user_permissions_cache_key',
            'user'=>'cache_key',
            'blackList'=>'black_cache_key'
        ];
        if(!isset($typeMapping[$type]))
        {
            throw new exception('缓存类型参数类型错误！');
        }
        return config('kabel_auth.'.$action.'.'.$typeMapping[$type]);
    }
}
