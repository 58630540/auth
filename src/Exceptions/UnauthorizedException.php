<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2021/11/27 10:31,
 * @LastEditTime: 2021/11/27 10:31
 */

namespace Liujun\Auth\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class AuthException
 * @package Kabel\Exceptions
 * @author lwz
 * 登录异常（如：登录失败、token错误）
 */
class UnauthorizedException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->code = config('kabel_auth.exception_code.no_login')[0];
        $this->message = config('kabel_auth.exception_code.no_login')[1];
    }
}
