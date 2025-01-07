<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9f8013702cc05257527179e28e7546bc
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Pub\\' => 4,
        ),
        'L' => 
        array (
            'Lib\\' => 4,
        ),
        'I' => 
        array (
            'Inc\\' => 4,
        ),
        'A' => 
        array (
            'Admin\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Pub\\' => 
        array (
            0 => __DIR__ . '/../..' . '/public',
        ),
        'Lib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
        'Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
        'Admin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/admin',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9f8013702cc05257527179e28e7546bc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9f8013702cc05257527179e28e7546bc::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
