<?php
/**
 * 
 * Cron para processar integra��o com sistema ERP KPL - �bacos via webservice   
 * @author Tito Junior <titojunior1@gmail.com>
 * 
 */
class Model_Verden_Cron_KplCron {
	
	/**
	 * 
	 * Objeto Kpl (inst�ncia do webservice kpl)
	 * @var Model_Verden_Kpl_KplWebService
	 */
	private $_kpl;	

	/**
	 * Construtor
	 * @param 
	 */
	public function __construct () {

		echo "- Iniciando Cron para processar integracao com sistema ERP KPL via webservice" . PHP_EOL;
		
	}

	/**
	 * 
	 * Atualiza estoque do Kpl
	 * 
	 * 
	 */
	public function AtualizaEstoqueKpl () {

		ini_set ( 'memory_limit', '512M' );
			
		if ( empty ( $this->_kpl ) ) {
			$this->_kpl = new Model_Verden_Kpl_KplWebService();
		}
		echo "- importando estoques dispon�veis do cliente Verden - " . date ( "d/m/Y H:i:s" ) . PHP_EOL;

		try {
			$chaveIdentificacao = KPL_KEY;
			$estoques = $this->_kpl->EstoquesDisponiveis( $chaveIdentificacao );
			if ( ! is_array ( $estoques ['EstoquesDisponiveisResult'] ) ) {
				throw new Exception ( 'Erro ao buscar Estoque - ' . $estoques );
			}
			if ( $estoques ['EstoquesDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003 ) {
				echo "N�o existem estoques dispon�veis para integra��o" . PHP_EOL;
			} else {
				
				$kpl_estoques = new Model_Verden_Kpl_EstoqueKpl();
				$retorno = $kpl_estoques->ProcessaEstoqueWebservice ( $estoques ['EstoquesDisponiveisResult'] ['Rows'] );
				if(is_array($retorno)){
					// ERRO					
				}	
			}
				
			echo "- importa��o de estoque do cliente Verden realizada com sucesso" . PHP_EOL;
		
		} catch ( Exception $e ) {
			echo "- erros ao importar estoque do cliente Verden: " . $e->getMessage () . PHP_EOL;
		}
		unset ( $this->_kpl );
		unset ( $chaveIdentificacao );

		echo "- Finalizando cron para atualizar estoque do Kpl" . PHP_EOL;
	}

	/**
	 * 
	 * Cadastrar fornecedores do Kpl.
	 */
	public function CadastraFornecedoresKpl () {

		
		if ( empty ( $this->_kpl ) ) {
			$this->_kpl = new Model_Verden_Kpl_KplWebService ();
		}
		
		echo "- importando Fornecedores do cliente Verden -  " . date ( "d/m/Y H:i:s" ) . PHP_EOL;
		
		try {
			$chaveIdentificacao = KPL_KEY;
			
			$fornecedores = $this->_kpl->FornecedoresDisponiveis ( $chaveIdentificacao );
			if ( ! is_array ( $fornecedores ['FornecedoresDisponiveisResult'] ) ) {
				throw new Exception ( 'Erro ao buscar Fornecedores' );
			}
			if ( $fornecedores ['FornecedoresDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003 ) {
				echo "N�o existem fornecedores dispon�veis para integra��o".PHP_EOL;
			
			}else{
				$kpl_fornecedores = new Model_Verden_Kpl_Fornecedor ();
					$retorno = $kpl_fornecedores->ProcessaFornecedoresWebservice ( $fornecedores ['FornecedoresDisponiveisResult'] );
				}
				echo "- importa��o de fornecedores do cliente Verden realizada com sucesso" . PHP_EOL;
			
		
		} catch ( Exception $e ) {
			echo "- erros ao importar os fornecedores do cliente Verden: " . $e->getMessage () . PHP_EOL;
		}
		unset ( $this->_kpl );
		
		
		echo "- Finalizando cron para cadastrar fornecedores do Kpl" . PHP_EOL;
	
	}

	/**
	 * 
	 * Cadastrar Notas Entrada do Kpl
	 */
	public function CadastraNotasEntradaKpl () {

		ini_set ( 'memory_limit', '512M' );
		
		if (empty ( $this->_kpl )) {
			$this->_kpl = new Model_Wms_Kpl_KplWebService ( $cli_id );
		}
		
		echo "- importando notas de entrada do cliente {$cli_id}, warehouse {$empwh_id} - " . date ( "d/m/Y H:i:s" ) . PHP_EOL;
		try {
			$chaveIdentificacao = KPL_KEY;
			$notas_entrada = $this->_kpl->NotasFiscaisEntradaDisponiveis ( $chaveIdentificacao );
			if ( ! is_array ( $notas_entrada ['NotasFiscaisEntradaDisponiveisResult'] ) ) {
				throw new Exception ( 'Erro ao buscar notas de entrada' );
			}
			if ( $notas_entrada ['NotasFiscaisEntradaDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003 ) {
				echo "N�o existem pedidos de entrada dispon�veis para integra��o".PHP_EOL;
				
			}else{
				$kpl_notas_entrada = new Model_Wms_Kpl_NotasEntrada ( $cli_id, $empwh_id );
					$retorno = $kpl_notas_entrada->ProcessaNotasEntradaWebservice ( $notas_entrada ['NotasFiscaisEntradaDisponiveisResult'] );
					
					if(is_array($retorno)){
						// gravar logs de erro						
						$this->_log->gravaLogErros($cron_id, $retorno);	
					}
				}
				
		} catch ( Exception $e ) {
			echo "- erros ao importar as notas de entrada do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
		}
		unset ( $this->_kpl );		
	
	}

	/**
	 * 
	 * Cadastrar Pedidos Saida do Kpl
	 */
	public function CadastraPedidosSaidaKpl () {

		//ini_set ( 'memory_limit', '-1' );
		ini_set ( 'memory_limit', '512M' );
		
		// Solicita Pedidos Saida Dispon�veis
			if ( empty ( $this->_kpl ) ) {
				$this->_kpl = new Model_Wms_Kpl_KplWebService ( $cli_id );
			}
			
			echo "- importando pedidos de sa�da do cliente {$cli_id}, warehouse {$empwh_id} - " . date ( "d/m/Y H:i:s" ) . PHP_EOL;
			try {
				$chaveIdentificacao = KPL_KEY;
				$pedidos_disponiveis = $this->_kpl->PedidosDisponiveis ( $chaveIdentificacao );
				if ( ! is_array ( $pedidos_disponiveis ['PedidosDisponiveisResult'] ) ) {
					throw new Exception ( 'Erro ao buscar notas de sa�da' );
				}
				if ( $pedidos_disponiveis ['PedidosDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003 ) {
					echo "N�o existem pedidos de sa�da dispon�veis para integra��o".PHP_EOL;

				}else{
					$kpl = new Model_Wms_Kpl_Pedido ( $cli_id, $empwh_id );
					$retorno = $kpl->ProcessaArquivoSaidaWebservice ( $pedidos_disponiveis ['PedidosDisponiveisResult'] );
						if(is_array($retorno)){
							// gravar logs de erro						
							$this->_log->gravaLogErros($cron_id, $retorno);					
						}	
					}

					echo "- importa��o de pedidos do cliente {$cli_id} realizada com sucesso" . PHP_EOL;
					
			} catch ( Exception $e ) {
				echo "- erros ao importar os pedidos de sa�da do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
			}
			unset ( $this->_kpl );
		
		echo "- Finalizando cron para cadastrar pedidos de sa�da da Kpl" . PHP_EOL;
		
	}

	/**
	 * 
	 * Importa os produtos dispon�veis.
	 * @throws Exception
	 */
	public function CadastraProdutosKpl () {

		ini_set ( 'memory_limit', '512M' );
			
		if ( empty ( $this->_kpl ) ) {
			$this->_kpl = new Model_Verden_Kpl_KplWebService();
		}
		echo "- importando produtos do cliente Verden - " . date ( "d/m/Y H:i:s" ) . PHP_EOL;

		try {
			
			echo PHP_EOL;
			echo "Consultando produtos disponiveis para integracao " . PHP_EOL;
			$chaveIdentificacao = KPL_KEY;
			$produtos = $this->_kpl->ProdutosDisponiveis ( $chaveIdentificacao );
			if ( ! is_array ( $produtos ['ProdutosDisponiveisResult'] ) ) {
				throw new Exception ( 'Erro ao buscar Produtos - ' . $produtos );
			}
			if ( $produtos ['ProdutosDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003 ) {
				echo "N�o existem produtos dispon�veis para integra��o" . PHP_EOL;
			} else {
				
				$kpl_produtos = new Model_Verden_Kpl_Produtos();
					$retorno = $kpl_produtos->ProcessaProdutosWebservice ( $produtos ['ProdutosDisponiveisResult'] ['Rows'] );
					if(is_array($retorno))
					{
						// ERRO					
					}	
				}
				
				echo "- importa��o de produtos do cliente {$cli_id} realizada com sucesso" . PHP_EOL;
		
		} catch ( Exception $e ) {
			echo "- erros ao importar os produtos do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
		}
		unset ( $this->_kpl );
		unset ( $chaveIdentificacao );

		echo "- Finalizando cron para cadastrar produtos do Kpl" . date ( "d/m/Y H:i:s" ) . PHP_EOL;
	}
	
	/**
	 *
	 * Importa os pre�os dispon�veis.
	 * @throws Exception
	 */
	public function CadastraPrecosKpl () {
	
		ini_set ( 'memory_limit', '512M' );
			
		if ( empty ( $this->_kpl ) ) {
			$this->_kpl = new Model_Verden_Kpl_KplWebService();
		}
		echo "- importando pre�os do cliente Verden - " . date ( "d/m/Y H:i:s" ) . PHP_EOL;
	
		try {
			$chaveIdentificacao = KPL_KEY;
			$precos = $this->_kpl->PrecosDisponiveis( $chaveIdentificacao );
			if ( ! is_array ( $precos ['PrecosDisponiveisResult'] ) ) {
				throw new Exception ( 'Erro ao buscar Pre�os - ' . $precos );
			}
			if ( $precos ['PrecosDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003 ) {
				echo "N�o existem pre�os dispon�veis para integra��o" . PHP_EOL;
			} else {
					
				$kpl_pre�os = new Model_Verden_Kpl_Precos();
				$retorno = $kpl_pre�os->ProcessaPrecosWebservice( $precos ['PrecosDisponiveisResult'] ['Rows'] );
				if(is_array($retorno))
				{
					// ERRO
				}
			}
				
			echo "- importa��o de pre�os do cliente {$cli_id} realizada com sucesso" . PHP_EOL;
				
		} catch ( Exception $e ) {
		echo "- erros ao importar os pre�os do cliente Verden: " . $e->getMessage () . PHP_EOL;
		}
		unset ( $this->_kpl );
		unset ( $chaveIdentificacao );
	
		echo "- Finalizando cron para atualizar pre�os do Kpl" . PHP_EOL;
	}
	

	/**
	 * 
	 * Importa as notas fiscais dispon�veis.
	 */
	public function NotasFiscaisDisponiveis () {

		ini_set ( 'memory_limit', '728M' );
		//ini_set ( 'memory_limit', '-1' );
		

		foreach ( $this->_clientes as $indice => $cli_id ) {
			
			if ( empty ( $this->_kpl ) ) {
				$this->_kpl = new Model_Wms_Kpl_KplWebService ( $cli_id );
			}
			
			echo "- importando notas fiscais do cliente {$cli_id} " . date ( "d/m/Y H:i:s" ) . PHP_EOL;
			$cron_id = $this->_log->getCronId('NotasFiscaisDisponiveis');
			try {
				$empwh_id = $this->_kpl->getArmazem ();
				$chaveIdentificacao = $this->_kpl->getChaveIdentificacao ();
				$notas_fiscais = $this->_kpl->NotasFiscaisSaidaDisponiveis ( $chaveIdentificacao );
				if (! is_array ( $notas_fiscais ['NotasFiscaisSaidaDisponiveisResult'] )) {
					throw new Exception ( 'Erro ao buscar Produtos' );
				}
				if ($notas_fiscais ['NotasFiscaisSaidaDisponiveisResult'] ['ResultadoOperacao'] ['Codigo'] == 200003) {
					echo "N�o existem notas dispon�veis para integra��o".PHP_EOL;
					
				}else{
					
					if($cli_id == 78)
					{
						$ns = new Model_Wms_Kpl_PedidoGWBR( $cli_id, $empwh_id );
						$ns->capturaDadosNf ( $notas_fiscais ['NotasFiscaisSaidaDisponiveisResult'] ['Rows'] );
						
					}
					else
					{
						$ns = new Model_Wms_Kpl_Pedido ( $cli_id, $empwh_id );
						$ns->capturaDadosNf ( $notas_fiscais ['NotasFiscaisSaidaDisponiveisResult'] ['Rows'] );
						
					}
					
					
				}
				
			} catch ( Exception $e ) {
				echo "- erros ao importar notas fiscais do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
				$array_erro[] = "- erros ao importar notas fiscais do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
			}
			if(is_array($array_erro)){
				// gravar logs de erro						
				$this->_log->gravaLogErros($cron_id, $array_erro);					
			}
			unset ( $this->_kpl );
		}
		echo "- Finalizando cron para baixar notas fiscais dispon�veis KPL" . PHP_EOL;
	}
	
	/**
	 *
	 * Importa as notas fiscais dispon�veis com pdf
	 */
	public function NotasFiscaisSaidaDisponiveisComPdf(){
		foreach ( $this->_clientes as $indice => $cli_id ) {
			// Inicialmente somente para Meu Espelho
			if($cli_id==77){
				
				$this->_kpl = new Model_Wms_Kpl_KplWebService ( $cli_id );
				echo "- importando notas fiscais do cliente {$cli_id} " .date("d/m/Y H:i:s"). PHP_EOL;
				$cron_id = $this->_log->getCronId('NotasFiscaisSaidaDisponiveisComPdf');
				try{	
					$empwh_id = $this->_kpl->getArmazem ();
					$pedido = new Model_Wms_Kpl_Pedido ( $cli_id, $empwh_id );
					$pedido->NotasFiscaisSaidaDisponiveisComPdf();
			
				} catch ( Exception $e ) {
						echo "- erros ao importar notas fiscais do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
					$array_erro[] = "- erros ao importar notas fiscais do cliente {$cli_id}: " . $e->getMessage () . PHP_EOL;
				}
			}			
			if(is_array($array_erro)){
				// gravar logs de erro						
				$this->_log->gravaLogErros($cron_id, $array_erro);					
			}			
		}
		echo "- Finalizando cron para baixar notas fiscais dispon�veis KPL" . PHP_EOL;
	}
}