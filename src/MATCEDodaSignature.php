<?php 

/**
 *	
 */
namespace HEGAR\MatceDoda;

use RobRichards\XMLSecLibs\XMLSecurityDSign;
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
	public $doc = null;

	/**
	 * @var string Contiene el nombre del metodo que se va a usar
	 */
	public $webServiceMethod = '';

	public function __construct()
	{
		$this->doc = new DOMDocument();

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
	 * @param string $key 			Llave privada o ruta para cargar la llave privada
	 * @param bool $isFile 			Indica si el primer parametro es la ruta para la llave
	 * @param string $passphrase 	Indica la contrasena de la llave pridad 
	 * @throws 
	 */
}


?>