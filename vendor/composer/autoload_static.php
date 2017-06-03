<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf2b8e4b411b09723eae9d69084454e72
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Informagenie\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Informagenie\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf2b8e4b411b09723eae9d69084454e72::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf2b8e4b411b09723eae9d69084454e72::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
