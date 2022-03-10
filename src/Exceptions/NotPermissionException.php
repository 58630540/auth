<?php
/**
 * @Author: liujun <58630540@qq.com>,
 * @Date: 2022/01/10 10:31,
 * @LastEditTime: 2022/01/10 10:31
 */

namespace Liujun\Auth\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class AuthException
 * @package Kabel\Exceptions
 * @author liujun
 * 登录异常（如：登录失败、token错误）
 */
class NotPermissionException extends \Exception
{
    public bool $showOriMsg = true; // 是否显示原始异常信息

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->code = config('kabel_auth.exception_code.no_permission')[0];
        $this->message = config('kabel_auth.exception_code.no_permission')[1];
    }
}
