<?php

/**
 * Part of Teinte https://github.com/oeuvres/teinte
 * MIT License https://opensource.org/licenses/mit-license.php
 * Copyright (c) 2022 frederic.Glorieux@fictif.org
 * Copyright (c) 2013 Frederic.Glorieux@fictif.org & LABEX OBVIL
 * Copyright (c) 2012 Frederic.Glorieux@fictif.org
 * Copyright (c) 2010 Frederic.Glorieux@fictif.org
 *                    & École nationale des chartes
 */

declare(strict_types=1);

namespace Oeuvres\Kit;

use Oeuvres\Teinte\Format\File;

mb_internal_encoding("UTF-8");

class I18n
{
    /** Messages */
    private static $messages = array();

    /**
     * Load default error messages send by app
     */
    public static function init(): void
    {
        self::load(__DIR__ . '/I18n_en.tsv');
    }

    /**
     * Load an array of messages
     */
    public static function load($tsv_file): void
    {
        $map = Parse::tsv_map($tsv_file);
        if ($map === null) {
            Log::warning("I18n error");
            return;
        }
        self::put($map);
    }

    /**
     * Put an array of messages, new value for same key overwrite old one
     */
    public static function put($messages): void
    {
        self::$messages = array_merge(self::$messages, $messages);
    }

    /**
     * Return a formated message
     */
    public static function _(): string
    {
        $args = func_get_args();
        if (count($args) < 1) {
            Log::warning("A key is required for a message");
            return '';
        }
        $msg = array_shift($args);
        if (isset(self::$messages[$msg])) {
            $msg = self::$messages[$msg];
        } else {
            // test if capitalized key exists
            $keyuc1 = mb_strtoupper(mb_substr($msg, 0, 1)) . mb_substr($msg, 1);
            if (isset(self::$messages[$keyuc1])) {
                $args[0] = mb_strtolower(self::$messages[$keyuc1]);
            } else {
                Log::warning("No message found for the key=\"$msg\"");
            }
        }
        // sprintf is not safe if not enough arguments
        return self::format($msg, $args);
    }

    /**
     * a python format like
     */
    public static function format(string $msg, ?array $vars): string
    {
        $vars = (array)$vars;
        // in case of sprintf
        $msg = preg_replace('#%s#', '{}', $msg);
            //numbering empty {}
        $msg = preg_replace_callback('#\{\}#', function($r){
            static $i = 0;
            return '{'.($i++).'}';
        }, $msg);
        return str_replace(
            array_map(function($k) {
                return '{'.$k.'}';
            }, array_keys($vars)),

            array_values($vars),

            $msg
        );
    }

    /**
     * Format a stack trace, returned as an array of strings 
     */
    public static function trace(): array
    {
        $trace = debug_backtrace();
        $ret = array();
        for ($i = 0, $count = count($trace); $i < $count; $i++) {
            $ret[] = $trace[$i]['file']
                . '#'
                . $trace[$i]['line']
                . ' '
                . $trace[$i]['function']
                . '('
                . implode(', ', $trace[$i]['args'])
                . ')';
        }
        return $ret;
    }


}
I18n::init();
