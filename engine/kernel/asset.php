<?php

final class Asset extends Genome {

    public static $lot = [];

    public static function URL(string $url) {
        $path = self::path($url);
        return isset($path) ? To::URL($path) : (false !== strpos($url, '://') || 0 === strpos($url, '//') || 0 === strpos($url, 'data:') ? $url : null);
    }

    public static function get($path = null, int $i = 1) {
        if (is_array($path)) {
            $out = [];
            foreach ($path as $v) {
                $out[$v] = self::get($v, $i);
            }
            return $out;
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
            if (isset(self::$lot[1][$x])) {
                $any = (new Anemone(self::$lot[1][$x]))->sort([1, 'stack'], true);
                $out = [];
                if (is_callable($task)) {
                    foreach ($any as $k => $v) {
                        $out[] = fire($task, [$v, $k], static::class);
                    }
                } else {
                    foreach ($any as $k => $v) {
                        if (isset($v['path']) && is_file($v['path'])) {
                            $out[] = file_get_contents($v['path']);
                        }
                    }
                }
                return implode($join, $out);
            }
        }
        return "";
    }

    public static function path(string $path) {
        if (false !== strpos($path, '://') || 0 === strpos($path, '//') || 0 === strpos($path, 'data:')) {
            // External URL, nothing to check!
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? "";
            if (false === strpos($path, '://' . $host) && 0 !== strpos($path, '//' . $host)) {
                return null;
            }
            if ($path !== strtok($path, '?&#')) {
                return null; // Internal URL, with query and/or hash part
            }
            $path = To::path($path);
        }
        $path = strtok($path, '?&#'); // Ignore query and/or hash part in the file path
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
            $x = 0 === strpos($path, 'data:') ? substr($path, 5, strpos($path, ';') - 5) : '*.' . pathinfo(strtok($path, '?&#'), PATHINFO_EXTENSION);
            if (!isset(self::$lot[0][$x][$path])) {
                self::$lot[1][$x][$path] = [
                    '0' => c2f(static::class),
                    '1' => "",
                    '2' => $lot,
                    'link' => self::URL($path),
                    'path' => self::path($path),
                    'stack' => (float) $stack
                ];
            }
        }
    }

}