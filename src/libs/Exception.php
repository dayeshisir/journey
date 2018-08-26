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
     * DB 操作异常
     */
    const ERR_DB_ERROR    = 20002;

    protected $aErrorMap = [
        self::ERR_PARAM_ERROR => '参数错误',
        self::ERR_DB_ERROR    => 'DB 操作异常',
    ];

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = isset($this->aErrorMap[$code]) ? $this->aErrorMap[$code] : $message;

        parent::__construct($message, $code, $previous);
    }
}