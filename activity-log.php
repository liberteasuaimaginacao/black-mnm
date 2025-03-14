<?php
// Criar tabela de log quando necessário
function criar_tabela_log() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_activity_log';
    
    // Verifica se a tabela já existe
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            activity_type VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
criar_tabela_log();

// Função para registrar atividades
function log_activity($activity_type, $description) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_activity_log';
    
    $user_id = get_current_user_id();
    
    $wpdb->insert($table_name, [
        'user_id' => $user_id ? $user_id : 0,
        'activity_type' => $activity_type,
        'description' => $description,
        'timestamp' => current_time('mysql')
    ]);
}

// Registrar visualização de página
function log_page_view() {
    if (is_singular() || is_page()) {
        $post_id = get_queried_object_id();
        $post_title = get_the_title($post_id);
        $user = wp_get_current_user();
        $username = $user->ID ? $user->user_login : 'Visitante';
        
        if ($post_id && $post_title) {
            log_activity('page_view', "Visualização de '$post_title' (ID: $post_id) por $username");
        }
    }
}
add_action('template_redirect', 'log_page_view');

// Endpoint para registrar atividades do player via fetch
function register_activity_endpoint() {
    add_action('rest_api_init', function () {
        register_rest_route('activity-logger/v1', '/log', array(
            'methods' => 'POST',
            'callback' => 'handle_activity_log',
            'permission_callback' => '__return_true'
        ));
    });
}
add_action('init', 'register_activity_endpoint');

// Handler para o endpoint de log
function handle_activity_log($request) {
    $params = $request->get_params();
    
    if (!isset($params['activity_type']) || !isset($params['description'])) {
        return new WP_Error('missing_params', 'Parâmetros obrigatórios não fornecidos', array('status' => 400));
    }
    
    log_activity(
        sanitize_text_field($params['activity_type']),
        sanitize_text_field($params['description'])
    );
    
    return new WP_REST_Response(['status' => 'success'], 200);
}

// Adicionar script para logging no front-end
function add_logging_script() {
    ?>
    <script>
    // Função para registrar atividades via fetch
    async function logActivity(activity_type, description) {
        try {
            const response = await fetch('/wp-json/activity-logger/v1/log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    activity_type: activity_type,
                    description: description
                })
            });
            
            if (!response.ok) {
                console.error('Erro ao registrar atividade:', response.statusText);
            }
        } catch (error) {
            console.error('Erro ao enviar log:', error);
        }
    }

    // Adicionar eventos ao player
    document.addEventListener('DOMContentLoaded', function() {
        const audio = document.getElementById('audio');
        const audioTitle = document.getElementById('audio-title');
        const progressContainer = document.getElementById('progress-container');
        let lastLoggedTime = 0;

        // Log de início de reprodução
        function logPlayStart(title) {
            logActivity('play_audio', `Iniciou a reprodução: ${title}`);
        }

        // Log de pausa
        audio?.addEventListener('pause', () => {
            if (audioTitle?.textContent) {
                logActivity('pause_audio', `Pausou: ${audioTitle.textContent}`);
            }
        });

        // Log de fim de reprodução
        audio?.addEventListener('ended', () => {
            if (audioTitle?.textContent) {
                logActivity('complete_audio', `Completou: ${audioTitle.textContent}`);
            }
        });

        // Log de progresso
        audio?.addEventListener('timeupdate', () => {
            if (!audio.duration) return;
            
            const currentMinute = Math.floor(audio.currentTime / 60);
            if (currentMinute > lastLoggedTime) {
                lastLoggedTime = currentMinute;
                if (audioTitle?.textContent) {
                    logActivity('playback_progress', 
                        `${currentMinute} minutos de reprodução em: ${audioTitle.textContent}`
                    );
                }
            }
        });

        // Log de busca na timeline
        progressContainer?.addEventListener('click', e => {
            if (!audio?.duration) return;
            
            const newTime = (e.offsetX / progressContainer.clientWidth) * audio.duration;
            const direction = newTime > audio.currentTime ? 'Avançou' : 'Retrocedeu';
            
            if (audioTitle?.textContent) {
                logActivity('seek_audio', 
                    `${direction} para ${Math.floor(newTime)}s em: ${audioTitle.textContent}`
                );
            }
        });

        // Sobrescrever função de popup existente
        if (typeof openPopup === 'function') {
            const originalOpenPopup = openPopup;
            window.openPopup = function(title) {
                logActivity('popup_open', `Abriu popup: ${title}`);
                originalOpenPopup(title);
            }
        }

        // Sobrescrever função de verificação de acesso
        if (typeof checkAccessAndPlay === 'function') {
            const originalCheckAccess = checkAccessAndPlay;
            window.checkAccessAndPlay = function(title, url, requiresSubscription, element) {
                if (requiresSubscription) {
                    logActivity('restricted_content', `Tentativa de acesso a conteúdo restrito: ${title}`);
                }
                originalCheckAccess(title, url, requiresSubscription, element);
            }
        }

        // Sobrescrever função playAudio
        if (typeof playAudio === 'function') {
            const originalPlayAudio = playAudio;
            window.playAudio = function(title, url, element) {
                logActivity('play_audio', `Iniciou: ${title}`);
                originalPlayAudio(title, url, element);
            }
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'add_logging_script');

// Página de administração para visualizar logs
function display_activity_logs_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_activity_log';
    
    // Obtém os últimos 100 logs
    $results = $wpdb->get_results(
        "SELECT * FROM $table_name 
         ORDER BY timestamp DESC 
         LIMIT 100"
    );
    
    echo '<div class="wrap">';
    echo '<h1>Logs de Atividade</h1>';
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Data/Hora</th>';
    echo '<th>Usuário</th>';
    echo '<th>Tipo</th>';
    echo '<th>Descrição</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($results as $row) {
        $user = get_userdata($row->user_id);
        $username = $user ? $user->user_login : 'Visitante';
        
        echo '<tr>';
        echo '<td>' . esc_html($row->timestamp) . '</td>';
        echo '<td>' . esc_html($username) . '</td>';
        echo '<td>' . esc_html($row->activity_type) . '</td>';
        echo '<td>' . esc_html($row->description) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Adicionar menu de administração
function add_activity_logs_menu() {
    add_menu_page(
        'Logs de Atividade',
        'Logs de Atividade',
        'manage_options',
        'activity-logs',
        'display_activity_logs_page',
        'dashicons-chart-bar',
        30
    );
}
add_action('admin_menu', 'add_activity_logs_menu');

// Adicionar submenus para análises específicas
function add_analytics_submenus() {
    add_submenu_page(
        'activity-logs',
        'Engajamento de Usuários',
        'Engajamento',
        'manage_options',
        'user-engagement',
        'display_user_engagement_page'
    );
    
    add_submenu_page(
        'activity-logs',
        'Estatísticas de Áudio',
        'Estatísticas de Áudio',
        'manage_options',
        'audio-statistics',
        'display_audio_statistics_page'
    );
}
add_action('admin_menu', 'add_analytics_submenus');

// Página de engajamento de usuários
function display_user_engagement_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_activity_log';
    
    // Obter estatísticas de engajamento dos últimos 30 dias
    $start_date = date('Y-m-d', strtotime('-30 days'));
    
    $user_stats = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            user_id,
            COUNT(*) as total_activities,
            COUNT(CASE WHEN activity_type = 'play_audio' THEN 1 END) as total_plays,
            COUNT(CASE WHEN activity_type = 'complete_audio' THEN 1 END) as completed_audios,
            MAX(timestamp) as last_activity
        FROM $table_name
        WHERE timestamp >= %s
        GROUP BY user_id
        ORDER BY total_activities DESC
        LIMIT 20",
        $start_date
    ));
    
    echo '<div class="wrap">';
    echo '<h1>Engajamento de Usuários (Últimos 30 dias)</h1>';
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th>Usuário</th>';
    echo '<th>Total de Atividades</th>';
    echo '<th>Áudios Iniciados</th>';
    echo '<th>Áudios Completados</th>';
    echo '<th>Taxa de Conclusão</th>';
    echo '<th>Última Atividade</th>';
    echo '</tr></thead><tbody>';
    
    foreach ($user_stats as $stat) {
        $user = get_userdata($stat->user_id);
        $username = $user ? $user->user_login : 'Visitante';
        $completion_rate = $stat->total_plays > 0 
            ? round(($stat->completed_audios / $stat->total_plays) * 100, 1) 
            : 0;
        
        echo '<tr>';
        echo '<td>' . esc_html($username) . '</td>';
        echo '<td>' . esc_html($stat->total_activities) . '</td>';
        echo '<td>' . esc_html($stat->total_plays) . '</td>';
        echo '<td>' . esc_html($stat->completed_audios) . '</td>';
        echo '<td>' . esc_html($completion_rate) . '%</td>';
        echo '<td>' . esc_html(date('d/m/Y H:i', strtotime($stat->last_activity))) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    
    // Gráfico de atividade diária
    $daily_activity = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            DATE(timestamp) as date,
            COUNT(*) as total
        FROM $table_name
        WHERE timestamp >= %s
        GROUP BY DATE(timestamp)
        ORDER BY date ASC",
        $start_date
    ));
    
    echo '<h2>Atividade Diária</h2>';
    echo '<div style="width: 100%; height: 300px;">';
    echo '<canvas id="dailyActivityChart"></canvas>';
    echo '</div>';
    
    // Preparar dados para o gráfico
    $dates = array_map(function($item) {
        return date('d/m', strtotime($item->date));
    }, $daily_activity);
    
    $totals = array_map(function($item) {
        return $item->total;
    }, $daily_activity);
    
    // Adicionar script do Chart.js
    wp_enqueue_script('chartjs', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js');
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('dailyActivityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Atividades Diárias',
                    data: <?php echo json_encode($totals); ?>,
                    borderColor: '#2271b1',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
    </script>
    <?php
    echo '</div>';
}

// Página de estatísticas de áudio
function display_audio_statistics_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_activity_log';
    
    // Obter estatísticas de áudio dos últimos 30 dias
    $start_date = date('Y-m-d', strtotime('-30 days'));
    
    $audio_stats = $wpdb->get_results($wpdb->prepare(
        "SELECT 
            CASE 
                WHEN activity_type = 'play_audio' THEN 
                    CASE 
                        WHEN description LIKE 'Iniciou:%' THEN SUBSTRING_INDEX(description, 'Iniciou: ', -1)
                        WHEN description LIKE 'Iniciou a reprodução:%' THEN SUBSTRING_INDEX(description, 'Iniciou a reprodução: ', -1)
                        ELSE SUBSTRING_INDEX(description, 'Iniciou: ', -1)
                    END
            END as audio_title,
            COUNT(CASE WHEN activity_type = 'play_audio' THEN 1 END) as total_plays,
            COUNT(CASE WHEN activity_type = 'complete_audio' THEN 1 END) as completions,
            COUNT(DISTINCT user_id) as unique_listeners
        FROM $table_name
        WHERE timestamp >= %s
            AND (activity_type = 'play_audio' OR activity_type = 'complete_audio')
            AND description NOT LIKE '%%undefined%%'
            AND description NOT LIKE '%%null%%'
        GROUP BY audio_title
        HAVING audio_title IS NOT NULL
        ORDER BY total_plays DESC
        LIMIT 20",
        $start_date
    ));
    
    echo '<div class="wrap">';
    echo '<h1>Estatísticas de Áudio (Últimos 30 dias)</h1>';
    
    // Adicionar filtro de período
    echo '<div class="tablenav top">';
    echo '<div class="alignleft actions">';
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="audio-statistics">';
    echo '<select name="period">';
    echo '<option value="7" ' . selected(isset($_GET['period']) ? $_GET['period'] : '30', '7', false) . '>Últimos 7 dias</option>';
    echo '<option value="15" ' . selected(isset($_GET['period']) ? $_GET['period'] : '30', '15', false) . '>Últimos 15 dias</option>';
    echo '<option value="30" ' . selected(isset($_GET['period']) ? $_GET['period'] : '30', '30', false) . '>Últimos 30 dias</option>';
    echo '<option value="90" ' . selected(isset($_GET['period']) ? $_GET['period'] : '30', '90', false) . '>Últimos 90 dias</option>';
    echo '</select>';
    echo '<input type="submit" class="button" value="Filtrar">';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th class="column-primary">Título do Áudio</th>';
    echo '<th>Total de Reproduções</th>';
    echo '<th>Conclusões</th>';
    echo '<th>Taxa de Conclusão</th>';
    echo '<th>Ouvintes Únicos</th>';
    echo '</tr></thead><tbody>';
    
    if ($audio_stats) {
        foreach ($audio_stats as $stat) {
            if (empty($stat->audio_title)) continue;
            
            $completion_rate = $stat->total_plays > 0 
                ? round(($stat->completions / $stat->total_plays) * 100, 1) 
                : 0;
            
            echo '<tr>';
            echo '<td class="column-primary">' . esc_html($stat->audio_title) . '</td>';
            echo '<td>' . number_format($stat->total_plays, 0, ',', '.') . '</td>';
            echo '<td>' . number_format($stat->completions, 0, ',', '.') . '</td>';
            echo '<td>' . number_format($completion_rate, 1, ',', '.') . '%</td>';
            echo '<td>' . number_format($stat->unique_listeners, 0, ',', '.') . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">Nenhum dado encontrado para o período selecionado.</td></tr>';
    }
    
    echo '</tbody></table>';
    
    // Apenas mostrar o gráfico se houver dados
    if ($audio_stats && count($audio_stats) > 0) {
        // Gráfico de reproduções vs conclusões
        $top_audios = array_slice($audio_stats, 0, 5); // Pegar top 5 áudios
        
        echo '<h2>Top 5 Áudios - Reproduções vs Conclusões</h2>';
        echo '<div style="width: 100%; height: 400px;">';
        echo '<canvas id="audioComparisonChart"></canvas>';
        echo '</div>';
        
        // Preparar dados para o gráfico
        $titles = array_map(function($item) {
            // Limitar o tamanho do título para melhor visualização
            return strlen($item->audio_title) > 30 ? 
                   substr($item->audio_title, 0, 27) . '...' : 
                   $item->audio_title;
        }, $top_audios);
        
        $plays = array_map(function($item) {
            return $item->total_plays;
        }, $top_audios);
        
        $completions = array_map(function($item) {
            return $item->completions;
        }, $top_audios);
        
        // Adicionar script do Chart.js
        wp_enqueue_script('chartjs', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js');
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('audioComparisonChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($titles); ?>,
                    datasets: [
                        {
                            label: 'Reproduções',
                            data: <?php echo json_encode($plays); ?>,
                            backgroundColor: '#2271b1'
                        },
                        {
                            label: 'Conclusões',
                            data: <?php echo json_encode($completions); ?>,
                            backgroundColor: '#72aee6'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        });
        </script>
        <?php
    }
    
    echo '</div>';
}