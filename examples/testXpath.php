<?php
$doc = new DOMDocument();

$doc->load(dirname(__FILE__) . '/xml/altaDoda.xml');
$doc->preserveWhiteSpace = false;
$doc->formatOutput = false;

$arXPath = array(
    'query' => '(.//. | .//@* | .//namespace::*)[/dodas]',
    'namespaces' => array('xd' => 'http://www.w3.org/2000/09/xmldsig#')
);
$data = $doc->C14N(false, true,$arXPath,null);

echo $data;
