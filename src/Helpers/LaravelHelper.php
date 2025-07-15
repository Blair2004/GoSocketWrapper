<?php

namespace GoSocket\Wrapper\Helpers;

class LaravelHelper
{
    /**
     * Get class basename
     *
     * @param string $class
     * @return string
     */
    public static function classBasename(string | object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Get all traits used by a class recursively
     *
     * @param string $class
     * @return array
     */
    public static function classUsesRecursive(string $class): array
    {
        if (function_exists('class_uses_recursive')) {
            return class_uses_recursive($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive(class_uses($class));
        }

        return array_unique($results);
    }
}

/**
 * Get all traits used by a trait recursively
 *
 * @param array $traits
 * @return array
 */
function trait_uses_recursive(array $traits): array
{
    $uses = [];

    foreach ($traits as $trait) {
        $uses += class_uses($trait);
        $uses += trait_uses_recursive(class_uses($trait));
    }

    return $uses;
}
