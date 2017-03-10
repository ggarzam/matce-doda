<?php 

ini_set('display_errors', '1');
require(dirname(__FILE__) . '/../matce-doda.php');

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

$doc = new DOMDocument();
$doc->formatOutput = false;
$doc->preserveWhiteSpace = false;
$doc->load(dirname(__FILE__) . '/xml/consultaEstatus.xml');

$objSig = new XMLSecurityDSig('');

$objSig->setCanonicalMethod(XMLSecurityDSig::C14N_COMMENTS);

$transforms = array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::C14N_COMMENTS,
	array('http://www.w3.org/TR/1999/REC-xpath-19991116' => array('query' => '/dodas'))
);

$objSig->addReference($doc,XMLSecurityDSig::SHA1,$transforms,array('force_uri' => true));

// cargamos la llave privada
$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type'=>'private'));
// Si lleva contrasena se tiene que poner antes $objKey->passphrase = '<password>'
$objKey->loadKey(dirname(__FILE__) . '/keys/private.key.pem', TRUE);

$objSig->sign($objKey,$doc->documentElement);

// Agregamso la llave publica asociada
// @todo Crear una nueva funcion para agregar la llave publice en forma de XML
$objSig->add509Cert(file_get_contents(dirname(__FILE__) . '/keys/public.pem'));

/// Generamos el XML para poder agregar al nodo KeyInfo


$objSig->appendSignature($doc->getElementsByTagName('dodas')->item(0));

//$doc->save(dirname(__FILE__) . '/doda-consulta-sign.xml');


header("Content-type: text/xml");
print_r($doc->saveXML());