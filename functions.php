<?php
// Registrar o menu do tema
function meu_tema_basico_setup() {
    add_theme_support('title-tag'); // Suporte ao título dinâmico do WordPress
    add_theme_support('post-thumbnails'); // Suporte para imagens destacadas
    register_nav_menus(array(
        'primary' => __('Menu Principal', 'meu-tema-basico')
    ));
}
add_action('after_setup_theme', 'meu_tema_basico_setup');











/// Evita o acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Redireciona tentativas de acesso ao login para a página personalizada /meu-login
 * e restringe o acesso ao wp-admin para usuários não administradores.
 */
function custom_redirecionamentos() {
    $current_uri = $_SERVER['REQUEST_URI'];

// Redireciona tentativas de acessar wp-login.php para /meu-login, exceto para páginas de redefinição de senha ou alteração de senha
if ( ! is_user_logged_in() && strpos( $current_uri, 'wp-login.php' ) !== false ) {
    // Verifica se não é a página de redefinição de senha (action=lostpassword) ou de redefinição de senha com key (action=rp)
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'lostpassword' ) {
        // Permite o acesso à página de redefinição de senha (perda de senha)
        // A URL padrão de perda de senha é tratada normalmente
        return;
    }
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'rp' ) {
        // Se for a página de redefinição de senha, redireciona para a página personalizada
        wp_safe_redirect( home_url( '/redefinir-senha/?key=' . $_GET['key'] . '&login=' . $_GET['login'] ) );
        exit;
    }
    
    // Verifica se não é a página de redefinição de senha (com 'lostpassword' ou 'rp') ou de redefinição com key
    if ( !( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) ) {
        wp_safe_redirect( home_url( '/meu-login' ) );
        exit;
    }
}



    // Restringe acesso ao wp-admin para usuários não administradores
    if ( is_admin() && ! is_user_logged_in() ) {
        wp_safe_redirect( home_url( '/meu-login' ) );
        exit;
    } elseif ( is_admin() && ! current_user_can( 'administrator' ) ) {
        wp_safe_redirect( home_url() );
        exit;
    }
}
add_action( 'init', 'custom_redirecionamentos' );


// Função para registrar a taxonomia "Estilo" com hierarquia para posts e mídia
function criar_taxonomia_estilo() {
    $labels = array(
        'name'                       => 'Estilos',
        'singular_name'              => 'Estilo',
        'search_items'               => 'Procurar Estilos',
        'all_items'                  => 'Todos os Estilos',
        'parent_item'                => 'Estilo Pai',
        'parent_item_colon'          => 'Estilo Pai:',
        'edit_item'                  => 'Editar Estilo',
        'update_item'                => 'Atualizar Estilo',
        'add_new_item'               => 'Adicionar Novo Estilo',
        'new_item_name'              => 'Novo Nome de Estilo',
        'menu_name'                  => 'Estilos',
        'view_item'                  => 'Ver Estilo',
        'popular_items'              => 'Estilos Populares',
        'separate_items_with_commas' => 'Separe os estilos com vírgulas',
        'add_or_remove_items'        => 'Adicionar ou Remover Estilos',
        'choose_from_most_used'      => 'Escolher entre os mais usados',
        'not_found'                  => 'Nenhum estilo encontrado',
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'estilo'),
        'show_in_rest'      => true,
    );

    // Registrar a taxonomia "Estilo" para os tipos de post 'post' e 'attachment' (mídia)
    register_taxonomy('estilo', array('post', 'attachment'), $args);
}
add_action('init', 'criar_taxonomia_estilo');
// Adiciona uma nova coluna "Estilo" na Biblioteca de Mídia
function adicionar_coluna_estilo_midia($columns) {
    $columns['estilo'] = 'Estilo';
    return $columns;
}
add_filter('manage_media_columns', 'adicionar_coluna_estilo_midia');

// Exibe os termos de "Estilo" na nova coluna para cada item de mídia
function mostrar_estilo_coluna_midia($column_name, $post_id) {
    if ($column_name == 'estilo') {
        // Recupera os termos de "Estilo" atribuídos ao item atual
        $current_terms = wp_get_post_terms($post_id, 'estilo', array('fields' => 'ids'));

        // Recupera todos os termos da taxonomia "Estilo"
        $all_terms = get_terms(array(
            'taxonomy' => 'estilo',
            'hide_empty' => false,
        ));

        if (!empty($all_terms)) {
            echo '<select class="estilo-select" data-post-id="' . esc_attr($post_id) . '">';
            echo '<option value="">Selecione um estilo</option>';

            foreach ($all_terms as $term) {
                $selected = in_array($term->term_id, $current_terms) ? 'selected' : '';
                echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
            }

            echo '</select>';
        } else {
            echo '<em>Nenhum estilo disponível</em>';
        }
    }
}
add_action('manage_media_custom_column', 'mostrar_estilo_coluna_midia', 10, 2);

// Enfileirar o script de AJAX para atualizar a taxonomia "Estilo"
function estilo_midia_ajax_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.estilo-select').on('change', function () {
                var post_id = $(this).data('post-id');
                var estilo_id = $(this).val();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'atualizar_estilo_midia',
                        post_id: post_id,
                        estilo_id: estilo_id
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Estilo atualizado com sucesso!');
                        } else {
                            alert('Erro ao atualizar o estilo.');
                        }
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'estilo_midia_ajax_script');

// Manipulador AJAX para atualizar o termo "Estilo" do item de mídia
function atualizar_estilo_midia() {
    if (!empty($_POST['post_id']) && isset($_POST['estilo_id'])) {
        $post_id = intval($_POST['post_id']);
        $estilo_id = intval($_POST['estilo_id']);

        if ($estilo_id) {
            // Define o termo de "Estilo" para o item de mídia
            wp_set_object_terms($post_id, array($estilo_id), 'estilo');
        } else {
            // Remove o termo de "Estilo" caso nenhum seja selecionado
            wp_set_object_terms($post_id, array(), 'estilo');
        }

        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_atualizar_estilo_midia', 'atualizar_estilo_midia');

// Adicionar ação em massa na Biblioteca de Mídia
function adicionar_acao_massa_estilo($bulk_actions) {
    $bulk_actions['atribuir_estilo'] = 'Atribuir Estilo';
    return $bulk_actions;
}
add_filter('bulk_actions-upload', 'adicionar_acao_massa_estilo');

// Adiciona o seletor de estilo e o botão de "Atribuir Estilo" na Biblioteca de Mídia
function adicionar_acao_massa_estilo_personalizada() {
    $screen = get_current_screen();
    if ($screen->id != 'upload') return;

    $terms = get_terms(array(
        'taxonomy' => 'estilo',
        'hide_empty' => false,
    ));

    echo '<div style="display: inline-block; margin-left: 10px;">';
    echo '<select name="bulk_estilo" id="bulk_estilo">';
    echo '<option value="">Nenhum Estilo</option>'; // Opção para remover estilos

    if (!empty($terms)) {
        foreach ($terms as $term) {
            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
        }
    }

    echo '</select>';
    echo '<button type="button" class="button" id="atribuir_estilo">Atribuir Estilo</button>';
    echo '</div>';
}
add_action('restrict_manage_posts', 'adicionar_acao_massa_estilo_personalizada');

// Manipulador AJAX para aplicar o termo "Estilo" em massa
function aplicar_estilo_em_massa() {
    if (!empty($_POST['media_ids'])) {
        $media_ids = array_map('intval', $_POST['media_ids']);
        $estilo_id = isset($_POST['estilo_id']) ? intval($_POST['estilo_id']) : 0;

        foreach ($media_ids as $media_id) {
            if ($estilo_id) {
                wp_set_object_terms($media_id, array($estilo_id), 'estilo', false);
            } else {
                wp_set_object_terms($media_id, array(), 'estilo'); // Remove estilos se "Nenhum Estilo" for selecionado
            }
        }

        wp_send_json_success();
    } else {
        wp_send_json_error('Erro ao aplicar o estilo.');
    }
}
add_action('wp_ajax_aplicar_estilo_em_massa', 'aplicar_estilo_em_massa');

// Script AJAX para a ação em massa com botão personalizado, sem mensagem de confirmação
function estilo_massa_ajax_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#atribuir_estilo').on('click', function () {
                var estilo_id = $('#bulk_estilo').val();

                // Obter IDs dos itens de mídia selecionados
                var media_ids = [];
                $('input[name="media[]"]:checked').each(function () {
                    media_ids.push($(this).val());
                });

                if (media_ids.length === 0) {
                    alert('Por favor, selecione pelo menos um item de mídia.');
                    ;
                }

                // Enviar requisição AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aplicar_estilo_em_massa',
                        media_ids: media_ids,
                        estilo_id: estilo_id
                    },
                    success: function () {
                        location.reload(); // Atualiza a página para refletir as mudanças na coluna
                    }
                });
            });
        });
    </script>
    <?php
}
add_action('admin_footer-upload.php', 'estilo_massa_ajax_script');



// Exibir posts com áudio por taxonomia
function exibir_posts_com_audio($exibir_todas_taxonomias = true, $custom_taxonomia_id = null) {
    $taxonomias_padrao = [
        'Recentes' => 27,
        'M. Guida' => 23,
        'Eróticos' => 25,
        'Dominação' => 3,
        'Gatilhos' => 24,
        'Mais áudios' => 80,
        'Rapidinhos' => 26,
    ];

    $taxonomias = $exibir_todas_taxonomias ? $taxonomias_padrao : [$custom_taxonomia_id];

    foreach ($taxonomias as $titulo => $id) {
        if (!$exibir_todas_taxonomias && $id !== $custom_taxonomia_id) continue;

        $query_args = [
            'post_type' => 'post',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'estilo',
                    'field' => 'term_id',
                    'terms' => $id,
                ]
            ]
        ];

        $query = new WP_Query($query_args);

        // Exibir título da taxonomia
        echo '<div class="taxonomy-title">' . esc_html($titulo) . '</div>';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $audio_url = '';

                if (preg_match('/https?:\/\/[^\s]+\.mp3/', get_the_content(), $matches)) {
                    $audio_url = $matches[0];
                }

                if (empty($audio_url)) {
                    $tracks = get_post_meta(get_the_ID(), 'srmp3_elementor_tracks', true);
                    if (!empty($tracks)) {
                        $tracks_data = maybe_unserialize($tracks);
                        if (is_array($tracks_data) && isset($tracks_data[0]['feed_source_file']['url'])) {
                            $audio_url = $tracks_data[0]['feed_source_file']['url'];
                        }
                    }
                }

                if (!empty($audio_url)) {
                    echo '<div class="audio">'; // Início da div com classe audio
                    echo '<div class="card-audio" onclick="playAudio(\'' . esc_js(get_the_title()) . '\', \'' . esc_url($audio_url) . '\')">';
                    echo '<div class="audio-title">' . esc_html(get_the_title()) . '</div>';
                    echo '</div>';
                    echo '</div>'; // Fim da div com classe audio
                }
            }
            wp_reset_postdata();
        } else {
            echo '<p>Nenhum post encontrado nesta categoria.</p>';
        }
    }
}




// Permitir upload de arquivos SVG
function permitir_upload_svg($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'permitir_upload_svg');

// Segurança adicional para SVG
function verificar_svg_upload($file) {
    if ($file['type'] === 'image/svg+xml') {
        if (preg_match('/<svg[^>]*>.*<\/svg>/s', file_get_contents($file['tmp_name']))) {
            return $file;
        } else {
            $file['error'] = 'O arquivo SVG não contém um SVG válido.';
            return $file;
        }
    }
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'verificar_svg_upload');

// Ocultar barra de administração para usuários não-admin
function ocultar_barra_admin_para_nao_admins() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'ocultar_barra_admin_para_nao_admins');


// Incluir arquivo de exibição de posts por taxonomia
require get_stylesheet_directory() . '/includes/exibicao-de-audios.php';

// Função para exibir o player de áudio
function exibir_audio_player() {
    include get_stylesheet_directory() . '/includes/audio-player.php';
}

/*
function add_fixed_footer_menu() {
    ?>
    <footer id="fixed-footer" class="fixed-footer">
        <div class="footer-container">
            <div class="menu-items">
                <div class="menu-item">
                    <a href="<?php echo esc_url( home_url( '/play' ) ); ?>">
                        <img src="https://app.liberteasuaimaginacao.com/wp-content/uploads/2024/11/fone-1.svg" alt="Ouvir agora">
                        Ouvir agora
                    </a>
                </div>
                <div class="menu-item">
                    <a href="<?php echo esc_url( home_url( '/minha-conta' ) ); ?>">
                        <img src="https://app.liberteasuaimaginacao.com/wp-content/uploads/2024/11/user.svg" alt="Conta" class="icon-white">
                        Conta
                    </a>
                </div>
            </div>
        </div>
    </footer> 

    <style>
        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #000;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.5);
            z-index: 999;
            padding: 10px 0;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .menu-items {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu-item {
            margin: 0 15px;
            text-align: center;
        }
        .menu-item a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .menu-item img {
            width: 24px;
            height: 24px;
            margin-bottom: 5px;
        }
        /* Define o ícone da conta como branco 
        .menu-item .icon-white {
            filter: brightness(0) invert(1);
        }

        @media (max-width: 768px) {
            .menu-item {
                margin: 0 10px;
            }
            .menu-item a {
                font-size: 12px;
            }
            .menu-item img {
                width: 20px;
                height: 20px;
            }
        }
    </style>
    <?php
}
add_action('wp_footer', 'add_fixed_footer_menu');
*/




/* To disable the Gutenberg’s CSS loading on the front-end  */
function tw_unload_files() {
 
        wp_dequeue_style ( 'wp-block-library' );
        wp_dequeue_style ( 'wp-block-library-theme' );
 
 }
 add_action( 'wp_enqueue_scripts', 'tw_unload_files', 100 );

/* To disable Elementor’s Google font  */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/*To disable Elementor’s Font Awesome */
add_action('elementor/frontend/after_register_styles',function() { 
 
        foreach( [ 'solid', 'regular', 'brands' ] as $style ) {
                wp_deregister_style( 'elementor-icons-fa-' . $style );
        }
 
}, 20 );

/*To disable Elementor’s icons (eicons) */
function ti_unload_files() {
 
        if ( is_admin() || current_user_can( 'manage_options' ) ) {
                return;
        }
 
        wp_deregister_style( 'elementor-icons' ); 
 
 }
 add_action( 'wp_enqueue_scripts', 'ti_unload_files', 100 );

/*To fix “Image elements do not have explicit width and height” warning from CWV / PSI */

add_filter( 'the_content', 'add_image_dimensions' );

function add_image_dimensions( $content ) {

    preg_match_all( '/<img[^>]+>/i', $content, $images);

    if (count($images) < 1)
        return $content;

    foreach ($images[0] as $image) {
        preg_match_all( '/(alt|title|src|width|class|id|height)=("[^"]*")/i', $image, $img );

        if ( !in_array( 'src', $img[1] ) )
            continue;

        if ( !in_array( 'width', $img[1] ) || !in_array( 'height', $img[1] ) ) {
            $src = $img[2][ array_search('src', $img[1]) ];
            $alt = in_array( 'alt', $img[1] ) ? ' alt=' . $img[2][ array_search('alt', $img[1]) ] : '';
            $title = in_array( 'title', $img[1] ) ? ' title=' . $img[2][ array_search('title', $img[1]) ] : '';
            $class = in_array( 'class', $img[1] ) ? ' class=' . $img[2][ array_search('class', $img[1]) ] : '';
            $id = in_array( 'id', $img[1] ) ? ' id=' . $img[2][ array_search('id', $img[1]) ] : '';
            list( $width, $height, $type, $attr ) = getimagesize( str_replace( "\"", "" , $src ) );

            $image_tag = sprintf( '<img src=%s%s%s%s%s width="%d" height="%d" />', $src, $alt, $title, $class, $id, $width, $height );
            $content = str_replace($image, $image_tag, $content);
        }
    }

    return $content;
}

/**
 * Disable jQuery migrate
 */

function dequeue_jquery_migrate( $scripts ) {
    if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
}
add_action( 'wp_default_scripts', 'dequeue_jquery_migrate' );

/**
 * Disable the emoji's
 */
function disable_emojis() {
 remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
 remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
 remove_action( 'wp_print_styles', 'print_emoji_styles' );
 remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
 remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
 remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
 remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
 add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
 add_filter( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param array $plugins 
 * @return array Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
 if ( is_array( $plugins ) ) {
 return array_diff( $plugins, array( 'wpemoji' ) );
 } else {
 return array();
 }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
 if ( 'dns-prefetch' == $relation_type ) {
 /** This filter is documented in wp-includes/formatting.php */
 $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

$urls = array_diff( $urls, array( $emoji_svg_url ) );
 }

return $urls;
}

/**
 * Disable XML-RPC
 */

add_filter( 'xmlrpc_enabled', '__return_false' );

function remover_css_desnecessario() {
    // Verifique os identificadores dos estilos e desregistre-os
    wp_dequeue_style('frontend-base-css'); // Substitua pelo identificador correto, se disponível
    wp_dequeue_style('frontend-variation-1-css'); // Substitua pelo identificador correto
    wp_dequeue_style('select2-css'); // Substitua pelo identificador correto
}

add_action('wp_enqueue_scripts', 'remover_css_desnecessario', 100);

// Inclui o código do plano customizado Hotmart no tema
include_once get_template_directory() . '/includes/minimalistmember.php';

// Inclui o código do plano customizado Hotmart no tema
include_once get_template_directory() . '/includes/hotmart-custom-plan.php';



// Caminho relativo ao diretório do tema
require_once get_template_directory() . '/includes/activity-log.php';




function custom_login_styles() {
    // Verifica se a página atual é a de recuperação de senha
    if ( isset($_GET['action']) && $_GET['action'] == 'lostpassword' ) {
        // Adiciona o arquivo CSS personalizado
        wp_enqueue_style('custom-login-style', get_stylesheet_directory_uri() . '/style.css');
    }
}
add_action('login_enqueue_scripts', 'custom_login_styles');

require_once get_template_directory() . '/includes/favorites.php';







function liberte_get_audio_data( $request ) {
    global $wpdb;

    $post_name = sanitize_text_field( $request['slug'] );

    // Consulta ao banco de dados incluindo status 'publish' e 'inherit'
    $query = $wpdb->prepare(
        "SELECT post_title, guid FROM {$wpdb->prefix}posts 
         WHERE post_name = %s 
         AND (post_status = 'publish' OR post_status = 'inherit') 
         LIMIT 1",
        $post_name
    );
    
    $result = $wpdb->get_row( $query );

    if ( ! $result ) {
        return new WP_Error( 'no_audio', 'Nenhum áudio encontrado com esse nome.', [ 'status' => 404 ] );
    }

    return rest_ensure_response( [
        'title' => $result->post_title,
        'guid'  => $result->guid
    ] );
}

function liberte_register_rest_route() {
    register_rest_route( 'liberte/v1', '/audio/(?P<slug>[a-zA-Z0-9-_]+)', [
        'methods'  => 'GET',
        'callback' => 'liberte_get_audio_data',
        'permission_callback' => '__return_true',
    ] );
}

add_action( 'rest_api_init', 'liberte_register_rest_route' );