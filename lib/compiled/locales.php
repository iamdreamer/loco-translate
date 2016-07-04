<?php

/**
 * Compiled from Loco core. Do not edit!
 * Wed, 29 Jun 2016 13:50:11 +0100
 */
function loco_parse_locale($tag)
{
    $tag = trim(strtr($tag, '_+', '--'), '-');
    if (!$tag) {
        throw new InvalidArgumentException('Empty language tag');
    }
    if (!preg_match('/^([a-z]{2,3})(?:-([a-z]{3}(?:-[a-z]{3}){0,2}))?(?:-([a-z]{4}))?(?:-([a-z]{2}|[0-9]{3}))?(?:-((?:[0-9][a-z0-9]{3,8}|[a-z0-9]{5,8})(?:-(?:[0-9][a-z0-9]{3,8}|[a-z0-9]{5,8}))*))?(?:-([a-wy-z0-9](?:-[a-z0-9]{2,8})+(?:-[a-wy-z0-9](?:-[a-z0-9]{2,8})+)*))?(?:-(x(?:-[a-z0-9]{1,8})+))?$/i', $tag, $tags)) {
        if (preg_match('/^x(?:-[a-z0-9]{1,8})+/i', $tag)) {
            return array('extension' => array($tag));
        }
        throw new InvalidArgumentException('Invalid language tag, ' . $tag);
    }
    $data['lang'] = strtolower($tags[1]);
    if (isset($tags[2]) && ($subtag = $tags[2])) {
        $data['extlang'] = strtolower($subtag);
    }
    if (isset($tags[3]) && ($subtag = $tags[3])) {
        $data['script'] = strtoupper($subtag[0]) . strtolower(substr($subtag, 1));
    }
    if (isset($tags[4]) && ($subtag = $tags[4])) {
        $data['region'] = strtoupper($tags[4]);
    }
    if (isset($tags[5]) && ($subtag = $tags[5])) {
        $data['variant'] = array_values(array_unique(explode('-', strtolower($subtag)), SORT_REGULAR));
    }
    if (isset($tags[6]) && ($subtag = $tags[6])) {
        $subtags = array();
        $offset = -1;
        $parts = explode('-', $subtag);
        while ($value = array_shift($parts)) {
            if (isset($value[1])) {
                $subtags[$offset] .= '-' . $value;
            } else {
                $subtags[++$offset] = $value;
            }
        }
        $data['extension'] = $subtags;
    }
    if (isset($tags[7]) && ($subtag = $tags[7])) {
        $data['extension'][] = 'x-' . substr($subtag, 2);
    }
    return $data;
}