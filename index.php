<?php

namespace {
    function asset(...$lot) {
        return \count($lot) < 2 ? \Asset::get(...$lot) : \Asset::set(...$lot);
    }
}

namespace x\asset {
    function body($body) {
        $js = \Hook::fire('asset.js', [\Asset::join('*.js')], null, \Asset::class);
        $script = \Hook::fire('script', [\Asset::join('text/javascript') . \Asset::join('text/js')], null, \Asset::class);
        return $body . $js . $script; // Put inline JS after remote JS
    }
    function content($content) {
        return \strtr($content ?? "", [
            '</body>' => \Hook::fire('body', [""], null, \Asset::class) . '</body>',
            '</head>' => \Hook::fire('head', [""], null, \Asset::class) . '</head>'
        ]);
    }
    function head($head) {
        $css = \Hook::fire('asset.css', [\Asset::join('*.css')], null, \Asset::class);
        $style = \Hook::fire('style', [\Asset::join('text/css')], null, \Asset::class);
        return $head . $css . $style; // Put inline CSS after remote CSS
    }
    \Hook::set('body', __NAMESPACE__ . "\\body", 10);
    \Hook::set('content', __NAMESPACE__ . "\\content", 0);
    \Hook::set('head', __NAMESPACE__ . "\\head", 10);
}