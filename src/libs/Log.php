<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午4:14
 */

namespace apps\libs;


use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Log
{
    const NOTICE_LOG  = 'journey.notice';
    const WARNING_LOG = 'journey.wf';
    const ERROR_LOG   = 'journey.error';

    /**
     * @var \Monolog\Logger
     */
    protected static $oWarning = null;

    /**
     * @var \Monolog\Logger
     */
    protected static $oError   = null;

    /**
     * @var \Monolog\Logger
     */
    protected static $oNotice  = null;

    protected static $aEnabledLog = array(
        'notice'  => 1,
        'warning' => 1,
        'error'   => 1,
    );

    public static function init()
    {
        foreach (self::$aEnabledLog as $log => $enable) {
            if (!$enable) {
                continue;
            }

            switch ($log) {
                case 'warning' :
                    self::bEnableWarningLog();
                    break;
                case 'notice' :
                    self::bEnableNoticeLog();
                    break;
                case 'error' :
                    self::bEnableErrorLog();
                    break;
                default :
                    break;
            }
        }
    }

    /**
     * 添加warning日志
     *
     * @param $message
     * @param array $aContent
     */
    public static function vWarning($message, $aContent = [])
    {
        if (null === self::$oWarning) {

            return;
        }

        self::$oWarning->addWarning($message, $aContent);
    }

    /**
     * 添加error日志
     *
     * @param $message
     * @param array $aContent
     */
    public static function vError($message, $aContent = [])
    {
        if (null === self::$oError) {

            return;
        }

        self::$oError->addError($message, $aContent);
    }

    /**
     * 添加notice日志
     *
     * @param $message
     * @param array $aContent
     */
    public static function vNotice($message, $aContent)
    {
        if (null === self::$oNotice) {

            return;
        }

        self::$oNotice->addNotice($message, $aContent);
    }

    /**
     * 开启异常日志
     *
     * @throws \Exception
     */
    protected static function bEnableWarningLog()
    {
        $oLog = new Logger("warning");
        $oLog->pushHandler(new StreamHandler(self::sGetWarningFile(), Logger::WARNING));

        self::$oWarning = $oLog;
    }

    /**
     * 开启错误日志
     *
     * @throws \Exception
     */
    protected static function bEnableErrorLog()
    {
        $oLog = new Logger("error");
        $oLog->pushHandler(new StreamHandler(self::sGetErrorFile(), Logger::ERROR));

        self::$oError = $oLog;
    }

    /**
     * 开启notice日志
     *
     * @throws \Exception
     */
    protected static function bEnableNoticeLog()
    {
        $oLog = new Logger("notice");
        $oLog->pushHandler(new StreamHandler(self::sGetNoticeFile(), Logger::NOTICE));

        self::$oNotice = $oLog;
    }

    /**
     * 返回warn日志文件路径名
     *
     * @return string
     */
    protected static function sGetWarningFile()
    {
        $sFileName = sprintf("%s%s", \apps\common\Constant::FILE_LOG_DIR, self::WARNING_LOG);

        return $sFileName;
    }

    /**
     * 返回error日志文件路径名
     *
     * @return string
     */
    protected static function sGetErrorFile()
    {
        $sFileName = sprintf("%s%s", \apps\common\Constant::FILE_LOG_DIR, self::ERROR_LOG);

        return $sFileName;
    }

    /**
     * 返回notice日志文件路径名
     *
     * @return string
     */
    protected static function sGetNoticeFile()
    {
        $sFileName = sprintf("%s%s", \apps\common\Constant::FILE_LOG_DIR, self::NOTICE_LOG);

        return $sFileName;
    }
}