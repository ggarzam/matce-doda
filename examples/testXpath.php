<?php
ini_set('display_errors', 'on');
$doc = new DOMDocument();

$doc->load(dirname(__FILE__) . '/xml/altaDoda.xml');
$doc->preserveWhiteSpace = false;
$doc->formatOutput = false;

$xpath = new DOMXPath($doc);

$query = array();
$query['query'] = './dodas';


$res = $doc->C14N(false, true, $query);

var_dump($res);