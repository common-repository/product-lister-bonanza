<?php
/*
 * This file is part of Bonapitit.
 *
 * (c) 2016 Jason Clark
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Autoloads Bonapitit classes.
 *
 * @author Jason Clark <mithereal@gmail.com>
 */
class Bonanza_Autoloader
{
    /**
     * Registers Bonapitit_Autoloader as an SPL autoloader.
     *
     * @param bool    $prepend Whether to prepend the autoloader or not.
     */
    public static function register($prepend = false)
    {
        
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array(__CLASS__, 'autoload'));
        }
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     */
    public static function autoload($class)
    {

        if (is_file($file = dirname(__FILE__).'/../'.str_replace(array('_', "\0"), array('/', ''), $class).'.php')) {
            require $file;
        }
    }
}