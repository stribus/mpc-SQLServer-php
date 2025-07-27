<?php

namespace MCP\SqlServer\Helpers;

class ClassAutoLoader
{
    /**
     * Autoload classes that implement a specific interface from a given path.
     *
     * @param string $path      the directory path to search for classes
     * @param string $interface the interface that the classes must implement
     *
     * @return array an array of instantiated objects that implement the specified interface
     */
    public static function autoloadClasses(string $path, string $interface): array
    {
        $objects = [];

        foreach (glob("{$path}/*.php") as $file) {
            // Capture classes declared antes do require
            $beforeClasses = get_declared_classes();

            require_once $file;
            // Capture classes declaradas após o require
            $afterClasses = get_declared_classes();
            // Identifica apenas as novas classes carregadas
            $newClasses = array_diff($afterClasses, $beforeClasses);

            foreach ($newClasses as $class) {
                $reflection = new \ReflectionClass($class);
                if ($reflection->implementsInterface($interface) && !$reflection->isAbstract()) {
                    $objects[] = $reflection->newInstanceArgs();
                }
            }
        }

        return $objects;
    }
}
