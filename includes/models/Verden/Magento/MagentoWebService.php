<?php
/**
 * 
 * Classe para trabalhar com o Webservice da Magento(Stub de Servi�o)
 * Importante sempre finalizar a sess�o ( m�todo finalizaSessao )ap�s utilizar a classe
 * @author    Tito Junior
 * 
 */

class Model_Verden_Magento_MagentoWebService {
	
	/**
	 * Endere�o do WebService.
	 *
	 * @var string
	 */
	private $_ws;
	
	/**
	 * 
	 * Usu�rio de identifica��o do cliente na Magento.
	 * @var string
	 */
	private $_usuario;
	
	/**
	 *
	 * Senha de identifica��o do cliente na Magento.
	 * @var string
	 */
	private $_senha;
	
	/**
	 * inst�ncia do WebService
	 *
	 * @var string
	 */
	private $_webservice;

	/**
	 * Sess�o do Webservice
	 */
	private $_session;
	
	/**
	 * valida se sess�o do Webservice foi iniciada
	 */
	private $_session_valid = false;
	
	/**
	 * Array de Mensagens de Erros.
	 *
	 * @var array
	 */
	private $_array_erros = array ();
	
	/**
	 * Habilita fun��o de Debug.
	 *
	 * @var boolean
	 */
	private $_debug = false;
	
	/**
	 * Array de Mensagens de Debug.
	 *
	 * @var array
	 */
	private $_debugMSG = array ();
	
	/**
	 * Construtor.
	 * @param string $ws Endere�o do Webservice.
	 * @param string $login Login de Conex�o do Webservice.
	 * @param string $pass Senha de Conex�o do Webservice.
	 */
	public function __construct() {
			
		$this->_ws = MAGENTO_WSDL;
		$this->_usuario = MAGENTO_USUARIO;
		$this->_senha = MAGENTO_SENHA;
			
		try {
			
			// conecta com o SoapClient
			$this->_webservice = new SoapClient ( $this->_ws );			
			$this->_webservice->soap_defencoding = 'UTF-8';
			$this->_webservice->decode_utf8 = true;			
			
		} catch ( Exception $e ) {
			throw new Exception ( 'Erro ao conectar no WebService' );
		}
	}	
	
	/**
	 * Adiciona mensagem de erro ao array
	 * @param String $mensagem Mensagem de Erro
	 */
	private function _Erro($mensagem) {
		$msg = "Data:" . date ( "d/m/Y H:i:s" ) . " <br>" . $mensagem;
		$this->_array_erros [] = $msg;
		throw new Exception ( $msg );
	}
	
	private function _iniciaSessao(){
		
		try {

			$this->_session = $this->_webservice->login ($this->_usuario, $this->_senha);
			$this->_session_valid = true;
			
		}catch ( Exception $e ) {
			$this->_session_valid = false;
			throw new Exception ( 'Erro ao iniciar sess�o no WebService' );
		}
		
	}
	
	public function _encerraSessao(){
	
		try {
				
			$this->_session = $this->_webservice->endSession ( $this->_session );
			$this->_session_valid = false;
				
		}catch ( Exception $e ) {			
			throw new Exception ( 'Erro ao finalizar sess�o no WebService' );
		}
	
	}
	
	public function cadastraProduto( $sku, $produto ){
		
		if($this->_session_valid == false){
			$this->_iniciaSessao();	
		}
		
		try {			
		
			// get attribute set
			$attributeSets = $this->_webservice->catalogProductAttributeSetList($this->_session);
			$attributeSet = current($attributeSets);
			
			$result = $this->_webservice->catalogProductCreate($this->_session, 'simple', $attributeSet->set_id, $sku, $produto);			
		
		} catch ( Exception $e ) {
			throw new RuntimeException( 'Erro ao cadastrar Produto ' . $sku );
		}
		
		return $result;
		
	}
	
	public function atualizaProduto( $idProduto, $produto ){
	
		if($this->_session_valid == false){
			$this->_iniciaSessao();
		}
	
		try {			
				
			$result = $this->_webservice->catalogProductUpdate( $this->_session, $idProduto, $produto );
	
		} catch (Exception $e) {
			throw new RuntimeException( 'Erro ao atualizar Produto ID ' . $idProduto . ' - ' . $e->getMessage() );
		}
	
		return $result;
	
	}
	
	public function buscaProduto( $sku ){
	
		if($this->_session_valid == false){
			$this->_iniciaSessao();
		}
	
		try {
	
			$result = $this->_webservice->catalogProductInfo( $this->_session, $sku, null, null, 'sku' );
			return $result->product_id;
			
		} catch (SoapFault $e) {
			return false;
		}
	}
	
}	