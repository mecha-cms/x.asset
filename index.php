<?php

namespace {
    function asset(...$lot) {
        return \count($lot) < 2 ? \Asset::get(...$lot) : \Asset::set(...$lot);
    }
}

namespace x\asset {
    function body($body) {
        $script = \Asset::join('*.js') . \Asset::join('text/javascript') . \Asset::join('text/js');
        return \substr($body, 0, -7) . $script . \substr($body, -7);
    }
    function content($content) {
        // Capture the `<body>…</body>` part
        $a = \strpos($content, ($s = '<body') . '>') ?: \strpos($content, $s . ' ') ?: \strpos($content, $s . "\n") ?: \strpos($content, $s . "\r") ?: \strpos($content, $s . "\t");
        if (false !== $a) {
            $b = \substr($content, 0, $a);
            $d = \strpos($c = \substr($content, $a), '</body>');
            if (false !== $d) {
                $e = \substr($c, $d += 7);
                $c = \substr($c, 0, $d);
                $content = $b . \Hook::fire('body', [$c, $a, $d], null, \Asset::class) . $e;
            }
        }
        // Capture the `<head>…</head>` part
        $a = \strpos($content, ($s = '<head') . '>') ?: \strpos($content, $s . ' ') ?: \strpos($content, $s . "\n") ?: \strpos($content, $s . "\r") ?: \strpos($content, $s . "\t");
        if (false !== $a) {
            $b = \substr($content, 0, $a);
            $d = \strpos($c = \substr($content, $a), '</head>');
            if (false !== $d) {
                $e = \substr($c, $d += 7);
                $c = \substr($c, 0, $d);
                $content = $b . \Hook::fire('head', [$c, $a, $d], null, \Asset::class) . $e;
            }
        }
        return $content;
    }
    function head($head) {
        $style = \Asset::join('*.css') . \Asset::join('text/css');
        return \substr($head, 0, -7) . $style . \substr($head, -7);
    }
    \Hook::set('body', __NAMESPACE__ . "\\body", 10);
    \Hook::set('content', __NAMESPACE__ . "\\content", 0);
    \Hook::set('head', __NAMESPACE__ . "\\head", 10);
}