<?php
/**
 * wcorreios_webservice class.
 */
class wcorreios_webservice {

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
        register_activation_hook( __FILE__, array( $this, 'myplugin_activate' ) );
        $this->create_post_type_service();
        $this->create_metabox_service();
        add_action( 'init' , array( $this, 'show_wcorreios_options_config' ) );
	}

    public function myplugin_activate(){
        add_action( 'admin_notices', 'myplugin_activate_result' );
    }

    public function myplugin_activate_result(){
        echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios Webservice depends to %s to work!', 'wcorreios_webservice' ), '<a href="https://github.com/wpbrasil/odin">inicialização</a>' ) . '</p></div>';
    }

    public function show_wcorreios_options_config(){

        $services = $this->get_services();

        $services_options = null;
        foreach( $services as $id => $service ){
            $services_options[] = array(
                            'id' => 'wcorreios_option_' . $id,
                            'label' => sprintf( '%s (%s)' , $service, $id),
                            'type' => 'checkbox',
                        );
        }

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
                    'title' => __( 'Ceps de Origem', 'wcorreios_webservice' ) // Título da aba.
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
                            'id' => 'minimun_width',
                            'label' => __( 'Largura Mínima', 'wcorreios_webservice' ),
                            'type' => 'input',
                            'attributes'  => array( // Optional (html input elements)
                                'type' => 'number',
                                'min'  => 11
                            ),
                            'description' => __( 'cm' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimun_height',
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
                    'title' => __( 'Atualização', 'wcorreios_webservice' ),
                    'fields' => array(
                        array(
                            'id' => 'from_zip',
                            'label' => __( 'Cep Origem', 'wcorreios_webservice' ),
                            'type' => 'textarea',
                            'description' => __( 'Um cep por linha' , 'wcorreios_webservice' )
                        ),
                    )
                ),
                'wcorreios_webservice_from' => array(
                    'tab'   => 'wcorreios_webservice_from', // Sessão da aba odin_general
                    'title' => __( 'Atualizaçãos', 'wcorreios_webservice' ),
                    'fields' => array(
                        array(
                            'id' => 'from_zip',
                            'label' => __( 'Cep Origem', 'wcorreios_webservice' ),
                            'type' => 'textarea',
                            'description' => __( 'Um cep por linha' , 'wcorreios_webservice' )
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
}
new wcorreios_webservice;
