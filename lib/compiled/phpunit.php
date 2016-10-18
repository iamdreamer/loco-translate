<?php
/**
 * Downgraded for PHP 5.2 compatibility. Do not edit.
 */
class LocoDomQuery extends ArrayIterator { public static function parse($source) { $dom = new DOMDocument('1.0', 'UTF-8'); $dom->preserveWhitespace = true; $dom->formatOutput = false; $source = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>' . $source . '</body></html>'; $used_errors = libxml_use_internal_errors(true); $opts = 0; defined('LIBXML_HTML_NODEFDTD') and $opts |= LIBXML_HTML_NODEFDTD; $parsed = $dom->loadHTML($source, $opts); $errors = libxml_get_errors(); $used_errors || libxml_use_internal_errors(false); libxml_clear_errors(); if ($errors || !$parsed) { $e = new Loco_error_ParseException('Unknown parse error'); foreach ($errors as $error) { $e = new Loco_error_ParseException(trim($error->message)); $e->setContext($error->line, $error->column, $source); if (LIBXML_ERR_FATAL === $error->level) { throw $e; } } if (!$parsed) { throw $e; } } return $dom; } public function __construct($value) { if (is_array($value)) { $nodes = $value; } else { if ($value instanceof DOMDocument) { $nodes = array($value->documentElement); } else { if ($value instanceof DOMNodeList) { $nodes = array(); foreach ($value as $node) { $nodes[] = $node; } } else { $value = self::parse($value); $nodes = array($value->documentElement); } } } parent::__construct($nodes); } public function eq($index) { $q = new LocoDomQuery(array()); if ($el = $this[$index]) { $q[] = $el; } return $q; } public function find($value) { $q = new LocoDomQuery(array()); $f = new _LocoDomQueryFilter($value); foreach ($this as $el) { foreach ($f->filter($el) as $match) { $q[] = $match; } } return $q; } public function text() { $s = ''; foreach ($this as $el) { $s .= $el->textContent; } return $s; } public function html() { $s = ''; foreach ($this as $outer) { foreach ($outer->childNodes as $inner) { $s .= $inner->ownerDocument->saveXML($inner); } break; } return $s; } public function attr($name) { foreach ($this as $el) { return $el->getAttribute($name); } return null; } public function serialize() { $pairs = array(); foreach (array('input', 'select', 'textarea', 'button') as $type) { foreach ($this->find($type) as $field) { $name = $field->getAttribute('name'); if (!$name) { continue; } if ($field->hasAttribute('type')) { $type = $field->getAttribute('type'); } if ('select' === $type) { $value = null; $f = new _LocoDomQueryFilter('option'); foreach ($f->filter($field) as $option) { if ($option->hasAttribute('value')) { $_value = $option->getAttribute('value'); } else { $_value = $option->nodeValue; } if ($option->hasAttribute('selected')) { $value = $_value; break; } else { if (is_null($value)) { $value = $_value; } } } if (is_null($value)) { $value = ''; } } else { if ('checkbox' === $type || 'radio' === $type) { if ($field->hasAttribute('checked')) { $value = $field->getAttribute('value'); } else { continue; } } else { if ('file' === $type) { $value = ''; } else { if ($field->hasAttribute('value')) { $value = $field->getAttribute('value'); } else { $value = $field->textContent; } } } } $pairs[] = sprintf('%s=%s', rawurlencode($name), rawurlencode($value)); } } return implode('&', $pairs); } }
class _LocoDomQueryFilter { private $tag; private $attr = array(); public function __construct($value) { if (!preg_match('/^([a-z1-6]*)(\\.[-a-z]+)?(\\[(\\w+)="(.+)"\\])?$/i', $value, $r)) { throw new InvalidArgumentException('Bad filter, ' . $value); } if ($r[1]) { $this->tag = $r[1]; } if (!empty($r[2])) { $this->attr['class'] = substr($r[2], 1); } if (!empty($r[3])) { $this->attr[$r[4]] = $r[5]; } } public function filter(DOMElement $el) { if ($this->tag) { $list = $el->getElementsByTagName($this->tag); } else { $list = $el->childNodes; } if ($this->attr) { return $this->reduce($list); } return $list; } public function reduce(DOMNodeList $list) { $reduced = array(); foreach ($list as $node) { foreach ($this->attr as $name => $value) { if (!$node->hasAttribute($name)) { continue 2; } $values = array_flip(explode(' ', $node->getAttribute($name))); if (!isset($values[$value])) { continue 2; } } $reduced[] = $node; } return $reduced; } }
