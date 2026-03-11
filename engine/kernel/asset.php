<?php

final class Asset extends Proxy {

    public static $lot = [];

    public static function get($path = null, int $i = 1) {
        if (is_array($path)) {
            $r = [];
            foreach ($path as $v) {
                $r[$v] = self::get($v, $i);
            }
            return $r;
        }
        if (isset($path)) {
            if (0 === strpos($path, 'data:')) {
                $type = substr($path, 5, strpos($path, ';') - 5);
                return self::$lot[$i][$type][$path] ?? null;
            }
            $path = stream_resolve_include_path($path) ?: $path;
            return self::$lot[$i]['*.' . pathinfo($path, PATHINFO_EXTENSION)][$path] ?? null;
        }
        return self::$lot[$i] ?? [];
    }

    public static function join(string $x, string $join = "") {
        if ($v = self::_($x)) {
            $task = $v[0];
            if ($lot = self::$lot[1][$x] ?? 0) {
                usort($lot, function ($a, $b) {
                    return $a['stack'] <=> $b['stack'];
                });
                $r = [];
                if (is_callable($task)) {
                    foreach ($lot as $k => $v) {
                        $r[] = fire($task, [$v, $k], static::class);
                    }
                } else {
                    foreach ($lot as $k => $v) {
                        if (isset($v['path']) && is_file($v['path'])) {
                            $r[] = file_get_contents($v['path']);
                        }
                    }
                }
                return implode($join, $r);
            }
        }
        return "";
    }

    public static function link(string $link) {
        $path = self::path($link);
        return isset($path) ? To::link($path) : (false !== strpos($link, '://') || 0 === strpos($link, '//') || 0 === strpos($link, 'data:') ? $link : null);
    }

    public static function path(string $path) {
        if (false !== strpos($path, '://') || 0 === strpos($path, '//') || 0 === strpos($path, 'data:')) {
            // External link, nothing to check!
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
            if (false === strpos($path, '://' . $host) && 0 !== strpos($path, '//' . $host)) {
                return null;
            }
            if ($path !== substr($path, 0, strcspn($path, '?&#'))) {
                return null; // Internal link, with query and/or hash part
            }
            $path = To::path($path);
        }
        $path = substr($path, 0, strcspn($path, '?&#')); // Ignore query and/or hash part in the file path
        // Full path, be quick!
        if (0 === strpos($path = strtr($path, '/', D), PATH)) {
            return exist($path, 1) ?: null;
        }
        // Return the path relative to the `.\lot\asset` or `.` folder if exist!
        $path = ltrim($path, D);
        return exist([
            LOT . D . 'asset' . D . $path,
            PATH . D . $path
        ], 1) ?: null;
    }

    public static function let($path = null) {
        if (is_array($path)) {
            foreach ($path as $v) {
                self::let($v);
            }
        } else if (isset($path)) {
            $path = stream_resolve_include_path($path) ?: $path;
            $x = 0 === strpos($path, 'data:') ? substr($path, 5, strpos($path, ';') - 5) : '*.' . pathinfo($path, PATHINFO_EXTENSION);
            self::$lot[0][$x][$path] = 1;
            unset(self::$lot[1][$x][$path]);
        } else {
            self::$lot[1] = [];
        }
    }

    public static function set($path, float $stack = 10, array $lot = []) {
        if (is_array($path)) {
            $i = 0;
            foreach ($path as $v) {
                self::set($v, $stack + $i, $lot);
                $i += 0.1;
            }
        } else {
            $path = stream_resolve_include_path($path) ?: $path;
            $x = 0 === strpos($path, 'data:') ? substr($path, 5, strpos($path, ';') - 5) : '*.' . pathinfo(substr($path, 0, strcspn($path, '?&#')), PATHINFO_EXTENSION);
            if (!isset(self::$lot[0][$x][$path])) {
                self::$lot[1][$x][$path] = [
                    '0' => null,
                    '1' => "",
                    '2' => $lot,
                    'link' => self::link($path),
                    'path' => self::path($path),
                    'stack' => (float) $stack
                ];
            }
        }
    }

}