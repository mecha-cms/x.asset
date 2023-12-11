<?php

foreach ([
    '*.css' => static function ($value, $key) {
        $link = $value['link'] ?? "";
        $path = $value['path'] ?? "";
        $x = false !== strpos($link, '://') || 0 === strpos($link, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $link . '?v=' . (is_file($path) ? filemtime($path) : 0) : $link;
        extract($value[2], EXTR_SKIP);
        if (isset($href) && is_callable($href)) {
            $value[2]['href'] = fire($href, [$u, $value, $key], null, Asset::class);
        } else if (empty($href)) {
            $value[2]['href'] = $u;
        }
        $value[0] = 'link';
        $value[1] = false;
        $value[2]['rel'] = 'stylesheet';
        return new HTML($value);
    },
    '*.js' => static function ($value, $key) {
        $link = $value['link'] ?? "";
        $path = $value['path'] ?? "";
        $x = false !== strpos($link, '://') || 0 === strpos($link, '//');
        if (!$path && !$x) {
            return '<!-- ' . $key . ' -->';
        }
        $u = $path ? $link . '?v=' . (is_file($path) ? filemtime($path) : 0) : $link;
        extract($value[2], EXTR_SKIP);
        if (isset($src) && is_callable($src)) {
            $value[2]['src'] = fire($src, [$u, $value, $key], null, Asset::class);
        } else if (empty($src)) {
            $value[2]['src'] = $u;
        }
        $value[0] = 'script';
        return new HTML($value);
    },
    'text/css' => static function ($value, $key) {
        $v = substr((string) strstr($value['link'] ?? "", ';'), 1);
        $value[0] = 'style';
        $value[1] = 0 === strpos($v, 'base64,') ? base64_decode(substr(rawurldecode($v), 7)) : rawurldecode($v);
        return new HTML($value);
    },
    'text/js' => static function ($value, $key) {
        $v = substr((string) strstr($value['link'] ?? "", ';'), 1);
        $value[0] = 'script';
        $value[1] = 0 === strpos($v, 'base64,') ? base64_decode(substr(rawurldecode($v), 7)) : rawurldecode($v);
        return new HTML($value);
    }
] as $k => $v) {
    Asset::_($k, $v);
}

// <https://www.rfc-editor.org/rfc/rfc9239#name-text-javascript>
Asset::_('text/javascript', Asset::_('text/js'));