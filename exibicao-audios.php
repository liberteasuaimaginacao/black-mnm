<?php
// Função para exibir arquivos de mídia de áudio por taxonomia
function exibir_arquivos_audio_por_taxonomia($exibir_todas_taxonomias = true, $custom_taxonomia_id = null) {
    // Definição das taxonomias padrão
    $taxonomias_padrao = [
        'Eróticos' => 4,
        'Dominação' => 3,
        'Gatilhos' => 7,
        'M. Guiada' => 5,
        'Mais' => 6,
    ];

    // Define as taxonomias a serem exibidas
    $taxonomias = $exibir_todas_taxonomias ? $taxonomias_padrao : [$custom_taxonomia_id];

    // Loop através das taxonomias selecionadas
    foreach ($taxonomias as $id) {
        // Verifica se o ID existe no array
        $titulo = array_search($id, $taxonomias_padrao);

        // Se o título não existir ou for vazio, continue
        if ($titulo === false || empty($titulo)) {
            continue;
        }

        // Exibe o título da taxonomia em uma div acima dos áudios
        echo '<div class="taxonomia-title"><h2>' . esc_html($titulo) . '</h2></div>';

        // Argumentos da query para buscar os áudios
        $query_args = [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'inherit',
            'post_mime_type' => 'audio/mpeg',
            'tax_query' => [
                [
                    'taxonomy' => 'estilo',
                    'field' => 'term_id',
                    'terms' => $id,
                ],
            ],
        ];

        // Executa a query
        $query = new WP_Query($query_args);

        // Verifica se existem posts para a taxonomia atual
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $audio_id = get_the_ID();
                $titulo_audio = get_the_title();
                $audio_url = wp_get_attachment_url($audio_id);

                // Verifica se o usuário não tem acesso ao plano
                if (!current_user_has_access()) {
                    echo '<div class="audio" id="repro" style="cursor: pointer;" onclick="openPopup(\'' . esc_js($titulo_audio) . '\')">';
                    echo '<div class="audio-title" style="display: inline-flex; align-items: center; margin: 0;">';
                    echo esc_html($titulo_audio);
                    echo '</div>';
                    echo '</div>';
                } else {
                    // Se o usuário tiver acesso ao plano, exibe o áudio normalmente
                    if (!empty($audio_url)) {
                        echo '<div class="audio" id="repro" onclick="playAudio(\'' . esc_js($titulo_audio) . '\', \'' . esc_url($audio_url) . '\', this)" style="cursor: pointer;">'; 
                        echo '<div class="audio-title" style="display: inline-flex; align-items: center; margin: 0;">';
                        echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="play-icon" style="display:none; width: 12px; height: 12px; margin-right: 5px;">';
                        echo '<rect class="bar" x="2" y="15" width="3" height="8" fill="currentColor"/>';
                        echo '<rect class="bar" x="8" y="15" width="3" height="8" fill="currentColor"/>';
                        echo '<rect class="bar" x="14" y="15" width="3" height="8" fill="currentColor"/>';
                        echo '<rect class="bar" x="20" y="15" width="3" height="8" fill="currentColor"/>';
                        echo '</svg>';
                        echo esc_html($titulo_audio);
                        echo '</div>';
                        
                        // Adiciona o botão de favoritos
                        if (is_user_logged_in()) {
                            echo get_audio_favorite_button($audio_id);
                        }
                        
                        echo '</div>';
                    }
                }
            }
            wp_reset_postdata();
        } else {
            echo '<p>Nenhum áudio encontrado nesta categoria.</p>';
        }
    }
}

// Função para exibir arquivos de mídia de áudio por taxonomia sem verificação 
function exibir_arquivos_noveri($exibir_todas_taxonomias = true, $custom_taxonomia_id = null) {
    $taxonomias_padrao = [
        'Eróticos' => 4,
        'Dominação' => 3,
        'Gatilhos' => 7,
        'M. Guiada' => 5,
        'Mais' => 6,
        'livres' => 8,
    ];

    $taxonomias = $exibir_todas_taxonomias ? $taxonomias_padrao : [$custom_taxonomia_id];

    foreach ($taxonomias as $id) {
        $titulo = array_search($id, $taxonomias_padrao);

        if ($titulo === false || empty($titulo)) {
            continue;
        }

        echo '<div class="taxonomia-title"><h2>' . esc_html($titulo) . '</h2></div>';

        $query_args = [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'inherit',
            'post_mime_type' => 'audio/mpeg',
            'tax_query' => [
                [
                    'taxonomy' => 'estilo',
                    'field' => 'term_id',
                    'terms' => $id,
                ],
            ],
        ];

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $audio_id = get_the_ID();
                $titulo_audio = get_the_title();
                $audio_url = wp_get_attachment_url($audio_id);

                if (!empty($audio_url)) {
                    echo '<div class="audio" id="repro" onclick="playAudio(\'' . esc_js($titulo_audio) . '\', \'' . esc_url($audio_url) . '\', this)" style="cursor: pointer;">'; 
                    echo '<div class="audio-title" style="display: inline-flex; align-items: center; margin: 0;">';
                    echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="play-icon" style="display:none; width: 12px; height: 12px; margin-right: 5px;">';
                    echo '<rect class="bar" x="2" y="15" width="3" height="8" fill="currentColor"/>';
                    echo '<rect class="bar" x="8" y="15" width="3" height="8" fill="currentColor"/>';
                    echo '<rect class="bar" x="14" y="15" width="3" height="8" fill="currentColor"/>';
                    echo '<rect class="bar" x="20" y="15" width="3" height="8" fill="currentColor"/>';
                    echo '</svg>';
                    echo esc_html($titulo_audio);
                    echo '</div>';
                    
                    // Adiciona o botão de favoritos
                    if (is_user_logged_in()) {
                        echo get_audio_favorite_button($audio_id);
                    }
                    
                    echo '</div>';
                }
            }
            wp_reset_postdata();
        } else {
            echo '<p>Nenhum áudio encontrado nesta categoria.</p>';
        }
    }
}

// Função para exibir a seção de favoritos
function exibir_secao_favoritos() {
    if (is_user_logged_in()) {
        echo get_audio_favorites_section();
    }
}
?>