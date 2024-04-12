<?php

namespace aogg\phpunit\think;

/**
 * 复制think-helper项目部分方法
 */
class ThinkHelperClass
{

    /**
     *
     *获取一个类里所有用到的trait，包括父类的
     *
     * @param mixed $class 类名
     * @return array
     */
    public static function class_uses_recursive($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];
        $classes = array_merge([$class => $class], class_parents($class));
        foreach ($classes as $class) {
            $results += static::trait_uses_recursive($class);
        }

        return array_unique($results);
    }

    /**
     * 获取一个trait里所有引用到的trait
     *
     * @param string $trait Trait
     * @return array
     */
    public static function trait_uses_recursive(string $trait): array
    {
        $traits = class_uses($trait);
        foreach ($traits as $trait) {
            $traits += static::trait_uses_recursive($trait);
        }

        return $traits;
    }
}