<?php

namespace {
    function asset(...$lot) {
        return \count($lot) < 2 ? \Asset::get(...$lot) : \Asset::set(...$lot);
    }
}

namespace x\asset {
    function body($body) {
        $v = \Asset::join('*.js') . \Asset::join('text/javascript') . \Asset::join('text/js');
        return \substr($body, 0, -7) . $v . \substr($body, -7);
    }
    function content($content) {
        if (!$content || false === \strpos($content, '</')) {
            return $content;
        }
        // Capture the `<head>…</head>` part
        if (
            false !== ($a = \strpos($content, '<head>')) ||
            false !== ($a = \strpos($content, '<head ')) ||
            false !== ($a = \strpos($content, "<head\n")) ||
            false !== ($a = \strpos($content, "<head\r")) ||
            false !== ($a = \strpos($content, "<head\t"))
        ) {
            if (false !== ($b = \strpos($content, '</head>'))) {
                $content = \substr($content, 0, $a) . \Hook::fire('head', [\substr($content, $a, ($b += 7) - $a)], null, \Asset::class) . \substr($content, $b);
            }
        }
        // Capture the `<body>…</body>` part
        if (
            false !== ($a = \strpos($content, '<body>')) ||
            false !== ($a = \strpos($content, '<body ')) ||
            false !== ($a = \strpos($content, "<body\n")) ||
            false !== ($a = \strpos($content, "<body\r")) ||
            false !== ($a = \strpos($content, "<body\t"))
        ) {
            if (false !== ($b = \strpos($content, '</body>'))) {
                $content = \substr($content, 0, $a) . \Hook::fire('body', [\substr($content, $a, ($b += 7) - $a)], null, \Asset::class) . \substr($content, $b);
            }
        }
        return $content;
    }
    function head($head) {
        $v = \Asset::join('*.css') . \Asset::join('text/css');
        return \substr($head, 0, -7) . $v . \substr($head, -7);
    }
    \Hook::set('body', __NAMESPACE__ . "\\body", 10);
    \Hook::set('content', __NAMESPACE__ . "\\content", 0);
    \Hook::set('head', __NAMESPACE__ . "\\head", 10);
}