<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 24/02/2016
 * Time: 19:08
 */
class Setting extends Model
{
    private static $cache = null;
    public $settingid;
    public $key;
    public $value;

    static function getKey($key) {
        if (self::$cache === null) {
            self::$cache = Model::pluck(Setting::find('1=1'), 'value', 'key');
        }
        return self::$cache[$key];
    }

    static function setKey($key, $value) {
        self::$cache[$key] = $value;

        $setting = Setting::findOne('`key` = ?', [$key]);
        if (!$setting) {
            $setting = Setting::create();
            $setting->key = $key;
        }
        $setting->value = $value;
        $setting->save();
    }
}
Setting::init('settings', 'settingid');