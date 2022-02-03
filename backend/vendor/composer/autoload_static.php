<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite233912702920958b02f2630caecf419
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Maisenvios\\Middleware\\' => 22,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Maisenvios\\Middleware\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-curl-class/php-curl-class/src/Curl',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite233912702920958b02f2630caecf419::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite233912702920958b02f2630caecf419::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite233912702920958b02f2630caecf419::$classMap;

        }, null, ClassLoader::class);
    }
}
