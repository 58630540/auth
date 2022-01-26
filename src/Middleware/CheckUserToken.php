<?php

namespace Liujun\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Liujun\Auth\Exceptions\UnauthorizedException;
use Liujun\Auth\Interfaces\UserServiceInterface;
use Liujun\Auth\Library\JWT;

class CheckUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  $action  来源端
     * @return mixed
     * @throws UnauthorizedException
     */
    public function handle(Request $request, Closure $next,string $action='user')
    {
        $token = $request->headers->get('authorization', '');
        if (!$token) {
            throw new UnauthorizedException();
        }
        $user = $this->checkToken($token,$action);
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
            JWT::$leeway = 5;//当前时间减去5，把时间留点余地
            $jwt = JWT::decode($token,$config['jwt']);
            $userId = $jwt['data']->userId ?? '';
            $cache = Redis::connection($action.'Auth');
            $cacheConf = $config['cache_key'];;
            $cacheKey = sprintf($cacheConf['key'], $userId);
            $userCache = $cache->get($cacheKey);
            if (empty($userCache)) {//缓存不存在，重新查询用户信息接口
                $userInfo = app(UserServiceInterface::class)->getLoginUser($token,$action);
            } else {
                $userInfo['user'] = json_decode($userCache, true);
                $userInfo['token'] = $token;
                $userInfo['app_id'] = $jwt['data']->appId ?? '';
                $userInfo['app_product_id'] = $jwt['data']->appProductId ?? '';
                if ($jwt['token']) {//如果有token，说明续期了,要重新响应给前端
                    $userInfo['token'] = $jwt['token'];//更新token
                    app(UserServiceInterface::class)->updateToken($token, $jwt['token'],$action);
                }
            }
            return $userInfo;
        } catch (\Exception $e) {//其他错误
            throw new UnauthorizedException($e->getMessage(),$e->getCode());
        }
    }
}
