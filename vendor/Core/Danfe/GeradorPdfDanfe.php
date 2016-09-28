<?php

/**
 * Core_Danfe_GeradorPdfDanfe
 *
 * Gera o PDF da DANFE.
 *
 * @package Core
 * @name Core_Danfe_GeradorPdfDanfe
 * @author Humberto dos Reis Rodrigues <humberto.rodrigues_assertiva@totalexpress.com.br>
 *
 */
class Core_Danfe_GeradorPdfDanfe {

	/**
	 * Gerar o PDF da DANFE a partir de um XML da DANFE.
	 *
	 * @param string $arquivo
	 * @param string $chave
	 * @param string $diretorio
	 * @throws RuntimeException
	 */
	public static function exportarXmlParaPdf($arquivo, $chave, $diretorio) {
	    $diretorio = self::_getCaminhoCompleto($diretorio);

	    // necess�rio converter para UTF-8 para evitar problemas com a classe DomDocument
	    $arquivo = utf8_encode($arquivo);

	    $exportadorDanfe = new DanfeNFePHP($arquivo);
	    $chave = $exportadorDanfe->montaDANFE();
	    $filename = $diretorio . DIRECTORY_SEPARATOR . "NFe{$chave}.pdf";

	    $exportadorDanfe->printDANFE($filename,'F');

	    unset($exportadorDanfe);

	    if(!@file_exists($filename)) {
	        throw new RuntimeException("Falha ao tentar gerar o arquivo NFe{$chave}.pdf em $diretorio. Verifique as permiss�es do diret�rio ou se o diret�rio existe.");
	    }

		return $filename;
	}

	/**
	 * Gerar o PDF da danfe a partir do conte�do em base64.
	 *
	 * @param string $arquivo
	 * @param string $chave
	 * @param string $diretorio
	 * @throws RuntimeException
	 * @see http://php.net/manual/pt_BR/function.base64-decode.php, http://php.net/manual/pt_BR/function.base64-encode.php
	 */
	public static function exportarBase64ParaPdf($arquivo, $chave, $diretorio) {
	    $diretorio = self::_getCaminhoCompleto($diretorio);
	    $filename = $diretorio . DIRECTORY_SEPARATOR . "NFe{$chave}.pdf";

	    $handle = @fopen($filename, "w");

	    if(!$handle) {
	        throw new RuntimeException('Falha ao tentar gerar o arquivo. Verifique as permiss�es do diret�rio.');
	    }

	    $stream = base64_decode($arquivo);

	    if(!$stream) {
	        throw new RuntimeException('Falha ao tentar decodificar os dados. Verifique se o conte�do n�o cont�m espa�os.');
	    }

	    @fwrite($handle, $stream);
	    @fclose($handle);

	    if(!@file_exists($filename)) {
	        throw new RuntimeException("Falha ao tentar gerar o arquivo NFe{$chave}.pdf em $diretorio. Verifique as permiss�es do diret�rio ou se o diret�rio existe.");
	    }

		return $filename;
	}

	/**
	 *
	 * @param string $diretorio
	 * @throws RuntimeException
	 * @return string
	 */
	private static function _getCaminhoCompleto($diretorio) {
	    $caminho = realpath($diretorio);

	    if(false === $caminho) {
	        throw new RuntimeException('O diret�rio para gravar a DANFE n�o existe ou n�o est� sem permiss�o de escrita.');
	    }

	    return $caminho;
	}
}