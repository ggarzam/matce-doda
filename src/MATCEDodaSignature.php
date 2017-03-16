<?php 

/**
 *	
 */
namespace HEGAR\MatceDoda;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use DOMDocument;


class MATCEDodaSignature
{
	// Metodos del WebService
	const ALTA_DODA = 'altaDoda';
	const CONSULTA_ESPECIFICA = 'consultaEspecificaDoda';
	const CONSULTA_ESTATUS = 'consultaEstatus';
	const CONSULTA_GENERAL = 'consultaGeneralDoda';
	const ELIMINAR = 'eliminarDoda';
	const MODIFICAR = 'modificarDoda';

	/**
	 * @var DOMDocument|null Contiene el XML que se va firmar y enviar al webService
	 */
	private $doc = null;

	/**
	 * @var string Contiene el nombre del metodo que se va a usar
	 */
	private $webServiceMethod = '';

	/**
	 * @var XMLSecurityKey Contiene el objeto con la llave privada
	 */
	private $objKey = null;

	/**
	 *	@var DOMElement Contiene el elemento de la llave privada en formato RSA
	 */
	private $publicRSA = null;

	public function __construct()
	{
		$this->doc = new DOMDocument();
                $this->doc->preserveWhiteSpace = false;
                $this->doc->formatOutput = false;
	}

	/**
	 * Establece un xml para ser firmado
	 *
	 * @param string $xml el xml que sera firmado
	 * @param bool $isFile Indica que el primer parametro es un archivo
	 * @throws Exception En el caso de que no pueda ser cargado el XML
	 * @return null|MatceDoda
	 */
	public function setXml($xml, $isFile = false)
	{
		if($isFile){
			if(!$this->doc->load($xml)){
				throw new \Exception("Error al cargar el documento proporcionado");
				
			}
		} else {
			if (!$this->doc->loadXML($xml)){
				throw new \Exception("Error al cargar la cadena proporcionada");
			}
		}
		$this->setWebServiceName();
		return $this;
	}

	public function getDoc()
	{
		return $this->doc;
	}

	/**
	 * Establece el metodo que se va a usar para procesar la respuesta
	 *
	 * @throws Exception en dado caso que no se proporcione un xml valido
	 */
	protected function setWebServiceName()
	{
		if(is_null($this->doc)){
			throw new \Exception("No se ha cargado un documento valido para poder determinar el webService a usar");
		}

		// Buscamos si es un altaDoda
		$node = $this->doc->getElementsByTagName(self::ALTA_DODA);
		if($node->length > 0){
			$this->webServiceMethod = self::ALTA_DODA;
			return;
		}

		// Buscamos si es una consultaEspecifica
		$node = $this->doc->getElementsByTagName(self::CONSULTA_ESPECIFICA);
		if($node->length > 0){
			$this->webServiceMethod = self::CONSULTA_ESPECIFICA;
			return;
		}

		$node = $this->doc->getElementsByTagName(self::CONSULTA_ESTATUS);
		if($node->length > 0) {
			$this->webServiceMethod = self::CONSULTA_ESTATUS;
			return;
		}

		$node = $this->doc->getElementsByTagName(self::CONSULTA_GENERAL);
		if($node->length > 0){
			$this->webServiceMethod = self::CONSULTA_GENERAL;
			return;
		}

		$node = $this->doc->getElementsByTagName(self::ELIMINAR);
		if($node->length > 0){
			$this->webServiceMethod = self::ELIMINAR;
			return;
		}

		$node = $this->doc->getElementsByTagName(self::MODIFICAR);
		if($node->length > 0){
			$this->webServiceMethod = self::MODIFICAR;
			return;
		}
		$this->webServiceMethod = '';
		throw new \Exception("El Xml proporcionado no pertenece a un contrato de MATCE-DODA");
	}

	/**
	 * Establecemos las rutas a la llave privada
	 * 
	 * @param 	string 		$key 			Llave privada o ruta para cargar la llave privada
	 * @param 	bool 		$isFile 		Indica si el primer parametro es la ruta para la llave
	 * @param 	string 		$passphrase 	Indica la contrasena de la llave pridad 
	 * @throws 	Exception 					Si no se puede cargar correctamente la llave privada
	 * @return 	bool 						True si se cargo correctamente la llave
	 */

	public function setPrivateKey($key, $isFile = true, $passphrase=null)
	{
		$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1,array('type' => 'private'));
		$objKey->passphrase = $passphrase;
		$objKey->loadKey($key,$isFile);
		$this->objKey = $objKey;
	}

	/**
	 *	Establece la llave publica en formato RSA para poder usada en el nodo KeyInfo
	 *
	 * 	@param 	string 		$key 			Llave publica o ruta a ella misma ver (openssl_pkey_get_private)
	 *  @param  bool 		$isFile 		Determina si el primer parametro es la ruta al archivo
	 *  @throws Exception 					Si la llave no puede ser cargada
	 */
	public function setPublicKey($key, $isFile=true)
	{
		if($isFile){
			$key = file_get_contents($key);
			if(!$key){
				throw new Exception("Error al tratar de cargar el contenido de la llave privada: " . $key, 1);
			}
		}
		$res = openssl_pkey_get_public($key);
		$details = openssl_pkey_get_details($res);

		$d = new \DOMDocument();
		$rsaKey = $d->createElement('RSAKeyValue');
		$modulus = $d->createElement('Modulus',base64_encode($details['rsa']['n']));
		$exponent = $d->createElement('Exponent',base64_encode($details['rsa']['e']));

		$rsaKey->appendChild($modulus);
		$rsaKey->appendChild($exponent);

		$d->appendChild($rsaKey);

		$this->publicRSA = $rsaKey;
	}

	/**
	 *	Regresa el DOMElement conteniendo la llave publica en formato RSA
	 *
	 * @throws Exception 	En el caso que no se encuentre cargada la llave publica o no sea el formato correcto
	 * @return DOMElement 	El nodo que contiene la llave publica
	 */
	public function getPublicKeyNode()
	{
		if(is_null($this->publicRSA)){
			throw new Exception('No se ha cargado la llave publica', 1);
		}

		if(!$this->publicRSA instanceof \DOMElement){
			throw new \Exception('La llave que se encuentra cargada no es correcta', 1);
		}

		return $this->publicRSA;	
	}

	/**
	 * Realiza el firmado del documento Enviado agregandole el nodo Signature
	 *
	 * @throws Exception 			En el caso de que no se pueda firmar el documento
	 */
	public function firma()
	{
		if(is_null($this->doc) || !$this->doc instanceof \DOMDocument){
			throw new \Exception('No se ha cargado un XML para ser firmado', 1);
		}

		if(is_null($this->objKey) || !$this->objKey instanceof XMLSecurityKey){
			throw new \Exception('No se ha cargado la llave privada para poder hacer el firmado', 1);
		}

		if(is_null($this->publicRSA) || !$this->publicRSA instanceof \DOMElement){
			throw new \Exception('No se ha cargado la llave publica para poder hacer el firmado', 1);
		}

		$objSig = new XMLSecurityDSig('ds');
		$objSig->setCanonicalMethod(XMLSecurityDSig::C14N_COMMENTS);
		$transforms = array('http://www.w3.org/2000/09/xmldsig#enveloped-signature',
			XMLSecurityDSig::C14N_COMMENTS,
			array('http://www.w3.org/TR/1999/REC-xpath-19991116' => array('query' => './dodas'))
		);
		$objSig->addReference($this->doc,XMLSecurityDSig::SHA256,$transforms,array('force_uri' => true));

		$objSig->sign($this->objKey);
		$objSig->appendSignature($this->doc->getElementsByTagName('dodas')->item(0));
	}
}
?>