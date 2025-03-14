<?php
/*
Template Name: Player de Áudio
*/

get_header(); 

$slug = isset($_GET['audio']) ? sanitize_text_field($_GET['audio']) : '';

if (empty($slug)) {
    wp_die('Áudio não especificado.', 'Erro', ['response' => 404]);
}

$api_url = esc_url_raw(get_site_url() . '/wp-json/liberte/v1/audio/' . $slug);
$response = wp_remote_get($api_url);

if (is_wp_error($response)) {
    wp_die('Erro ao buscar o áudio: ' . $response->get_error_message(), 'Erro', ['response' => 500]);
}

$audio_data = json_decode(wp_remote_retrieve_body($response), true);

if (!is_array($audio_data) || isset($audio_data['code']) && $audio_data['code'] === 'no_audio') {
    wp_die('Áudio não encontrado.', 'Erro', ['response' => 404]);
}

$title = esc_html($audio_data['title']);
$audio_url = esc_url_raw($audio_data['guid']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .audio-player-container {
            max-width: 400px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .player-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .seek-bar {
            width: 100%;
        }
        .player-controls {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .control-btn, .play-btn {
            font-size: 20px;
            cursor: pointer;
            background: none;
            border: none;
        }
    </style>
</head>
<body>

<div class="audio-player-container">
    <div class="player">
        <div class="player-header">
            <button class="favorite-btn" aria-label="Favoritar">
                <i class="fas fa-heart"></i>
            </button>
            <h2 class="audio-title"><?php echo $title; ?></h2>
            <button class="share-btn" aria-label="Compartilhar">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>

        <audio id="audio" src="<?php echo $audio_url; ?>"></audio>

        <div class="progress-container">
            <input type="range" id="seekBar" value="0" class="seek-bar" aria-label="Progresso do áudio">
            <div class="time-info">
                <span id="currentTime">00:00</span>
                <span id="duration">00:00</span>
            </div>
        </div>

        <div class="player-controls">
            <button id="rewind" class="control-btn" aria-label="Retroceder 15 segundos">
                <i class="fas fa-undo-alt"></i> 15
            </button>
            <button id="playPause" class="play-btn" aria-label="Reproduzir">
                <i class="fas fa-play"></i>
            </button>
            <button id="forward" class="control-btn" aria-label="Avançar 15 segundos">
                <i class="fas fa-redo-alt"></i> 15
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const audio = document.getElementById("audio");
        const playPauseBtn = document.getElementById("playPause");
        const seekBar = document.getElementById("seekBar");
        const currentTimeElem = document.getElementById("currentTime");
        const durationElem = document.getElementById("duration");
        const rewindBtn = document.getElementById("rewind");
        const forwardBtn = document.getElementById("forward");

        playPauseBtn.addEventListener("click", function () {
            if (audio.paused) {
                audio.play();
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                audio.pause();
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        });

        audio.addEventListener("loadedmetadata", function () {
            durationElem.textContent = formatTime(audio.duration);
        });

        audio.addEventListener("timeupdate", function () {
            seekBar.value = (audio.currentTime / audio.duration) * 100;
            currentTimeElem.textContent = formatTime(audio.currentTime);
        });

        seekBar.addEventListener("input", function () {
            audio.currentTime = (seekBar.value / 100) * audio.duration;
        });

        rewindBtn.addEventListener("click", function () {
            audio.currentTime -= 15;
        });

        forwardBtn.addEventListener("click", function () {
            audio.currentTime += 15;
        });

        function formatTime(seconds) {
            const min = Math.floor(seconds / 60);
            const sec = Math.floor(seconds % 60);
            return `${min}:${sec < 10 ? '0' : ''}${sec}`;
        }
    });
</script>

</body>
</html>

<?php get_footer(); ?>