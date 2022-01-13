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
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws UnauthorizedException
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->headers->get('authorization', '');
        if (!$token) {
            throw new UnauthorizedException();
        }
        $user = $this->checkToken($token);
        $request->attributes->add(['user' => $user, 'token' => $user['token']]);
        $response = $next($request);
        if (!$response->headers->get('Authorization')) {//没有设值就可刷新
            $response->headers->set('Authorization', $user['token']);
        }
        return $response;
    }

    /**
     * 校验token
     *
     * @param string $token
     * @return mixed
     * @throws
     */
    public function checkToken(string $token)
    {
        try {
            $key = config('kabel_auth.jwt.key');
            $allowed = config('kabel_auth.jwt.allowed');
            JWT::$leeway = 5;//当前时间减去5，把时间留点余地
            $jwt = JWT::decode($token, $key, $allowed);
            $userId = $jwt['data']->userId ?? '';
            $cache = Redis::connection('authCache');
            $cacheConf = config('kabel_auth.cache_key.user');
            $cacheKey = sprintf($cacheConf['key'], $userId);
            $userInfo = $cache->get($cacheKey);
            if (empty($userInfo)) {//缓存不存在，重新查询用户信息接口
                $userInfo = app(UserServiceInterface::class)->getLoginUser($token);
            } else {
                $userInfo = json_decode($userInfo, true);
                if ($jwt['token']) {//如果有token，说明续期了,要重新响应给前端
                    $userInfo['token'] = $jwt['token'];
                    app(UserServiceInterface::class)->updateToken($token, $jwt['token']);
                    $cache->set($cacheKey, json_encode($userInfo), $cacheConf['expire']);//重新更新有效期
                }
            }
            return $userInfo;
        } catch (\Exception $e) {//其他错误
            throw new UnauthorizedException();
        }
    }
}
