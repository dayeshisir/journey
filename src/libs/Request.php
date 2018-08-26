<?php
namespace apps\libs;

/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/22
 * Time: 上午12:51
 */
class Request
{
    protected static $_oFactory = null;
    protected static $_oRequest = null;
    protected static $_oCookie  = null;

    public static function init()
    {
        $oFactory = new \Aura\Web\WebFactory($GLOBALS);
        self::$_oRequest = $oFactory->newRequest();
        self::$_oCookie  = $oFactory->newRequestCookies();
    }

    /**
     * 获取参数
     *
     * @param $name
     * @param string $default
     * @return mixed
     */
    public static function mGetParam($name, $default = '')
    {
        if (null === self::$_oRequest) {
            if (null === self::$_oFactory) {
                self::$_oFactory = new \Aura\Web\WebFactory($GLOBALS);
            }

            self::$_oRequest = self::$_oFactory->newRequest();
        }

        $mValue = self::$_oRequest->query->get($name, $default);

        if (!empty($mValue)) {
            return $mValue;
        }

        return self::$_oRequest->post->get($name, $default);
    }

    /**
     * @param $name
     * @param string $default
     * @return mixed
     */
    public static function mGetCookie($name, $default = '')
    {
        if (null === self::$_oRequest) {
            if (null === self::$_oFactory) {
                self::$_oFactory = new \Aura\Web\WebFactory($GLOBALS);
            }

            self::$_oCookie = self::$_oFactory->newRequestCookies();
        }

        $mValue = self::$_oCookie->get($name, $default);

        return $mValue;
    }
}