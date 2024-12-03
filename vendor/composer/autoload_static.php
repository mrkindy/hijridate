<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0c35c6cc738d307886c4620ed4a482c3
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'Kindy\\HijriDate\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Kindy\\HijriDate\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0c35c6cc738d307886c4620ed4a482c3::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0c35c6cc738d307886c4620ed4a482c3::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0c35c6cc738d307886c4620ed4a482c3::$classMap;

        }, null, ClassLoader::class);
    }
}