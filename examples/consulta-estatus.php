<?php 
ini_set('display_errors', 'on');
require(dirname(__FILE__) . '/../matce-doda.php');

use HEGAR\MatceDoda\MATCEDodaSignature;

$doda = new MATCEDodaSignature();

try{
	$doda->setXml(dirname(__FILE__) . '/xml/consultaEstatus.xml',true);
	$doda->setPrivateKey(dirname(__FILE__) . '/keys/private.key.pem');
	$doda->setPublicKey(dirname(__FILE__) . '/keys/public.pem');
	$doda->firma();
	$signed = $doda->getDoc();
} catch (Exception $e){
	echo 'Error: ' . $e->getMessage();
}
header('Content-type: text/xml');
echo $signed->saveXML();
