<?php

class Calcula_Frete{


    public function __construct(){
        $mensagens['0'] = 'Processamento com sucesso';
        $mensagens['-1'] = 'Código de serviço inválido';
        $mensagens['-2'] = 'CEP de origem inválido';
        $mensagens['-3'] = 'CEP de destino inválido';
        $mensagens['-4'] = 'Peso excedido';
        $mensagens['-5'] = 'O Valor Declarado não deve exceder R$ 10.000,00';
        $mensagens['-6'] = 'Serviço indisponível para o trecho informado';
        $mensagens['-7'] = 'O Valor Declarado é obrigatório para este serviço';
        $mensagens['-8'] = 'Este serviço não aceita Mão Própria';
        $mensagens['-9'] = 'Este serviço não aceita Aviso de Recebimento ';
        $mensagens['-10'] = 'Precificação indisponível para o trecho informado';
        $mensagens['-11'] = 'Para definição do preço deverão ser informados, também, o comprimento, a largura e altura do objeto em centímetros (cm).';
        $mensagens['-12'] = 'Processamento com sucesso';
        $mensagens['-13'] = 'Processamento com sucesso';
        $mensagens['-14'] = 'Processamento com sucesso';
        $mensagens['-15'] = 'Processamento com sucesso';
        $mensagens['-16'] = 'Processamento com sucesso';
        $mensagens['-17'] = 'Processamento com sucesso';
        $mensagens['-18'] = 'Processamento com sucesso';
        $mensagens['-19'] = 'Processamento com sucesso';
        $mensagens['-20'] = 'Processamento com sucesso';
        $mensagens['-21'] = 'Processamento com sucesso';
        $mensagens['-22'] = 'Processamento com sucesso';
        $mensagens['-23'] = 'Processamento com sucesso';
        $mensagens['-24'] = 'Processamento com sucesso';
        $mensagens['-25'] = 'Processamento com sucesso';
        $mensagens['-26'] = 'Processamento com sucesso';
        $mensagens['-27'] = 'Processamento com sucesso';
        $mensagens['-28'] = 'Processamento com sucesso';
        $mensagens['-29'] = 'Processamento com sucesso';
        $mensagens['-30'] = 'Processamento com sucesso';
        $mensagens['-31'] = 'Processamento com sucesso';
        $mensagens['-32'] = 'Processamento com sucesso';
        $mensagens['-33'] = 'Processamento com sucesso';
        $mensagens['-34'] = 'Processamento com sucesso';
        $mensagens['-35'] = 'Processamento com sucesso';
        $mensagens['-36'] = 'Processamento com sucesso';
        $mensagens['-37'] = 'Processamento com sucesso';
        $mensagens['-38'] = 'Processamento com sucesso';
        $mensagens['-39'] = 'Processamento com sucesso';
        $mensagens['-40'] = 'Processamento com sucesso';
        $mensagens['-41'] = 'Processamento com sucesso';
        $mensagens['-42'] = 'Processamento com sucesso';
        $mensagens['-43'] = 'Processamento com sucesso';
        $mensagens['-44'] = 'Processamento com sucesso';
        $mensagens['-45'] = 'Processamento com sucesso';
        $mensagens['888'] = 'Processamento com sucesso';
        $mensagens['006'] = 'Processamento com sucesso';
        $mensagens['007'] = 'Processamento com sucesso';
        $mensagens['008'] = 'Processamento com sucesso';
        $mensagens['009'] = 'Processamento com sucesso';
        $mensagens['010'] = 'Processamento com sucesso';
        $mensagens['7'] = 'Processamento com sucesso';
        $mensagens['99'] = 'Processamento com sucesso';
        define('SHORTINIT', true);
        require_once( dirname(__FILE__) . '/wp-load.php' );
        require_once( dirname(__FILE__) . '/wp-includes/formatting.php' );
        $this->init();
    }

    public function init(){
        $frete_params = $this->get_params();
        $validation_result = $this->is_valid_params( $frete_params );
    }

    public function get_params(){
        $frete_params[ 'empresa' ] = sanitize_key( @$_REQUEST[ 'nCdEmpresa' ] );
        $frete_params[ 'senha' ] = sanitize_key( @$_REQUEST[ 'sDsSenha' ] );
        $frete_params[ 'servico' ] = explode( ',', @$_REQUEST[ 'nCdServico' ] );
        $frete_params[ 'cep_origem' ] = sanitize_key( @$_REQUEST[ 'sCepOrigem' ] );
        $frete_params[ 'cep_destino' ] = sanitize_key( @$_REQUEST[ 'sCepOrigem' ] );
        $frete_params[ 'peso' ] = isset( $_REQUEST[ 'nVlPeso' ] ) ? intval( $_REQUEST[ 'nVlPeso' ] ) : 0 ;
        $frete_params[ 'formato' ] = isset( $_REQUEST[ 'nCdFormato' ] ) ? intval( $_REQUEST[ 'nCdFormato' ] ) : 1 ;
        $frete_params[ 'comprimento' ] = isset( $_REQUEST[ 'nVlComprimento' ] ) ? intval( $_REQUEST[ 'nVlComprimento' ] ) : 0 ;
        $frete_params[ 'altura' ] = isset( $_REQUEST[ 'nVlAltura' ] ) ? intval( $_REQUEST[ 'nVlAltura' ] ) : 0 ;
        $frete_params[ 'largura' ] = isset( $_REQUEST[ 'nVlLargura' ] ) ? intval( $_REQUEST[ 'nVlLargura' ] ) : 0 ;
        $frete_params[ 'diametro' ] = isset( $_REQUEST[ 'nVlDiametro' ] ) ? intval( $_REQUEST[ 'nVlDiametro' ] ) : 0 ;
        $frete_params[ 'mao_propria' ] = isset( $_REQUEST[ 'sCdMaoPropria' ] ) ? $_REQUEST[ 'sCdMaoPropria' ] : 'N' ;
        $frete_params[ 'valor_declarado' ] = isset( $_REQUEST[ 'nVlValorDeclarado' ] ) ? $_REQUEST[ 'nVlValorDeclarado' ] : 0 ;
        $frete_params[ 'aviso_recebimento' ] = isset( $_REQUEST[ 'sCdAvisoRecebimento' ] ) ? $_REQUEST[ 'sCdAvisoRecebimento' ] : 'N' ;
    }

    public function is_valid_params( $params ){
        $params[ 'cep_origem' ] = '';
        $soma_d = $params[ 'comprimento' ] + $params[ 'altura' ] + $params[ 'largura' ];
        if( $soma_d > 200 ){
            return
        }
        $peso_cubico = $params[ 'comprimento' ] * $params[ 'altura' ] * $params[ 'largura' ] / 4800;

    }

}
new Calcula_Frete();
