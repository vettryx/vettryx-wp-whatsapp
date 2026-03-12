<?php
/**
 * Plugin Name: VETTRYX WP WhatsApp Widget
 * Plugin URI:  https://github.com/vettryx/vettryx-wp-whatsapp
 * Description: Botão flutuante nativo e ultraleve do WhatsApp, focado em conversão e performance.
 * Version:     1.0.0
 * Author:      VETTRYX Tech
 * Author URI:  https://vettryx.com.br
 * License:     GPLv3
 */

// Segurança: Impede o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Evita conflitos de classe
class Vettryx_WhatsApp_Widget {

    // Nome da opção no banco de dados
    private $option_name = 'vettryx_whatsapp_data';

    // Construtor: Define hooks para admin e front-end
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
            add_action( 'admin_init', [ $this, 'register_settings' ] );
        } else {
            add_action( 'wp_footer', [ $this, 'inject_whatsapp_button' ], 99 );
        }
    }

    // Adiciona a página de configurações no menu do VETTRYX Core
    public function add_submenu_page() {
        add_submenu_page(
            'vettryx-core-modules',
            'WhatsApp Widget',
            'WhatsApp Widget',
            'manage_options',
            'vettryx-whatsapp-widget',
            [ $this, 'render_admin_page' ]
        );
    }

    // Registra as configurações
    public function register_settings() {
        register_setting( 'vettryx_whatsapp_group', $this->option_name, [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_data' ]
        ] );
    }

    /**
     * Sanitiza os dados de entrada
     */
    public function sanitize_data( $input ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return get_option( $this->option_name );
        }

        return [
            'phone'    => sanitize_text_field( trim( $input['phone'] ?? '' ) ),
            'message'  => sanitize_textarea_field( trim( $input['message'] ?? '' ) ),
            'position' => in_array( $input['position'] ?? 'right', ['right', 'left'] ) ? $input['position'] : 'right',
            'hide_on'  => in_array( $input['hide_on'] ?? 'none', ['none', 'mobile', 'desktop'] ) ? $input['hide_on'] : 'none',
        ];
    }

    /**
     * Injeta o CSS e HTML do botão no footer
     */
    public function inject_whatsapp_button() {
        $data = get_option( $this->option_name, [] );
        
        // Se não tiver telefone configurado, não exibe nada
        if ( empty( $data['phone'] ) ) {
            return;
        }

        // Limpa o número de telefone (deixa apenas números para a API wa.me)
        $clean_phone = preg_replace('/[^0-9]/', '', $data['phone']);
        
        // Codifica a mensagem pré-definida
        $encoded_message = !empty($data['message']) ? urlencode($data['message']) : '';
        
        // Monta a URL oficial da API
        $whatsapp_url = "https://wa.me/{$clean_phone}" . ($encoded_message ? "?text={$encoded_message}" : "");

        // Configurações visuais
        $position = $data['position'] ?? 'right';
        $hide_on = $data['hide_on'] ?? 'none';
        
        $position_css = ($position === 'left') ? 'left: 20px;' : 'right: 20px;';

        // Lógica de CSS Media Queries para ocultar em dispositivos
        $display_css = '';
        if ( $hide_on === 'mobile' ) {
            $display_css = '@media (max-width: 768px) { .vettryx-wa-widget { display: none !important; } }';
        } elseif ( $hide_on === 'desktop' ) {
            $display_css = '@media (min-width: 769px) { .vettryx-wa-widget { display: none !important; } }';
        }

        // Renderiza o estilo inline e o botão
        ?>
        <style>
            .vettryx-wa-widget {
                position: fixed;
                bottom: 20px;
                <?php echo $position_css; ?>
                width: 60px;
                height: 60px;
                background-color: #25d366;
                color: #FFF;
                border-radius: 50px;
                text-align: center;
                font-size: 30px;
                box-shadow: 2px 2px 8px rgba(0,0,0,0.15);
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: transform 0.3s ease, background-color 0.3s ease;
            }
            .vettryx-wa-widget:hover {
                background-color: #128c7e;
                transform: scale(1.05);
                color: #FFF;
            }
            .vettryx-wa-widget svg {
                width: 35px;
                height: 35px;
                fill: currentColor;
            }
            <?php echo $display_css; ?>
        </style>

        <a href="<?php echo esc_url($whatsapp_url); ?>" class="vettryx-wa-widget" target="_blank" rel="noopener noreferrer" aria-label="Fale conosco no WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zM223.9 414.8c-32 0-63.3-8.6-90.8-24.8l-6.5-3.8-67.4 17.7 18-65.7-4.2-6.7C55 301.9 44.5 264.4 44.5 223.9c0-98.8 80.4-179.3 179.4-179.3 47.9 0 92.9 18.7 126.8 52.6 33.9 33.9 52.6 78.9 52.6 126.8 0 98.9-80.4 179.3-179.4 179.3h-.1zm98.1-134.4c-5.4-2.7-31.9-15.8-36.9-17.6-5-1.8-8.6-2.7-12.2 2.7-3.6 5.4-14 17.6-17.2 21.2-3.2 3.6-6.4 4.1-11.8 1.4-5.4-2.7-22.8-8.4-43.4-26.8-16-14.3-26.8-31.9-30-37.3-3.2-5.4-.3-8.3 2.4-11 2.4-2.4 5.4-6.3 8.1-9.5 2.7-3.2 3.6-5.4 5.4-9 1.8-3.6.9-6.8-.5-9.5-1.4-2.7-12.2-29.4-16.7-40.3-4.4-10.7-8.9-9.3-12.2-9.5-3.2-.2-6.8-.2-10.4-.2-3.6 0-9.5 1.4-14.5 6.8-5 5.4-19 18.5-19 45.1s19.5 52.2 22.2 55.8c2.7 3.6 38.1 58.2 92.3 81.5 12.9 5.5 22.9 8.9 30.7 11.4 13 4.1 24.8 3.5 34.2 2.1 10.6-1.6 31.9-13 36.4-25.6 4.5-12.6 4.5-23.4 3.2-25.6-1.3-2.3-5-3.6-10.4-6.3z"/>
            </svg>
        </a>
        <?php
    }

    // Renderiza a página de configurações no painel administrativo
    public function render_admin_page() {
        $data = get_option( $this->option_name, [ 'phone' => '', 'message' => '', 'position' => 'right', 'hide_on' => 'none' ] );
        ?>
        <div class="wrap">
            <h1><?php _e( 'VETTRYX Tech - WhatsApp Widget', 'vettryx-wp-core' ); ?></h1>
            <p><?php _e( 'Configure o botão flutuante nativo do WhatsApp. Esta solução é otimizada para não prejudicar o PageSpeed do site.', 'vettryx-wp-core' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'vettryx_whatsapp_group' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="phone">Número de Telefone (com DDI)</label></th>
                        <td>
                            <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[phone]" id="phone" value="<?php echo esc_attr( $data['phone'] ); ?>" class="regular-text" placeholder="Ex: 5531999999999">
                            <p class="description">Inclua o código do país (DDI) e o DDD. Apenas números.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="message">Mensagem Pré-definida</label></th>
                        <td>
                            <textarea name="<?php echo esc_attr( $this->option_name ); ?>[message]" id="message" rows="3" class="large-text" placeholder="Olá! Gostaria de mais informações."><?php echo esc_textarea( $data['message'] ); ?></textarea>
                            <p class="description">Texto que aparecerá pronto na caixa de digitação do cliente.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="position">Posição no Ecrã</label></th>
                        <td>
                            <select name="<?php echo esc_attr( $this->option_name ); ?>[position]" id="position">
                                <option value="right" <?php selected( $data['position'], 'right' ); ?>>Canto Inferior Direito</option>
                                <option value="left" <?php selected( $data['position'], 'left' ); ?>>Canto Inferior Esquerdo</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hide_on">Ocultar Botão</label></th>
                        <td>
                            <select name="<?php echo esc_attr( $this->option_name ); ?>[hide_on]" id="hide_on">
                                <option value="none" <?php selected( $data['hide_on'], 'none' ); ?>>Mostrar em todos os dispositivos</option>
                                <option value="mobile" <?php selected( $data['hide_on'], 'mobile' ); ?>>Ocultar em Celulares (Mobile)</option>
                                <option value="desktop" <?php selected( $data['hide_on'], 'desktop' ); ?>>Ocultar em Computadores (Desktop)</option>
                            </select>
                            <p class="description">Útil se você já tiver outro botão focado em mobile ou desktop.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button( 'Salvar Configurações' ); ?>
            </form>
        </div>
        <?php
    }
}

// Inicializa o plugin
new Vettryx_WhatsApp_Widget();
