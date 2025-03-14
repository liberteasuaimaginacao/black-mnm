<style>
/* CSS do Player */
.volume-icon {
    display: inline-flex;
    align-items: flex-end;
}

.bar {
    transform-origin: bottom;
    animation: crescerDiminuirFixado 0.7s infinite ease-in-out;
}

.bar:nth-child(1) {
    animation-delay: 0s;
}

.bar:nth-child(2) {
    animation-delay: 0.8s;
}

.bar:nth-child(3) {
    animation-delay: 0.9s;
}

.bar:nth-child(4) {
    animation-delay: 0.12s;
}

@keyframes crescerDiminuirFixado {
    0%, 100% { transform: scaleY(0.5); }
    50% { transform: scaleY(1.5); }
}

/* Pop-up */
.popup {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
}

.popup-content {
    background-color: black;
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    position: relative;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 26px;
    color: #ffffff;
    cursor: pointer;
    transition: color 0.2s;
}

.close:hover {
    color: #ff4081;
}

/* Botões do Pop-up */
.popup-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

.popup-button {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    border: none;
}

.signup-button {
    background-color: #722F37;
    color: white;
}

.signup-button:hover {
    background-color: #8B3844;
}

.login-button {
    background-color: transparent;
    color: white;
    border: 2px solid white;
}

.login-button:hover {
    background-color: rgba(255, 255, 255, 0.1);
}
/* Player */
#audio-player {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100vw;
    padding: 12px;
    background: linear-gradient(145deg, #232323, #1a1a1a);
    color: var(--text-primary);
    border-top: 1px solid var(--border-main);
    box-shadow: 0 -1px 8px var(--shadow-color);
    z-index: 1000;
    box-sizing: border-box;
}

.player-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 15px;
}

.left-side {
    flex: 1;
    padding: 0 8px;
}

#audio-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 5px;
    text-align: left;
}

.progress-container {
    width: 100%;
    height: 4px;
    background: #444;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 5px;
}

.progress {
    height: 100%;
    background: white;
    width: 0;
}

.time-container {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-top: 5px;
}

.right-side {
    display: flex;
    align-items: center;
}

.control-btn {
    background: transparent;
    border: none;
    color: var(--text-primary);
    font-size: 22px;
    cursor: pointer;
    padding: 4px;
}
</style>

<!-- Pop-up -->
<div id="popup" class="popup">
    <div class="popup-content">
        <span class="close" onclick="closePopup()">&times;</span>
        <h2 id="popup-title">Conteúdo Exclusivo</h2>
        <p id="popup-message">Quer ouvir mais? Esse áudio é exclusivo para assinantes. Adquira um plano ou faça login.</p>
        <div class="popup-buttons">
            <!-- Botão de Assinar agora sempre aparece -->
            <a href="/planos" class="popup-button signup-button">Assinar agora</a>

            <?php if (is_user_logged_in()): ?>
                <!-- Botão Suporte para usuários logados -->
                <a href="https://wa.me/5511991835094" class="popup-button login-button">Suporte</a>
            <?php else: ?>
                <!-- Botão de login para usuários não logados -->
                <a href="meu-login" class="popup-button login-button">Fazer login</a>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Player -->
<div id="audio-player" class="player">
    <audio id="audio" src=""></audio>
    <div class="player-content">
        <div class="right-side">
            <button id="play" class="control-btn" onclick="togglePlayPause()">
                <img id="playIconMax" src="https://liberteasuaimaginacao.com/wp-content/uploads/play-solid.svg" alt="Play" width="24" height="24">
                <img id="pauseIconMax" src="https://liberteasuaimaginacao.com/wp-content/uploads/pause-solid.svg" alt="Pause" width="24" height="24" style="display: none;">
            </button>
        </div>
        
        <div class="left-side">
            <div id="audio-title"></div>
            <div class="progress-container" id="progress-container">
                <div class="progress" id="progress" style="width: 0;"></div>
            </div>
            <div class="time-container">
                <span id="current-time">0:00</span>
                <span id="duration"> / 0:00</span>
            </div>
        </div>
    </div>
</div>


<script async>
// Seletores do Player
const playIconMax = document.getElementById('playIconMax');
const pauseIconMax = document.getElementById('pauseIconMax');
const audio = document.getElementById('audio');
const audioTitle = document.getElementById('audio-title');
const audioPlayer = document.getElementById('audio-player');
const playButton = document.getElementById('play');
const progressContainer = document.getElementById('progress-container');
const progress = document.getElementById('progress');
const currentTimeEl = document.getElementById('current-time');
const durationEl = document.getElementById('duration');

// Seletores do Pop-up
const popup = document.getElementById('popup');
const popupTitle = document.getElementById('popup-title');

// Variável para rastrear a div do áudio ativo
let activeAudioElement = null;

// URL da capa para a Media Session API
const coverImageUrl = 'https://app.liberteasuaimaginacao.com/wp-content/uploads/2024/11/Liberte-a-sua-Imaginacao.jpg';

// Funções do Player
function playAudio(title, url, element) {
    // Se outro áudio está ativo, pause e remova a classe 'active'
    if (activeAudioElement && activeAudioElement !== element) {
        // Pausa o áudio atual
        audio.pause();
        audio.currentTime = 0;

        // Esconde o ícone de volume da div anterior
        const previousIcon = activeAudioElement.querySelector('.play-icon');
        if (previousIcon) {
            previousIcon.style.display = 'none';
        }

        // Remove a classe 'active' da div anterior
        activeAudioElement.classList.remove('active');
    }

    // Se a mesma div está sendo clicada novamente
    if (activeAudioElement === element) {
        if (audio.paused) {
            audio.play();
            showVolumeIcon(element);
        } else {
            audio.pause();
            hideVolumeIcon(element);
        }
        updatePlayPauseButton();
        return;
    }

    // Atualiza o áudio com o novo título e URL
    audio.src = url;
    audioTitle.textContent = title;
    audio.load();
    audio.play();
    audioPlayer.style.display = 'block';

    // Marca a nova div como ativa
    element.classList.add('active');
    activeAudioElement = element;

    // Exibe o ícone de volume na nova div
    showVolumeIcon(element);

    // Atualiza os botões de play/pause
    updatePlayPauseButton();

    // Atualiza os metadados da Media Session API
    updateMediaSessionMetadata(title);
}

function updatePlayPauseButton() {
    playIconMax.style.display = audio.paused ? 'block' : 'none';
    pauseIconMax.style.display = audio.paused ? 'none' : 'block';
}

// Função para exibir os ícones de volume na div ativa
function showVolumeIcon(element) {
    const playIcons = element.querySelectorAll('.play-icon');
    playIcons.forEach(icon => {
        icon.style.display = 'inline-flex';  // Exibe o ícone de volume apenas nesta div
    });
}

// Função para esconder os ícones de volume na div ativa
function hideVolumeIcon(element) {
    const playIcons = element.querySelectorAll('.play-icon');
    playIcons.forEach(icon => {
        icon.style.display = 'none';  // Esconde o ícone de volume apenas nesta div
    });
}

function togglePlayPause() {
    if (!activeAudioElement) return; // Se não há áudio ativo, não faz nada

    if (audio.paused) {
        audio.play();
        showVolumeIcon(activeAudioElement);
    } else {
        audio.pause();
        hideVolumeIcon(activeAudioElement);
    }
    updatePlayPauseButton();
}

// Função para atualizar os metadados da Media Session API
function updateMediaSessionMetadata(title) {
    if ('mediaSession' in navigator) {
        navigator.mediaSession.metadata = new MediaMetadata({
            title: title,
            artist: 'Liberte a sua Imaginação', // Substitua pelo nome do artista, se aplicável
            artwork: [
                { src: coverImageUrl,   sizes: '512x512', type: 'image/jpeg' },
                { src: coverImageUrl,   sizes: '192x192', type: 'image/jpeg' },
                { src: coverImageUrl,   sizes: '128x128', type: 'image/jpeg' },
            ]
        });

        // Opcional: Gerenciar ações de mídia (play/pause) na central de multimídia
        navigator.mediaSession.setActionHandler('play', () => {
            audio.play();
            showVolumeIcon(activeAudioElement);
        });

        navigator.mediaSession.setActionHandler('pause', () => {
            audio.pause();
            hideVolumeIcon(activeAudioElement);
        });

        navigator.mediaSession.setActionHandler('seekbackward', (details) => {
            audio.currentTime = Math.max(audio.currentTime - (details.seekOffset || 10), 0);
        });

        navigator.mediaSession.setActionHandler('seekforward', (details) => {
            audio.currentTime = Math.min(audio.currentTime + (details.seekOffset || 10), audio.duration);
        });

        navigator.mediaSession.setActionHandler('stop', () => {
            audio.pause();
            audio.currentTime = 0;
            hideVolumeIcon(activeAudioElement);
        });
    }
}

// Eventos do Player
audio.addEventListener('play', () => {
    updatePlayPauseButton();
    if (activeAudioElement) {
        showVolumeIcon(activeAudioElement);
    }
});

audio.addEventListener('pause', () => {
    updatePlayPauseButton();
    if (activeAudioElement) {
        hideVolumeIcon(activeAudioElement);
    }
});

audio.addEventListener('ended', () => {
    audioPlayer.style.display = 'none';
    updatePlayPauseButton();

    // Remove a classe 'active' da div do áudio
    if (activeAudioElement) {
        hideVolumeIcon(activeAudioElement);
        activeAudioElement.classList.remove('active');
        activeAudioElement = null;
    }
});

audio.addEventListener('timeupdate', () => {
    if (audio.duration) {
        const percent = (audio.currentTime / audio.duration) * 100;
        progress.style.width = `${percent}%`;
    } else {
        progress.style.width = `0%`;
    }

    const formatTime = time => `${Math.floor(time / 60)}:${String(Math.floor(time % 60)).padStart(2, '0')}`;
    currentTimeEl.textContent = formatTime(audio.currentTime);
    durationEl.textContent = audio.duration ? ` / ${formatTime(audio.duration)}` : ' / 0:00';
});

progressContainer.addEventListener('click', e => {
    if (!audio.duration) return;

    const newTime = (e.offsetX / progressContainer.clientWidth) * audio.duration;
    const direction = newTime > audio.currentTime ? 'Avançou' : 'Retrocedeu';
    audio.currentTime = newTime;
});

// Funções do Pop-up
function openPopup(title) {
    popupTitle.textContent = title;
    popup.style.display = 'flex';
}

function closePopup() {
    popup.style.display = 'none';
}

popup.addEventListener('click', e => {
    if (e.target === popup) closePopup();
});


function checkAccessAndPlay(title, url, requiresSubscription, element) {
    if (requiresSubscription) {
        openPopup('Conteúdo Exclusivo');
    } else {
        playAudio(title, url, element); // Passa o elemento correto
    }
}
</script>