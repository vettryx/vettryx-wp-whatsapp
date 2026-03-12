<?php
/**
 * Plugin Name: VETTRYX WP WhatsApp Widget
 * Plugin URI:  https://github.com/vettryx/vettryx-wp-core
 * Description: Botão flutuante nativo e ultraleve do WhatsApp, focado em conversão e performance.
 * Version:     1.1.0
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
            'bg_color' => sanitize_hex_color( $input['bg_color'] ?? '#25d366' ) ?: '#25d366',
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
        $bg_color = !empty($data['bg_color']) ? esc_attr($data['bg_color']) : '#25d366';
        
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
                background-color: <?php echo $bg_color; ?>;
                border-radius: 50px;
                box-shadow: 2px 2px 8px rgba(0,0,0,0.15);
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: transform 0.3s ease, filter 0.3s ease;
            }
            .vettryx-wa-widget:hover {
                transform: scale(1.05);
                filter: brightness(0.9); /* Escurece a cor atual em 10% no hover */
            }
            .vettryx-wa-widget svg {
                width: 35px;
                height: 35px;
                fill: #FFFFFF !important; /* Força a cor branca no ícone */
            }
            <?php echo $display_css; ?>
        </style>

        <a href="<?php echo esc_url($whatsapp_url); ?>" class="vettryx-wa-widget" target="_blank" rel="noopener noreferrer" aria-label="Fale conosco no WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
        <?php
    }

    // Renderiza a página de configurações no painel administrativo
    public function render_admin_page() {
        $data = get_option( $this->option_name, [ 'phone' => '', 'message' => '', 'position' => 'right', 'hide_on' => 'none', 'bg_color' => '#25d366' ] );
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
                    <tr>
                        <th scope="row"><label for="bg_color">Cor de Fundo do Botão</label></th>
                        <td>
                            <input type="color" name="<?php echo esc_attr( $this->option_name ); ?>[bg_color]" id="bg_color" value="<?php echo esc_attr( $data['bg_color'] ); ?>" style="padding: 0; border: none; width: 50px; height: 32px; cursor: pointer; border-radius: 4px;">
                            <p class="description">Padrão do WhatsApp: <strong>#25d366</strong>. Altere para combinar com a identidade da agência, se desejar.</p>
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
