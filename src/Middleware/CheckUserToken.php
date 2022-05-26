<?php

namespace Liujun\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Liujun\Auth\Exceptions\NotPermissionException;
use Liujun\Auth\Exceptions\UnauthorizedException;
use Liujun\Auth\Interfaces\UserServiceInterface;
use Liujun\Auth\Library\JWT;
use Liujun\Auth\Traits\AuthCacheTrait;

class CheckUserToken
{
    use AuthCacheTrait;
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  $action  来源端
     * @return mixed
     * @throws UnauthorizedException|NotPermissionException
     */
    public function handle(Request $request, Closure $next,string $action='user')
    {
        $token = $request->headers->get('authorization', '');
        if (!$token) {
            throw new UnauthorizedException('',0,$action);
        }
        $token = str_replace('Bearer ', '', $token);
        $user = $this->checkToken($token,$action);
        if($action == 'user')
        {//B端用户才验证权限
            $this->checkApiPermissions($user);
        }
        $request->attributes->add(['user' => $user, 'token' => $user['token']]);
        $response = $next($request);
        if ($token != $user['token']) {//不等于之前的就刷新token
            $response->headers->set('Authorization', $user['token']);
        }
        return $response;
    }

    /**
     * 校验token
     * @param  string  $token
     * @param  string  $action 来源端
     * @return mixed
     * @throws UnauthorizedException
     */
    public function checkToken(string $token,string $action)
    {
        try {
            $config = config('kabel_auth.'.$action);
            $jwt = JWT::decode($token,$config['jwt']);
            $jwtData = $jwt['data'];
            $userId = $jwtData->userId ?? '';
            $this->checkBlack($userId,$jwtData,$action);//检查黑名单
            $userCache = $this->getAuthCache($userId,'user',$action);
            if (empty($userCache)) {//缓存不存在，重新查询用户信息接口
                $user = app(UserServiceInterface::class)->getLoginUser($token,$action);
                $user['company_id'] = $jwtData->companyId ?? '';
            } else {
                $user['user'] = $userCache;
                $user['token'] = $token;
                $user['company_id'] = $jwtData->companyId ?? '';
                $user['app_id'] = $jwtData->appId ?? '';
                $user['app_product_id'] = $jwtData->appProductId ?? '';
                if ($jwt['token']) {//如果有token，说明续期了,要重新响应给前端
                    $user['token'] = $jwt['token'];//更新token
                    app(UserServiceInterface::class)->updateToken($token, $jwt['token'],$action);
                }
            }
            return $user;
        } catch (\Exception $e) {//其他错误
            throw new UnauthorizedException($e->getMessage(),$e->getCode(),$action);
        }
    }

    /**
     * 验证接口请求权限
     * @param $user
     * @return void
     * @throws NotPermissionException
     */
    private function checkApiPermissions($user): void
    {
        $config = config('kabel_auth.user');
        if(!isset($config['is_check_api_permissions']) || !$config['is_check_api_permissions'])
        {//没开启，或者没有设置就不判断接口权限
            return;
        }
        $userId = $user['user']['id'];
        $appId = $user['app_id'];
        $productId = $user['app_product_id'];

        //获取应用产品所有需要判断的接口权限数组
        $productPermissions = $this->getAuthCache($productId,'productApiPermission');
        if ($productPermissions === null)
        {//缓存不存在，重新查询用户信息接口
            $productPermissions = app(UserServiceInterface::class)->getProductPermissions($productId);
        }
        if(!$productPermissions)
        {//没有配置接口权限就不验证
            return;
        }
        //获取当前用户API接口权限数组
        $userPermissions = $this->getAuthCache($userId,'userApiPermission');
        if ($userPermissions === null) {//缓存不存在，重新查询用户信息接口
            $userPermissions = app(UserServiceInterface::class)->getPermissions($userId,$appId);
        }
        $path = '/'.\Illuminate\Support\Facades\Request::path();//获取请求路由路径
        if(in_array($path,$productPermissions) && (!$userPermissions || !in_array($path,$userPermissions)))
        {//当前地址有在当前产品权限列表里，并且当前用户没有权限列表或不在权限列表里表示没有权限
            throw new NotPermissionException();
        }
    }

    /**
     * 黑名单检测
     * @param $id
     * @param $jwtPayload
     * @param  string  $action
     * @throws \exception
     */
    private function checkBlack($id,$jwtPayload,$action='user')
    {
        if($action !='user'){
            return;
        }
        $info = $this->getAuthCache($id,'blackList',$action);//查询此用户黑名单缓存是否存在
        if($info && $jwtPayload->iat <= $info['iat'] )
        {//黑名单存在，并且当前token创建时间是小于加入黑名单时间，则直接抛未登录异常
            throw new UnauthorizedException('',0,$action);
        }
    }
}
