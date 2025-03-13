<?php
/*
Template Name: Liberte Template
Description: Um template responsivo e cross-browser para exibir várias categorias.
*/

get_header();
?>
<style>
/* Reset and base styles */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

main {
 margin-top: 60px;
  display: grid;            
  justify-items: center;    
  background-color: #000;
  width: 100%;
  min-height: 100vh;
  padding: 2rem 1rem;
}

/* Typography */
h2 {
  color: #fff;                            
  font-size: clamp(1.25rem, 2vw, 1.5rem);
  font-weight: 700;                       
  margin-bottom: 1.5rem;                  
  position: relative;                     
  text-align: center;                     
  text-transform: uppercase;              
  letter-spacing: 0.05em;                  
  padding-bottom: 1rem;                   
  -webkit-transition: color 0.3s ease;
  transition: color 0.3s ease;             
}

h2:hover {
  color: #FF1493;                         
}

h2::after {
  content: '';                                 
  width: 80px;                                 
  height: 4px;                                
  background: -webkit-linear-gradient(90deg, #7A1C16 0%, #53100C 50%, #170201 100%);
  background: linear-gradient(90deg, #7A1C16 0%, #53100C 50%, #170201 100%);
  position: absolute;                          
  bottom: 0;                                   
  left: 50%;                                   
  -webkit-transform: translateX(-50%);
  transform: translateX(-50%);                 
  border-radius: 2px;                          
  -webkit-transition: width 0.3s ease;
  transition: width 0.3s ease;                  
  -webkit-box-shadow: 0 2px 4px rgba(255, 20, 147, 0.2);
  box-shadow: 0 2px 4px rgba(255, 20, 147, 0.2);
}

h2:hover::after {
  width: 120px;  
}

.audio {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: start;
  -ms-flex-pack: start;
  justify-content: flex-start;
  -webkit-box-align: start;
  -ms-flex-align: start;
  align-items: flex-start;
  line-height: 1.6;
  margin-bottom: 1rem;
  padding: 1.25rem;
  background: -webkit-linear-gradient(145deg, #232323, #1a1a1a);
  background: linear-gradient(145deg, #232323, #1a1a1a);
  width: min(80%, 800px);
  border-radius: 12px;
  -webkit-box-shadow: 0 4px 6px rgba(0,0,0,0.1), 0 1px 3px rgba(0,0,0,0.08);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1), 0 1px 3px rgba(0,0,0,0.08);
  -webkit-transition: -webkit-transform 0.2s ease, -webkit-box-shadow 0.2s ease;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: 1px solid rgba(255,255,255,0.05);
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
}

.audio-title {
  -webkit-box-flex: 1;
  -ms-flex: 1;
  flex: 1;
  min-width: 0;
  font-weight: 600;
  color: #fff;
  font-size: 1.1rem;
  letter-spacing: 0.02em;
  word-wrap: break-word;
  padding-right: 1rem;
  position: relative;
  text-align: left;
}

.favorite-btn {
  -ms-flex-negative: 0;
  flex-shrink: 0;
  -ms-flex-item-align: center;
  align-self: center;
}

/* Black November Section */
.black-november-section {
  background: -webkit-linear-gradient(135deg, #080808 0%, black 100%);
  background: linear-gradient(180deg, #171717 0%, black 100%);
  color: #fff;
  padding: 3rem 1.5rem;
  border-radius: 0 0 20px 20px;
  -webkit-box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  position: relative;
  overflow: hidden;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.black-november-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
background: -webkit-linear-gradient(90deg, #1c1c1c, #808080, #1c1c1c);
background: linear-gradient(90deg, #1c1c1c, #808080, #1c1c1c);

  -webkit-animation: shimmer 2s infinite linear;
  animation: shimmer 2s infinite linear;
  background-size: 200% 100%;
}

.black-november-title {
  font-size: clamp(1.8rem, 4vw, 2.5rem);
  font-weight: 800;
  margin-bottom: 1.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  -webkit-animation: fadeInDown 0.8s ease-out;
  animation: fadeInDown 0.8s ease-out;
  text-align: center;
}

.black-november-description {
  font-size: clamp(1.1rem, 2vw, 1.6rem);
  line-height: 1.5;
  margin-bottom: 2rem;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
  -webkit-animation: fadeInUp 0.8s ease-out 0.2s backwards;
  animation: fadeInUp 0.8s ease-out 0.2s backwards;
  text-align: center;
}

.price-highlight {
  color: #ff0000;
  font-weight: 700;
  font-size: clamp(1.4rem, 3vw, 1.8rem);
  text-shadow: 0 0 10px rgba(255, 20, 147, 0.5);
  display: inline-block;
  position: relative;
}

.countdown-timer {
  font-size: clamp(1.6rem, 3vw, 2.5rem);
  font-weight: 700;
  margin: 2rem auto;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 12px;
  display: inline-block;
  min-width: min(300px, 90%);
  -webkit-animation: pulse 2s infinite;
  animation: pulse 2s infinite;
  text-align: center;
}

.offer-button {
  display: inline-block;
  padding: 1rem 2.5rem;
  font-size: clamp(1.2rem, 2vw, 1.4rem);
  font-weight: 600;
  text-decoration: none;
  color: #fff;
  background: -webkit-linear-gradient(45deg, #7A1C16, #53100C);
  background: linear-gradient(45deg, #7A1C16, #53100C);
  border-radius: 50px;
  -webkit-transition: all 0.3s ease;
  transition: all 0.3s ease;
  border: none;
  -webkit-box-shadow: 0 4px 15px rgba(122, 28, 22, 0.4);
  box-shadow: 0 4px 15px rgba(122, 28, 22, 0.4);
  position: relative;
  overflow: hidden;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.offer-button:hover, .offer-button:focus {
  -webkit-transform: translateY(-2px);
  transform: translateY(-2px);
  -webkit-box-shadow: 0 6px 20px rgba(122, 28, 22, 0.6);
  box-shadow: 0 6px 20px rgba(122, 28, 22, 0.6);
  background: -webkit-linear-gradient(45deg, #53100C, #7A1C16);
  background: linear-gradient(45deg, #53100C, #7A1C16);
  outline: none;
}

.offer-button:active {
  -webkit-transform: translateY(0);
  transform: translateY(0);
  -webkit-box-shadow: 0 2px 10px rgba(122, 28, 22, 0.4);
  box-shadow: 0 2px 10px rgba(122, 28, 22, 0.4);
}

/* Animations */
@-webkit-keyframes shimmer {
  0% { background-position: -200% center; }
  100% { background-position: 200% center; }
}

@keyframes shimmer {
  0% { background-position: -200% center; }
  100% { background-position: 200% center; }
}

@-webkit-keyframes fadeInDown {
  from {
    opacity: 0;
    -webkit-transform: translateY(-20px);
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    -webkit-transform: translateY(0);
    transform: translateY(0);
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    -webkit-transform: translateY(-20px);
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    -webkit-transform: translateY(0);
    transform: translateY(0);
  }
}

@-webkit-keyframes fadeInUp {
  from {
    opacity: 0;
    -webkit-transform: translateY(20px);
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    -webkit-transform: translateY(0);
    transform: translateY(0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    -webkit-transform: translateY(20px);
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    -webkit-transform: translateY(0);
    transform: translateY(0);
  }
}

@-webkit-keyframes pulse {
  0% { -webkit-transform: scale(1); transform: scale(1); }
  50% { -webkit-transform: scale(1.02); transform: scale(1.02); }
  100% { -webkit-transform: scale(1); transform: scale(1); }
}

@keyframes pulse {
  0% { -webkit-transform: scale(1); transform: scale(1); }
  50% { -webkit-transform: scale(1.02); transform: scale(1.02); }
  100% { -webkit-transform: scale(1); transform: scale(1); }
}

/* Additional Media Queries for better responsiveness */
@supports (padding: max(0px)) {
  .black-november-section {
    padding-left: max(1.5rem, env(safe-area-inset-left));
    padding-right: max(1.5rem, env(safe-area-inset-right));
  }
}

@media (hover: hover) {
  .offer-button:hover {
    transform: translateY(-2px);
  }
}

@media (prefers-reduced-motion: reduce) {
  * {
    animation: none !important;
    transition: none !important;
  }
}
</style>

<main>
 
Olá,
  <?php exibir_secao_favoritos(); ?>

  <?php
if (!is_user_logged_in()) {
    exibir_arquivos_noveri(false, 8);
}
  
  exibir_arquivos_audio_por_taxonomia();
     
  ?>

  <?php exibir_audio_player(); ?>

  
</main>

<?php get_footer(); ?>