<?php

namespace App\Helpers;


use App\Models\Config;

if (! function_exists('wGetConfigs')) {
    function wGetConfigs($tag,$default = null)
    {
        $config = Config::where('tag',$tag)->first();

        if(empty($config)){
            if(!empty($default)){
                return $default;
            }
            return null;
        }

        return $config->value;
    }
}
