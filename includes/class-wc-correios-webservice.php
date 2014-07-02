<?php
/**
 * wcorreios_webservice class.
 */
class WP_Correios_Webservice {
    public $correios_ws_url = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?StrRetorno=xml';
	/**
	 * Initialize the Correios shipping method.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
    }

    /**
     * Initializes the method.
     *
     * @return void
     */
    public function init() {
        $this->create_post_type_service();
        $this->create_metabox_service();
        add_action( 'init' , array( $this, 'show_wcorreios_options_config' ) );
        add_action( 'init' , array( $this, 'register_auto_update' ) );
        add_action( 'update_option_wcorreios_webservice_from' , array( $this, 'update_zip_code_from' )  , 10 , 2);
        add_action( 'update_option' , array( $this, 'update_wcorreios_webservices_enabled' )  , 10 , 3 );
        add_action( 'get_wcorreios_config' , array( $this, 'get_wcorreios_config' ) );
        add_filter( 'cron_schedules', array( $this , 'add_cron_five_minutes_interval_support') );
        add_filter( 'wcorreios_webservice_rates_update', array( $this , 'wcorreios_webservice_rates_update') );
        return;
    }
    public function update_zip_code_from( $old_values , $new_values){
        var_dump( 'update_zip_code_from' );
        global $wpdb;
        $new_zip_code = $new_values[ 'from_zip' ];
        $update_rate_query = "UPDATE " . $wpdb->prefix . "wcorreios_webservice_frete SET
             cep_origem='" . $new_zip_code . "',
             lastupdate= NULL
        ";
        $wpdb->query( $update_rate_query );

        $update_rate_query = "UPDATE " . $wpdb->prefix . "wcorreios_webservice_frete_modelo SET
             cep_origem='" . $new_zip_code . "',
             lastupdate= NULL
        ";
        $wpdb->query( $update_rate_query );
    }

    public function install(){
        $this->create_database_tables();
        $this->populate_database_tables();
        add_action( 'admin_notices', array( $this , 'install_result') );
    }

    public function install_result(){
        echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios Webservice depends to %s to work!', 'wcorreios_webservice' ), '<a href="https://github.com/wpbrasil/odin">inicialização</a>' ) . '</p></div>';
    }

    public function show_wcorreios_options_config(){
        $services = $this->get_services();
        $services_options = null;
        foreach( $services as $id => $service ){
            $services_options[] = array(
                'id' => 'wcorreios_option_' . $id,
                'label' => sprintf( '%s (%s)' , $service, $id),
                'type' => 'select',
                'options'       => array( // Obrigatório (adicione aque os ids e títulos)
                    'disable'   => 'Não',
                    'enable'   => 'Sim',
                )
            );
        }

        $update_status = $this->get_update_status();
        $update_status_options = array(
            array(
                'id'   => 'wcorreios_total', // Obrigatório
                'label' => __( 'Total', 'wcorreios_webservice' ) . ' : ' . $update_status[ 'total' ], // Obrigatório
                'type' => 'title', // Obrigatório
                ),
            array(
                'id'   => 'wcorreios_expireds', // Obrigatório
                'label' => __( 'Desatualizados', 'wcorreios_webservice' ) . ' : ' . $update_status[ 'expireds' ], // Obrigatório
                'type' => 'title', // Obrigatório
                )
            );


        $wcorreios_options = new Odin_Theme_Options(
            'wcorreios_webservice', // Slug/ID da página (Obrigatório)
            __( 'Configuração WebService Correios', 'wcorreios_webservice' ), // Titulo da página (Obrigatório
            'read'
        );

        $wcorreios_options->set_tabs(
            array(
                array(
                    'id'    => 'wcorreios_webservice_config',
                    'title' => __( 'Configuração', 'wcorreios_webservice' ) // Título da aba.
                ),
                array(
                    'id'    => 'wcorreios_webservice_update',
                    'title' => __( 'Atualização', 'wcorreios_webservice' ) // Título da aba.
                ),
                array(
                    'id'    => 'wcorreios_webservice_from',
                    'title' => __( 'Endereço Origem', 'wcorreios_webservice' ) // Título da aba.
                ),
                array(
                    'id'    => 'wcorreios_webservice_services',
                    'title' => __( 'Métodos de Serviço', 'wcorreios_webservice' ) // Título da aba.
                )
            )
        );

        $wcorreios_options->set_fields(
            array(
                'wcorreios_webservice_config' => array(
                    'tab'   => 'wcorreios_webservice_config', // Sessão da aba odin_general
                    'title' => __( 'Configuração', 'wcorreios_webservice' ),
                    'fields' => array(
                        array(
                            'id' => 'auto_update',
                            'label' => __( 'Atualização automática', 'wcorreios_webservice' ),
                            'type' => 'checkbox',
                            'description' => __( '' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'expire_time_in_seconds',
                            'label' => __( 'Atualizar registros a cada', 'wcorreios_webservice' ),
                            'type' => 'text',
                            'description' => __( 'Tempo em segundos' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'max_rate_updates',
                            'label' => __( 'Número máximo atualizações por requisição', 'wcorreios_webservice' ),
                            'attributes'  => array( // Optional (html input elements)
                                'type' => 'number',
                                'min'  => 1
                            ),
                        ),
                        array(
                            'id' => 'enterprise_id',
                            'label' => __( 'Código da Empresa', 'wcorreios_webservice' ),
                            'type' => 'text',
                            'description' => __( 'Opcional' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'enterprise_pass',
                            'label' => __( 'Código de Acesso', 'wcorreios_webservice' ),
                            'type' => 'text',
                            'description' => __( 'Opcional' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimum_length',
                            'label' => __( 'Comprimento Mínimo', 'wcorreios_webservice' ),
                            'type' => 'input',
                            'attributes'  => array( // Optional (html input elements)
                                'type' => 'number',
                                'min'  => 16
                            ),
                            'description' => __( 'cm' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimum_width',
                            'label' => __( 'Largura Mínima', 'wcorreios_webservice' ),
                            'type' => 'input',
                            'attributes'  => array( // Optional (html input elements)
                                'type' => 'number',
                                'min'  => 11
                            ),
                            'description' => __( 'cm' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimum_height',
                            'label' => __( 'Altura Mínima', 'wcorreios_webservice' ),
                            'type' => 'input',
                            'attributes'  => array( // Optional (html input elements)
                                'type' => 'number',
                                'min'  => 2
                            ),
                            'description' => __( 'cm' , 'wcorreios_webservice' )
                        ),
                    )
                ),
                'wcorreios_webservice_update' => array(
                    'tab'   => 'wcorreios_webservice_update', // Sessão da aba odin_general
                    'title' => __( 'Atualizações', 'wcorreios_webservice' ),
                    'fields' => $update_status_options
                ),
                'wcorreios_webservice_from' => array(
                    'tab'   => 'wcorreios_webservice_from', // Sessão da aba odin_general
                    'title' => __( 'Endereço de Origem', 'wcorreios_webservice' ),
                    'fields' => array(
                        array(
                            'id' => 'from_zip',
                            'label' => __( 'Cep Origem', 'wcorreios_webservice' ),
                            'type' => 'text',
                        ),
                    )
                ),
                'wcorreios_webservice_services' => array(
                    'tab'   => 'wcorreios_webservice_services', // Sessão da aba odin_general
                    'title' => __( 'Método de Serviços', 'wcorreios_webservice' ),
                    'fields' => $services_options
                ),
            )
        );
    }

    public function create_post_type_service(){

        if( !class_exists( 'Odin_Post_Type') ){
            include_once( get_stylesheet_directory() . '/core/classes/class-post-type.php' );
        }

        $wcorreios_service = new Odin_Post_Type(
            'Método Serviço', // Nome (Singular) do Post Type.
            'wcorreios_service'           // Slug do Post Type.
        );

        $wcorreios_service->set_arguments(
            array(
                'supports'     => array( 'title' ),
                'hierarchical' => false,
                'menu_icon'    => 'dashicons-images-alt2',
                'exclude_from_search' => true,
                'rewrite' => false
            )
        );

        $wcorreios_service->set_labels(
            array(
                'menu_name'          =>  __( 'Método Serviços' , 'wcorreios_webservice' ),
                'singular_name'      => 'Método Serviço',
                'name'               => 'Método Serviço',
                'add_new'            => 'Adicionar Novo Método Serviço',
                'add_new_item'       => 'Adicionar Novo Método Serviço',
                'edit_item'          => 'Editar Método Serviço',
                'new_item'           => 'Novo Método Serviço',
                'all_items'          => 'Todos os Método Serviços',
                'view_item'          => 'Ver Método Serviço',
                'search_items'       => 'Procurar Método Serviço',
                'not_found'          => 'Nenhum Método Serviço Encontrado',
                'not_found_in_trash' => 'Nenhum Método Serviço Encontrado na Lixeira',
                'parent_item_colon'  => '',
            )
        );

        add_filter( 'manage_edit-wcorreios_service_columns', array($this, 'colunas_exibicao_listagem' ));
        add_action( 'manage_wcorreios_service_posts_custom_column', array($this, 'valores_exibicao_listagem'), 10,2);
    }

    public function create_metabox_service(){
        if( !class_exists( 'Odin_Metabox') ){
            include_once( get_stylesheet_directory() . '/core/classes/class-metabox.php' );
        }

        $service_mtb = new Odin_Metabox(
            'service_info', // Slug/ID do Metabox (obrigatório)
            'Informações do Serviço', // Nome do Metabox  (obrigatório)
            'wcorreios_service', // Slug do Post Type (opcional)
            'normal', // Contexto (opções: normal, advanced, ou side) (opcional)
            'high' // Prioridade (opções: high, core, default ou low) (opcional)
        );

        $service_mtb->set_fields(
            array(
                array(
                    'id'          => 'service_id',
                    'label'       => __( 'Serviço', 'wcorreios_webservice' ),
                    'type'        => 'input',
                    'attributes'  => array( // Optional (html input elements)
                        'type' => 'number'
                    ),
                ),
            )
        );
    }

    public function get_services(){
        $args = array(
            'post_type' => 'wcorreios_service',
            'posts_per_page' => -1
            );

        $posts_services = get_posts( $args );

        $services = array();
        foreach( $posts_services as $post_service ){
            $services[ get_post_meta( $post_service->ID , 'service_id' , true ) ] = $post_service->post_title;
        }

        return $services;
    }

    public function colunas_exibicao_listagem( $columns ) {

        $icl_translations  = $columns["icl_translations"];
        unset($columns["date"]);
        unset($columns["wpseo-metadesc"]);
        unset($columns["wpseo-title"]);
        unset($columns["wpseo-focuskw"]);
        unset($columns["wpseo-score"]);
        return $columns;
    }

    public function valores_exibicao_listagem( $column, $post_id ) {
        $valor = get_post_meta( $post_id, $column , true );
        echo $valor;
    }

    public function create_database_tables(){
        global $wpdb;

        $table_name = $wpdb->prefix . 'wcorreios_webservice_frete';

        if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            //table is not created. you may create the table here.
            $query = "CREATE TABLE `". $wpdb->prefix ."wcorreios_webservice_frete` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `servico` varchar(50) DEFAULT NULL,
                      `nome` varchar(50) DEFAULT NULL,
                      `regiao` varchar(150) DEFAULT NULL,
                      `prazo` int(11) DEFAULT NULL,
                      `peso` decimal(8,4) DEFAULT NULL,
                      `valor` decimal(8,2) DEFAULT NULL,
                      `cep_origem` varchar(50) DEFAULT NULL,
                      `cep_destino_ini` int(11) DEFAULT NULL,
                      `cep_destino_fim` int(11) DEFAULT NULL,
                      `lastupdate` datetime DEFAULT NULL,
                      `cep_dest_ref` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
            $wpdb->get_results( $query );
        }

        $table_name = $wpdb->prefix . 'wcorreios_webservice_frete_model';

        if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            //table is not created. you may create the table here.
            $query = "CREATE TABLE `". $wpdb->prefix ."wcorreios_webservice_frete_model` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `servico` varchar(50) DEFAULT NULL,
                      `nome` varchar(50) DEFAULT NULL,
                      `regiao` varchar(150) DEFAULT NULL,
                      `prazo` int(11) DEFAULT NULL,
                      `peso` decimal(8,4) DEFAULT NULL,
                      `valor` decimal(8,2) DEFAULT NULL,
                      `cep_origem` varchar(50) DEFAULT NULL,
                      `cep_destino_ini` int(11) DEFAULT NULL,
                      `cep_destino_fim` int(11) DEFAULT NULL,
                      `lastupdate` datetime DEFAULT NULL,
                      `cep_dest_ref` int(11) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
            $wpdb->get_results( $query );
        }
    }

    public function populate_database_tables(){
        global $wpdb;
        $sql_data = @file_get_contents( WOO_CORREIOS_WEBSERVICE_PATH . 'data/default_rates_queries.sql' );
        $query_insert = 'INSERT INTO ' . $wpdb->prefix .'wcorreios_webservice_frete_model VALUES ' .( $sql_data );
        $wpdb->query( $query_insert );
    }

    public function get_wcorreios_config(){
        $wcorrreios['config'] = get_option( 'wcorreios_webservice_config' );
        $wcorrreios['update']  = get_option( 'wcorreios_webservice_update' );
        $wcorrreios['from']  = get_option( 'wcorreios_webservice_from' );
        $wcorrreios['services']  = get_option( 'wcorreios_webservice_services' );
        return $wcorrreios;
    }

    public function get_update_status(){
        global $wpdb;
        $wcorrreios_config = $this->get_wcorreios_config();
        $expire_time_in_seconds = intval( $wcorrreios_config['config']['expire_time_in_seconds'] );

        $query_total = "SELECT count( id ) as total from " . $wpdb->prefix . "wcorreios_webservice_frete";
        $query_expired = "SELECT count( id ) as expired from " . $wpdb->prefix . "wcorreios_webservice_frete WHERE (lastupdate IS NULL OR UNIX_TIMESTAMP( lastupdate )  < " . ( mktime() - $expire_time_in_seconds) . ")";
        $results_total = $wpdb->get_row( $query_total);
        $results_expired = $wpdb->get_row( $query_expired );

        return array( 'total' => $results_total->total , 'expireds' => $results_expired->expired );
    }

    public function register_auto_update(){
        if ( !wp_next_scheduled( 'wcorreios_webservice_rates_update' ) ) {
            wp_schedule_event( time() + 5 , '5_minutes' ,'wcorreios_webservice_rates_update' );
        }

        $to = 'ricardohaas@msn.com';
        $next_scheduled = wp_next_scheduled( 'wcorreios_webservice_rates_update' );
        $subject = 'Next execution in '. ( $next_scheduled - time() );
        $message = 'Next execution';
        //wp_mail( $to, $subject, $message );
    }

    public function wcorreios_webservice_rates_update(){
        global $wpdb;

        $wcorrreios_config = $this->get_wcorreios_config();
        //wp_mail( 'ricardohaas@msn.com', 'Iniciado wcorreios_webservice_rates_update', 'wcorreios_webservice_rates_update' );
        if( !isset( $wcorrreios_config[ 'config' ]['auto_update'] ) ){
            //auto update disabled
            wp_mail( 'ricardohaas@msn.com', 'Disabled wcorreios_webservice_rates_update', 'wcorreios_webservice_rates_update' );
            return;
        }

        $expire_time_in_seconds = intval( $wcorrreios_config['config']['expire_time_in_seconds'] );
        $max_updates_per_request = intval( $wcorrreios_config['config']['max_rate_updates'] );
        $nCdEmpresa = '';
        $sDsSenha = '';

        if( $wcorrreios_config['config'][ 'enterprise_id'] != '' && $wcorrreios_config['config'][ 'enterprise_pass' ] != '' ){
            $nCdEmpresa = $wcorrreios_config['config'][ 'enterprise_id'];
            $sDsSenha = $wcorrreios_config['config'][ 'enterprise_pass'];
        }

        $nVlComprimento = $nCdEmpresa = $wcorrreios_config['config'][ 'minimum_length'];
        $nVlLargura = $nCdEmpresa = $wcorrreios_config['config'][ 'minimum_width'];
        $nVlAltura = $nCdEmpresa = $wcorrreios_config['config'][ 'minimum_height'];


        $query_rates = "SELECT id,servico,nome,regiao,prazo,peso,valor,cep_origem,cep_destino_ini,cep_destino_fim,lastupdate,cep_dest_ref
                from " . $wpdb->prefix . "wcorreios_webservice_frete
                WHERE (lastupdate IS NULL OR UNIX_TIMESTAMP( lastupdate )  < " . ( mktime() - $expire_time_in_seconds) . ")
                Limit 0," . $max_updates_per_request;

        $rates = $wpdb->get_results( $query_rates );

        wp_mail( 'ricardohaas@msn.com', 'Count Rates wcorreios_webservice_rates_update' . count( $rates ), $query_rates );
        if( count( $rates ) > 0 ){
            foreach( $rates as $rate ){
                $nCdServico = $rate->servico;
                $weight = $rate->peso;
                $cep_origem = $rate->cep_origem;
                $cep_destino = $rate->cep_dest_ref;
                $url_d = $this->correios_ws_url."&nCdEmpresa=".$nCdEmpresa."&sDsSenha=".$sDsSenha."&nCdFormato=1&nCdServico=".$nCdServico."&nVlComprimento=".$nVlComprimento."&nVlAltura=".$nVlAltura."&nVlLargura=".$nVlLargura."&sCepOrigem=".$cep_origem."&sCdMaoPropria=N&sCdAvisoRecebimento=N&nVlValorDeclarado=0&nVlPeso=".$weight."&sCepDestino=".$cep_destino;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url_d);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                ob_start();
                curl_exec($ch);
                curl_close($ch);
                $content = ob_get_contents();
                ob_end_clean();

                @$xml = new SimpleXMLElement( $content );
                if( $xml ){
                    foreach( $xml->cServico as $servico ){
                        if ( in_array( $servico->Erro, array( '0', '010' ) ) ) {
                            $update_rate_query = "UPDATE " . $wpdb->prefix . "wcorreios_webservice_frete SET
                                 valor='" . str_replace( "," , "." , $servico->Valor ) . "',
                                 prazo='" . $servico->PrazoEntrega . "',
                                 lastupdate=NOW()
                                WHERE id='" . $rate->id . "'
                            ";
                            $wpdb->query( $update_rate_query );
                        }
                    }
                }
            }
        }
    }

    public function update_wcorreios_webservices_enabled( $option_name , $old_values, $new_values ){

        if( $option_name != 'wcorreios_webservice_services' ){
            return;
        }

        $services = $this->get_services();

        if( count( $new_values ) > 0 ){

            foreach( $new_values as $key => $value ){

                if( $value == 'enable' && ( !isset( $old_values[ $key ] ) || $old_values[ $key ] == 'disable' ) ){
                    $this->add_service_rates( $key );
                }

                if( $value == 'disable' && $old_values[ $key ] == 'enable' ){
                    $this->delete_service_rates( $key );
                }

            }
        }
    }

    public function add_cron_five_minutes_interval_support( $schedules ){
        $one_minute = 60;
        $schedules[ '5_minutes' ] = array(
            'interval' => $one_minute ,//* 5
            'display'  => __( 'A cada 5 minutos', 'wcorreios_webservice' ),
            );

        return $schedules;
    }

    public function delete_service_rates( $key ){
        global $wpdb;
        $rate_parts = explode( '_' , $key );
        $service_id = intval( end( $rate_parts ) );
        $delete_rates_query = "DELETE from " . $wpdb->prefix . "wcorreios_webservice_frete
             WHERE servico like '" . $service_id . "'
        ";
        $wpdb->query( $delete_rates_query );
        return;
    }

    public function add_service_rates( $key ){
        global $wpdb;

        $rate_parts = explode( '_' , $key );
        $service_id = end( $rate_parts );
        $service_name = $this->get_service_title( $service_id );

        $this->delete_service_rates( $key );
        $first_rate = "SELECT servico FROM " . $wpdb->prefix . "wcorreios_webservice_frete_model Limit 0,1";
        $first_row = $wpdb->get_row( $first_rate );

        if( !$first_row ){
            return;
        }

        $base_service_id = $first_row->servico;
        $query_servico_rates = "SELECT id,servico,nome,regiao,prazo,peso,valor,cep_origem,cep_destino_ini,cep_destino_fim,lastupdate,cep_dest_ref from " . $wpdb->prefix . "wcorreios_webservice_frete_model";
        $query_servico_rates .= " WHERE servico like " . $base_service_id;
        $base_rates = $wpdb->get_results( $query_servico_rates );

        $query_insert = 'INSERT INTO ' . $wpdb->prefix .'wcorreios_webservice_frete (servico,peso,valor,prazo,nome,regiao,cep_origem,cep_destino_ini,cep_destino_fim,lastupdate,cep_dest_ref) VALUES ';
        $data_insert = array();
        foreach( $base_rates as $base_rate ){
            if( !$service_name ){
                $base_rate->nome;
            }
            $data_insert[] =  sprintf( "('%s',%s,'%s','%s','%s','%s','%s','%s','%s',%s,'%s')" , $service_id, $base_rate->peso, $base_rate->valor, $base_rate->prazo, $service_name, $base_rate->regiao, $base_rate->cep_origem, $base_rate->cep_destino_ini, $base_rate->cep_destino_fim, 'Null', $base_rate->cep_dest_ref );
        }

        if( count( $data_insert ) == 0 ){
            return;
        }

        $query_insert .= implode( ',' , $data_insert );
        $wpdb->query( $query_insert );
    }

    public function get_service_title( $service_id ){
        $args = array(
            'post_type' => 'wcorreios_service',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'service_id',
                    'value' => $service_id,
                    'compare' => '='
                    )
                )
            );

        $posts = get_posts( $args );

        if( !$posts ){
            return null;
        }

        $post = array_shift( $posts );

        return $post->post_title;
    }


}
