<?php

namespace App\Helpers;

class AuthHelper
{
    private static $authKey = 'auth';

    private static function getUserResource(array $data)
    {
        return [
            'LANG' => \Arr::get($data, 'LANG'),
            'CUSTLIST' => \Arr::get($data, 'CUSTLIST'),
            'PRODLIST' => \Arr::get($data, 'PRODLIST'),
            'PRICELIST' => \Arr::get($data, 'PRICELIST'),
            'STATUS' => \Arr::get($data, 'STATUS'),
            'ALIAS2' => \Arr::get($data, 'ALIAS2'),
            'UID' => \Arr::get($data, 'UID'),
            'PWD' => \Arr::get($data, 'PWD')
        ];
    }

    public static function hasAuth()
    {
        return session()->has(self::$authKey);
    }

    public static function setUserProperties(array $properties)
    {
        $userResource = self::getUserResource($properties);
        session()->put(self::$authKey, $userResource);
    }

    public static function getUserProperties(string $property = '')
    {
        if ($property != '')
            return session()->get(self::$authKey . $property);
        return session()->get(self::$authKey);
    }
}