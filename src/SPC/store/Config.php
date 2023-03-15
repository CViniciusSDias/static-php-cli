<?php

declare(strict_types=1);

namespace SPC\store;

use SPC\exception\FileSystemException;
use SPC\exception\RuntimeException;

/**
 * 一个读取 config 配置的操作类
 */
class Config
{
    public static ?array $source = null;

    public static ?array $lib = null;

    public static ?array $ext = null;

    /**
     * 从配置文件读取一个资源(source)的元信息
     *
     * @throws FileSystemException
     */
    public static function getSource(string $name): ?array
    {
        if (self::$source === null) {
            self::$source = FileSystem::loadConfigArray('source');
        }
        return self::$source[$name] ?? null;
    }

    /**
     * 根据不同的操作系统分别选择不同的 lib 库依赖项
     * 如果 key 为 null，那么直接返回整个 meta。
     * 如果 key 不为 null，则可以使用的 key 有 static-libs、headers、lib-depends、lib-suggests。
     * 对于 macOS 平台，支持 frameworks。
     *
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public static function getLib(string $name, ?string $key = null, mixed $default = null)
    {
        if (self::$lib === null) {
            self::$lib = FileSystem::loadConfigArray('lib');
        }
        if (!isset(self::$lib[$name])) {
            throw new RuntimeException('lib [' . $name . '] is not supported yet for get');
        }
        $supported_sys_based = ['static-libs', 'headers', 'lib-depends', 'lib-suggests', 'frameworks'];
        if ($key !== null && in_array($key, $supported_sys_based)) {
            $m_key = match (PHP_OS_FAMILY) {
                'Windows' => ['-windows', '-win', ''],
                'Darwin' => ['-macos', '-unix', ''],
                'Linux' => ['-linux', '-unix', ''],
                default => throw new RuntimeException('OS ' . PHP_OS_FAMILY . ' is not supported'),
            };
            foreach ($m_key as $v) {
                if (isset(self::$lib[$name][$key . $v])) {
                    return self::$lib[$name][$key . $v];
                }
            }
            return $default;
        }
        if ($key !== null) {
            return self::$lib[$name][$key] ?? $default;
        }
        return self::$lib[$name];
    }

    /**
     * @throws FileSystemException
     */
    public static function getLibs(): array
    {
        if (self::$lib === null) {
            self::$lib = FileSystem::loadConfigArray('lib');
        }
        return self::$lib;
    }

    /**
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public static function getExt(string $name, ?string $key = null, mixed $default = null)
    {
        if (self::$ext === null) {
            self::$ext = FileSystem::loadConfigArray('ext');
        }
        if (!isset(self::$ext[$name])) {
            throw new RuntimeException('ext [' . $name . '] is not supported yet for get');
        }
        $supported_sys_based = ['lib-depends', 'lib-suggests', 'ext-depends', 'ext-suggests', 'arg-type'];
        if ($key !== null && in_array($key, $supported_sys_based)) {
            $m_key = match (PHP_OS_FAMILY) {
                'Windows' => ['-windows', '-win', ''],
                'Darwin' => ['-macos', '-unix', ''],
                'Linux' => ['-linux', '-unix', ''],
                default => throw new RuntimeException('OS ' . PHP_OS_FAMILY . ' is not supported'),
            };
            foreach ($m_key as $v) {
                if (isset(self::$ext[$name][$key . $v])) {
                    return self::$ext[$name][$key . $v];
                }
            }
            return $default;
        }
        if ($key !== null) {
            return self::$ext[$name][$key] ?? $default;
        }
        return self::$ext[$name];
    }

    /**
     * @throws FileSystemException
     */
    public static function getExts(): array
    {
        if (self::$ext === null) {
            self::$ext = FileSystem::loadConfigArray('ext');
        }
        return self::$ext;
    }
}
