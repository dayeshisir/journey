<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午3:46
 */

namespace apps\libs;

use Throwable;

class Exception extends \Exception
{
    /**
     * 参数错误
     */
    const ERR_PARAM_ERROR = 20001;

    /**
     *
     */
    const ERR_UID_ERROR  = 20002;

    /**
     * DB 操作异常
     */
    const ERR_DB_ERROR    = 30003;

    protected $aInternalErrorMap = [
        self::ERR_PARAM_ERROR => '参数错误',
        self::ERR_UID_ERROR   => '用户没登录',
        self::ERR_DB_ERROR    => 'DB 操作异常',
    ];

    protected $aExportErrorMap = [
        self::ERR_PARAM_ERROR => '您的输入有误，请查看后再次输入',
        self::ERR_UID_ERROR   => '您还没有登录，请登录后重试',
        self::ERR_DB_ERROR    => '系统开小差，请稍后重试',
    ];

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = isset($this->aInternalErrorMap[$code]) ? $this->aInternalErrorMap[$code] : $message;

        parent::__construct($message, $code, $previous);
    }

    public function sGetSysErrmsg($code = 0)
    {
        return  isset($this->aInternalErrorMap[$code]) ? $this->aInternalErrorMap[$code] : '';
    }

    public function sGetUserErrmsg($code = 0)
    {
        return isset($this->aExportErrorMap[$code]) ? $this->aExportErrorMap[$code] : '' ;
    }
}