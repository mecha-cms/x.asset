<?php namespace x;

function asset($content) {
    return \strtr($content ?? "", [
        '</body>' => \Hook::fire('asset:body', [""], null, \Asset::class) . '</body>',
        '</head>' => \Hook::fire('asset:head', [""], null, \Asset::class) . '</head>'
    ]);
}

\Hook::set('asset:head', function ($content) {
    $style = \Hook::fire('asset:style', [\Asset::join('text/css')], null, \Asset::class);
    $css = \Hook::fire('asset.css', [\Asset::join('*.css')], null, \Asset::class);
    return $content . $css . $style; // Put inline CSS after remote CSS
});

\Hook::set('asset:body', function ($content) {
    $script = \Hook::fire('asset:script', [\Asset::join('text/javascript') . \Asset::join('text/js')], null, \Asset::class);
    $js = \Hook::fire('asset.js', [\Asset::join('*.js')], null, \Asset::class);
    return $content . $js . $script; // Put inline JS after remote JS
});

\Hook::set('content', __NAMESPACE__ . "\\asset", 0);