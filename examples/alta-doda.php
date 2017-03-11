<?php 
ini_set('display_errors', 'on');
require(dirname(__FILE__) . '/../matce-doda.php');

use HEGAR\MatceDoda\MATCEDodaSignature;

$doda = new MATCEDodaSignature();

try{
	$doda->setXml(dirname(__FILE__) . '/xml/altaDoda.xml',true);
} catch (Exception $e){
	echo 'Error: ' . $e->getMessage();
}

echo $doda->webServiceMethod;

 ?>