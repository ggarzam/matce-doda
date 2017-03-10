<?php 

/**
 * matce-doda.php
 *
 * @author Ing. Guadalupe Garza Moreno
 * @version 1.0.0
 */

$matce_scrdir = dirname(__FILE__) . '/scr/';

require($matce_scrdir . 'libs/XMLSecurityKey.php');
require($matce_scrdir . 'libs/XMLSecurityDSig.php');
require($matce_scrdir . 'libs/XMLSecEnc.php');
require($matce_scrdir . 'MATCEDodaSignature.php');

 ?>