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
        add_action( 'init' , array( $this, 'show_wcorreios_options_config' ) );
	}

    public function myplugin_activate(){
        add_action( 'admin_notices', 'myplugin_activate_result' );
    }

    public function myplugin_activate_result(){
        echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios Webservice depends to %s to work!', 'wcorreios_webservice' ), '<a href="https://github.com/wpbrasil/odin">inicialização</a>' ) . '</p></div>';
    }

    public function show_wcorreios_options_config(){

        $tema_opcoes = new Odin_Theme_Options(
            'wcorreios_webservice', // Slug/ID da página (Obrigatório)
            __( 'Configuração WebService Correios', 'wcorreios_webservice' ), // Titulo da página (Obrigatório
            'read'
        );

        $tema_opcoes->set_tabs(
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

        $tema_opcoes->set_fields(
            array(
                'wcorreios_webservice_config' => array(
                    'tab'   => 'wcorreios_webservice_config', // Sessão da aba odin_general
                    'title' => __( 'Configuração', 'wcorreios_webservice' ),
                    'fields' => array(
                        array(
                            'id' => 'expire_time_in_seconds',
                            'label' => __( 'Atualizar registros a cada', 'wcorreios_webservice' ),
                            'type' => 'text',
                            'description' => __( 'Tempo em segundos' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimun_width',
                            'label' => __( 'Largura Mínima', 'wcorreios_webservice' ),
                            'type' => 'text',
                            'description' => __( 'cm' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimun_height',
                            'label' => __( 'Altura Mínima', 'wcorreios_webservice' ),
                            'type' => 'text',
                            'description' => __( 'cm' , 'wcorreios_webservice' )
                        ),
                        array(
                            'id' => 'minimum_length',
                            'label' => __( 'Comprimento Mínimo', 'wcorreios_webservice' ),
                            'type' => 'text',
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
                    'fields' => array(
                        array(
                            'id' => 'from_zip',
                            'label' => __( 'Cep Origem', 'wcorreios_webservice' ),
                            'type' => 'textarea',
                            'description' => __( 'Um cep por linha' , 'wcorreios_webservice' )
                        ),
                    )
                ),
            )

        );
    }
}
new wcorreios_webservice;
