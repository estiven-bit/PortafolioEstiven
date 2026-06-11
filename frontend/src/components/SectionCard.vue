<template>
  <div class="content-overlay" :class="{ 'center-layout': activeSection === 'projects' }">
    <transition name="fade-slide" mode="out-in">
      <!-- SecciÃƒÂ³n HOME -->
      <div 
        v-if="activeSection === 'home'" 
        key="home" 
        class="card glassmorphic home-layout"
        @mousemove="handleTilt"
        @mouseleave="resetTilt"
      >
        <span class="eyebrow">INICIO</span>
        <h1>Hola, soy Estiven.</h1>
        <h2 class="hero-subtitle">Desarrollador Web Full-Stack Junior enfocado en crear soluciones robustas, eficientes y escalables.</h2>
        <div class="cta-container">
          <button @click="$emit('navigate', 'projects')" class="btn btn-primary">
            Ver mis proyectos
            <span class="arrow">&#8594;</span>
          </button>
          <button @click="$emit('navigate', 'about')" class="btn btn-secondary">
            Saber m&aacute;s
          </button>
        </div>
      </div>

      <!-- SecciÃƒÂ³n ABOUT -->
      <div 
        v-else-if="activeSection === 'about'" 
        key="about" 
        class="card glassmorphic"
        @mousemove="handleTilt"
        @mouseleave="resetTilt"
      >
        <span class="eyebrow">SOBRE M&Iacute;</span>
        <h2>EL DESARROLLADOR</h2>
        <div class="about-grid">
          <div class="bio">
            <p>
              Tengo 30 a&ntilde;os y he dado un giro completo a mi trayectoria profesional, transitando del sector de la hosteler&iacute;a, donde desarroll&eacute; una gran capacidad de trabajo en equipo y resoluci&oacute;n de problemas bajo presi&oacute;n, al mundo del desarrollo de software, aportando esa misma disciplina al c&oacute;digo.
            </p>
            <p>
              Actualmente estoy finalizando el Grado Superior en Desarrollo de Aplicaciones Web (DAW). Me apasiona la l&oacute;gica del desarrollo backend, la gesti&oacute;n eficiente del c&oacute;digo y la integraci&oacute;n de soluciones interactivas premium.
            </p>
          </div>
          <div class="skills">
            <h3>Habilidades</h3>
            <div class="skill-tags">
              <!-- Fila 1 -->
              <div class="skill-row">
                <span class="tag">HTML5</span>
                <span class="tag">CSS3</span>
                <span class="tag">JavaScript (ES6+)</span>
                <span class="tag">Vue.js 3</span>
              </div>
              <!-- Fila 2 -->
              <div class="skill-row">
                <span class="tag">PHP (API / MVC)</span>
                <span class="tag">Java</span>
                <span class="tag">SQL / MySQL</span>
                <span class="tag">Git / GitHub</span>
              </div>
              <!-- Fila 3 -->
              <div class="skill-row">
                <span class="tag">IA (Certificaci&oacute;n IBM / Coursera)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección PROJECTS -->
      <div 
        v-else-if="activeSection === 'projects'" 
        key="projects" 
        class="card glassmorphic projects-layout"
        @mousemove="handleTilt"
        @mouseleave="resetTilt"
      >
        <span class="eyebrow">PORTAFOLIO</span>
        <h2>MIS PROYECTOS</h2>
        <p class="projects-intro">Una peque&ntilde;a muestra de los desarrollos interactivos y plataformas web que he construido.</p>
        
        <transition name="projects-fade" mode="out-in">
          <div class="projects-grid" :key="showProjectDetail ? 'detail' : 'list'">
            <template v-if="!showProjectDetail">
              <button
                v-for="project in projects"
                :key="project.key"
                type="button"
                class="project-item glassmorphic-sub project-selector"
                @click="selectProject(project)"
                @mousemove="handleTilt"
                @mouseleave="resetTilt"
              >
                <div class="project-header">
                  <div class="status-badge" :class="project.statusClass">
                    <span class="status-dot"></span>
                    <span class="status-text">{{ project.status }}</span>
                  </div>
                  <span class="project-select-hint">Ver resumen</span>
                </div>
                <div class="project-info">
                  <h4>{{ project.title }}</h4>
                  <p>{{ project.shortDescription }}</p>
                  <div class="project-tech">
                    <span v-for="tech in project.tech" :key="tech">{{ tech }}</span>
                  </div>
                </div>
              </button>
            </template>

            <template v-else>
              <div class="project-detail-grid">
                <div class="project-detail-card glassmorphic-sub project-detail-summary">
                  <button
                    type="button"
                    class="project-back-btn"
                    @click="showProjectDetail = false"
                    aria-label="Volver a proyectos"
                    title="Volver a proyectos"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="back-icon">
                      <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                  </button>
                  <span class="detail-label">Resumen</span>
                  <h4>{{ selectedProject.title }}</h4>
                  <p>{{ selectedProject.longDescription }}</p>
                  <div class="project-switcher">
                    <button
                      v-for="project in projects"
                      :key="project.key"
                      type="button"
                      class="project-switch-btn"
                      :class="{ active: selectedProject.key === project.key }"
                      @click="selectProject(project)"
                    >
                      {{ project.title }}
                    </button>
                  </div>
                </div>

                <div class="project-detail-card glassmorphic-sub project-detail-cta">
                  <span class="detail-label">Acceso directo</span>
                  <h4>Ir a la web</h4>
                  <p>Abre la versión publicada del proyecto seleccionado.</p>
                  <a :href="selectedProject.url" target="_blank" rel="noopener noreferrer" class="project-visit-link">
                    Visitar sitio
                    <svg class="external-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="7" y1="17" x2="17" y2="7"></line>
                      <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                  </a>
                </div>
              </div>
            </template>
          </div>
        </transition>
      </div>

      <!-- Sección CONTACT -->
      <div 
        v-else-if="activeSection === 'contact'" 
        key="contact" 
        class="card glassmorphic contact-layout"
        @mousemove="handleTilt"
        @mouseleave="resetTilt"
      >
        <span class="eyebrow">CONTACTAR</span>
        <h2>&iquest;HABLAMOS?</h2>
        <p class="description contact-desc">
          Si buscas incorporar a tu equipo a un desarrollador proactivo, con capacidad de adaptaci&oacute;n escribeme.
        </p>
        <div class="contact-action-container">
          <button @click="$emit('open-contact-modal')" class="btn btn-primary btn-email-cta">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="email-btn-icon">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Enviar un email
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  activeSection: {
    type: String,
    required: true
  }
});

defineEmits(['navigate', 'open-contact-modal']);

const projects = [
  {
    key: 'libreria-gabi',
        title: 'Librer\u00eda Gabi',
    status: 'EN DESARROLLO',
    statusClass: 'active',
        shortDescription: 'Tienda online para gesti\u00f3n de libros, carrito, pasarela de pago y autenticaci\u00f3n segura.',
        longDescription: 'Plataforma e-commerce completa para gesti\u00f3n de libros, carrito de compras, pasarela de pago, autenticaci\u00f3n OAuth2 y panel de administraci\u00f3n.',
    tech: ['Vue 3', 'PHP', 'MySQL', 'OAuth2'],
    url: 'https://libreria-taupe.vercel.app/'
  },
  {
    key: 'reglado',
    title: 'Inmobiliaria Reglado',
    status: 'EN VIVO',
    statusClass: 'live',
        shortDescription: 'Aplicaci\u00f3n para publicar y gestionar inmuebles con una interfaz clara y orientada al usuario.',
        longDescription: 'Aplicaci\u00f3n web interactiva desarrollada para la gesti\u00f3n y publicaci\u00f3n de inmuebles durante mi periodo de pr\u00e1cticas profesionales.',
    tech: ['HTML5', 'CSS3', 'JavaScript', 'PHP'],
    url: 'https://regladorealestate.com/'
  }
];

const selectedProject = ref(projects[0]);
const showProjectDetail = ref(false);

const selectProject = (project) => {
  selectedProject.value = project;
  showProjectDetail.value = true;
};

// Efecto interactivo 3D Tilt
const handleTilt = (e) => {
  const card = e.currentTarget;
  const rect = card.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;
  
  card.style.setProperty('--mouse-x', `${x}px`);
  card.style.setProperty('--mouse-y', `${y}px`);
  
  const xc = rect.width / 2;
  const yc = rect.height / 2;
  
  const tiltX = (yc - y) / (rect.height / 8);
  const tiltY = (x - xc) / (rect.width / 8);
  
  card.style.transition = 'none';
  card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(1.02, 1.02, 1.02)`;
  card.style.boxShadow = `${-tiltY * 3}px ${tiltX * 3}px 30px rgba(0,0,0,0.4), 0 20px 50px rgba(0,0,0,0.5)`;
};

const resetTilt = (e) => {
  const card = e.currentTarget;
  card.style.transition = 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.5s ease';
  card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
  card.style.boxShadow = '';
};
</script>

<style scoped>
.content-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 2;
  pointer-events: none; /* Dejar pasar los clics para el WebGL */
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding-left: 8%;
  box-sizing: border-box;
}

.content-overlay.center-layout {
  justify-content: center;
  padding-left: 0;
}

.card {
  position: relative;
  pointer-events: auto; /* Reactivar clics dentro de la tarjeta */
  max-width: 780px;
  width: 95%;
  max-height: calc(100vh - 220px); /* Evita desbordar la pantalla entre nav y footer */
  overflow-y: auto;
  padding: 40px;
  border-radius: 20px;
  box-sizing: border-box;
  color: var(--color-text);
  /* TransiciÃƒÂ³n de tema */
  transition: color 0.8s ease, transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
}

/* Scrollbar personalizado para la tarjeta en general */
.card::-webkit-scrollbar {
  width: 6px;
}
.card::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.01);
  border-radius: 10px;
}
.card::-webkit-scrollbar-thumb {
  background: var(--color-glass-border);
  border-radius: 10px;
  transition: background-color 0.3s ease;
}
.card::-webkit-scrollbar-thumb:hover {
  background: var(--color-accent);
}

/* Efecto Glassmorphism */
.glassmorphic {
  position: relative;
  background: var(--color-glass-bg);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--color-glass-border);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
  transition: background-color 0.8s ease, border-color 0.8s ease, box-shadow 0.8s ease;
}

/* Textura de micro-grano fÃƒÂ­sico (Frosted Glass) */
.glassmorphic::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
  opacity: 0.045;
  mix-blend-mode: overlay;
  pointer-events: none;
  z-index: -1;
}

/* Borde reactivo dinÃƒÂ¡mico de luz */
.glassmorphic::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  padding: 1px;
  background: radial-gradient(circle 240px at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(255, 255, 255, 0.35), transparent 70%);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: 2;
}

.eyebrow {
  font-size: 0.75rem;
  letter-spacing: 0.3em;
  color: var(--color-eyebrow);
  text-transform: uppercase;
  display: block;
  margin-bottom: 15px;
  font-weight: 600;
  transition: color 0.8s ease;
}

h1 {
  font-size: 3.5rem;
  font-weight: 400;
  line-height: 1.1;
  margin: 0 0 15px 0;
  letter-spacing: -0.02em;
  font-family: 'Outfit', sans-serif;
  color: var(--color-title);
  transition: color 0.8s ease;
}

h2 {
  font-size: 2.2rem;
  font-weight: 400;
  line-height: 1.2;
  margin: 0 0 25px 0;
  letter-spacing: -0.01em;
  font-family: 'Outfit', sans-serif;
  color: var(--color-title);
  transition: color 0.8s ease;
}

.subtitle {
  font-size: 1.1rem;
  line-height: 1.5;
  color: var(--color-accent);
  margin: 0 0 20px 0;
  transition: color 0.8s ease;
}

.hero-subtitle {
  font-size: 1.25rem;
  line-height: 1.6;
  font-weight: 400;
  color: var(--color-text);
  opacity: 0.95;
  margin: 0 0 30px 0;
  font-family: 'Inter', sans-serif;
  letter-spacing: normal;
  text-transform: none;
}

.description {
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--color-text);
  opacity: 0.85;
  margin-bottom: 35px;
  transition: color 0.8s ease;
}

.cta-container {
  display: flex;
  gap: 15px;
}

/* Botones Premium */
.btn {
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  font-weight: 500;
  padding: 12px 28px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), background-color 0.8s ease, color 0.8s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
}

.btn-primary {
  background: var(--color-glass-sub-bg);
  color: var(--color-title);
  border: 1px solid var(--color-glass-border);
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.05);
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.btn-primary::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  padding: 1px;
  background: linear-gradient(135deg, var(--color-accent), transparent, var(--color-accent));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: 2;
}

.btn-primary:hover {
  background: var(--color-glass-sub-hover-bg);
  transform: translateY(-2px);
  box-shadow: 0 0 25px var(--color-accent);
  color: var(--color-title);
  border-color: var(--color-accent);
}

.btn-primary .arrow {
  margin-left: 8px;
  transition: transform 0.3s ease;
}

.btn-primary:hover .arrow {
  transform: translateX(4px);
}

.btn-secondary {
  background-color: transparent;
  color: var(--color-title);
  border: 1px solid var(--color-glass-border);
}

.btn-secondary:hover {
  background-color: var(--color-glass-sub-hover-bg);
  border-color: var(--color-title);
  transform: translateY(-2px);
}

/* DistribuciÃƒÂ³n de Sobre mÃƒÂ­ */
.about-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.bio p {
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--color-text);
  opacity: 0.9;
  margin: 0 0 15px 0;
  transition: color 0.8s ease;
}

.skills h3 {
  font-size: 1rem;
  margin: 0 0 12px 0;
  color: var(--color-title);
  letter-spacing: 0.05em;
  text-transform: uppercase;
  transition: color 0.8s ease;
}

.skill-tags {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.skill-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag {
  font-size: 0.75rem;
  background: var(--color-glass-sub-bg);
  border: 1px solid var(--color-glass-sub-border);
  color: var(--color-text);
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 500;
  transition: background-color 0.8s ease, border-color 0.8s ease, color 0.8s ease;
}

/* Estilo para los Proyectos */
.projects-layout {
  max-width: 1180px; /* Más ancho para el panel de detalle y el acceso directo */
}

.projects-intro {
  font-size: 0.9rem;
  color: var(--color-text);
  opacity: 0.8;
  margin-bottom: 25px;
  transition: color 0.8s ease;
}

.projects-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
  max-height: 500px;
  overflow-y: auto;
  padding-right: 5px;
}

/* Personalizar barra de scroll de la tarjeta */
.projects-grid::-webkit-scrollbar {
  width: 4px;
}
.projects-grid::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.03);
}
.projects-grid::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 2px;
}

.glassmorphic-sub {
  position: relative;
  background: var(--color-glass-sub-bg);
  border: 1px solid var(--color-glass-sub-border);
  border-radius: 12px;
  padding: 20px;
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), background-color 0.8s ease, border-color 0.8s ease;
  transform-style: preserve-3d;
}

.glassmorphic-sub::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
  opacity: 0.03;
  mix-blend-mode: overlay;
  pointer-events: none;
  z-index: -1;
}

.glassmorphic-sub::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  padding: 1px;
  background: radial-gradient(circle 140px at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(255, 255, 255, 0.28), transparent 70%);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: 2;
}

.project-item {
  transform-style: preserve-3d;
}

.project-selector {
  width: 100%;
  text-align: left;
  font: inherit;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  border: 1px solid var(--color-glass-sub-border);
}

.project-selector.active {
  border-color: var(--color-accent);
  background: var(--color-glass-sub-hover-bg);
}

.project-item:hover {
  background: var(--color-glass-sub-hover-bg);
  border-color: var(--color-glass-sub-hover-border);
}

.project-select-hint {
  font-size: 0.65rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-accent);
  opacity: 0.85;
}

.project-detail-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.75fr) minmax(280px, 1fr);
  gap: 20px;
  align-items: stretch;
}

.project-detail-card {
  min-height: 100%;
  background: var(--color-detail-card-bg);
  border: 1px solid var(--color-detail-card-border);
}

.project-detail-summary,
.project-detail-cta {
  display: flex;
  flex-direction: column;
}

.project-detail-summary {
  position: relative;
  padding-top: 64px;
  min-height: 380px;
}

.project-back-btn {
  position: absolute;
  top: 18px;
  left: 18px;
  border: 1px solid var(--color-glass-sub-border);
  background: var(--color-glass-sub-bg);
  color: var(--color-title);
  border-radius: 999px;
  width: 38px;
  height: 38px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font: inherit;
  cursor: pointer;
  transition: background-color 0.8s ease, border-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
  z-index: 3;
}

.project-back-btn:hover {
  transform: translateY(-1px);
  background: var(--color-glass-sub-hover-bg);
  border-color: var(--color-accent);
  color: var(--color-accent);
}

.back-icon {
  width: 18px;
  height: 18px;
}

.project-switcher {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: auto;
  padding-top: 14px;
}

.project-switch-btn {
  border: 1px solid var(--color-glass-sub-border);
  background: transparent;
  color: var(--color-text);
  border-radius: 999px;
  padding: 7px 10px;
  font: inherit;
  font-size: 0.72rem;
  cursor: pointer;
  transition: background-color 0.8s ease, border-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
}

.project-switch-btn:hover,
.project-switch-btn.active {
  border-color: var(--color-accent);
  color: var(--color-accent);
  background: var(--color-glass-sub-hover-bg);
}

.detail-label {
  display: inline-block;
  font-size: 0.68rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-eyebrow);
  margin-bottom: 10px;
  font-weight: 600;
}

.project-detail-card .detail-label {
  color: var(--color-accent);
  font-weight: 700;
  letter-spacing: 0.16em;
}

.project-detail-card h4 {
  font-size: 1.1rem;
  margin: 0 0 10px 0;
  color: var(--color-title);
  transition: color 0.8s ease;
}

.project-detail-card p {
  margin-bottom: 16px;
  color: var(--color-title);
  opacity: 1;
  line-height: 1.65;
  font-size: 0.92rem;
}

.project-visit-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 700;
  color: #0c0a10;
  padding: 12px 20px;
  border-radius: 8px;
  background: var(--color-accent);
  border: 1px solid var(--color-accent);
  transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.8s ease, color 0.8s ease, border-color 0.8s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.project-visit-link:hover {
  transform: translateY(-2px);
  background: #ffffff;
  border-color: #ffffff;
  color: #0c0a10;
  box-shadow: 0 6px 20px rgba(255, 255, 255, 0.15);
}

.theme-light .project-visit-link {
  color: #ffffff;
}

.theme-light .project-visit-link:hover {
  background: #0f0f12;
  border-color: #0f0f12;
  color: #ffffff;
}

.projects-fade-enter-active,
.projects-fade-leave-active {
  transition: opacity 0.35s ease, transform 0.35s ease;
}

.projects-fade-enter-from,
.projects-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.project-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  border-radius: 20px;
  font-size: 0.62rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  background: rgba(0, 0, 0, 0.05);
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  position: relative;
  animation: status-pulse 1.2s infinite alternate;
}

.status-badge.live .status-dot {
  background-color: #4ade80;
  box-shadow: 0 0 8px #4ade80;
}

.status-badge.active .status-dot {
  background-color: #60a5fa;
  box-shadow: 0 0 8px #60a5fa;
}

.status-badge.concept .status-dot {
  background-color: #fbbf24;
  box-shadow: 0 0 8px #fbbf24;
}

@keyframes status-pulse {
  0% { transform: scale(0.85); opacity: 0.5; }
  100% { transform: scale(1.25); opacity: 1; }
}

.status-text {
  color: var(--color-text);
  opacity: 0.8;
}

.project-link {
  color: var(--color-text);
  opacity: 0.6;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s ease, opacity 0.3s ease, transform 0.3s ease;
}

.project-link:hover {
  color: var(--color-accent);
  opacity: 1;
  transform: translate(2px, -2px);
}

.external-icon {
  width: 15px;
  height: 15px;
}

.project-item h4 {
  font-size: 1.1rem;
  margin: 0 0 6px 0;
  color: var(--color-title);
  transition: color 0.8s ease;
}

.project-item p {
  font-size: 0.8rem;
  line-height: 1.5;
  color: var(--color-text);
  opacity: 0.8;
  margin: 0 0 12px 0;
  transition: color 0.8s ease;
}

.project-tech {
  display: flex;
  gap: 6px;
}

.project-tech span {
  font-size: 0.7rem;
  background: var(--color-glass-sub-bg);
  border: 1px solid var(--color-glass-sub-border);
  color: var(--color-accent);
  padding: 3px 8px;
  border-radius: 4px;
  transition: background-color 0.8s ease, color 0.8s ease;
}

/* DistribuciÃƒÂ³n de Contacto */
.contact-layout {
  text-align: center;
}

.contact-desc {
  margin-bottom: 30px;
  font-size: 1rem;
  line-height: 1.7;
}

.contact-action-container {
  display: flex;
  justify-content: center;
  margin-top: 10px;
}

.btn-email-cta {
  gap: 10px;
  text-decoration: none;
  font-weight: 600;
}

.email-btn-icon {
  width: 18px;
  height: 18px;
}

/* Transiciones de Entrada/Salida */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Animaciones escalonadas para elementos internos de la tarjeta */
.fade-slide-enter-active .eyebrow,
.fade-slide-enter-active h1,
.fade-slide-enter-active h2,
.fade-slide-enter-active .subtitle,
.fade-slide-enter-active .description,
.fade-slide-enter-active .projects-intro,
.fade-slide-enter-active .projects-grid,
.fade-slide-enter-active .about-grid,
.fade-slide-enter-active .contact-form,
.fade-slide-enter-active .cta-container {
  transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.6s ease;
}

.fade-slide-enter-from .eyebrow,
.fade-slide-enter-from h1,
.fade-slide-enter-from h2,
.fade-slide-enter-from .subtitle,
.fade-slide-enter-from .description,
.fade-slide-enter-from .projects-intro,
.fade-slide-enter-from .projects-grid,
.fade-slide-enter-from .about-grid,
.fade-slide-enter-from .contact-form,
.fade-slide-enter-from .cta-container {
  opacity: 0;
  transform: translateY(15px);
}

/* Tiempos de retraso escalonados */
.fade-slide-enter-active .eyebrow {
  transition-delay: 0.1s;
}
.fade-slide-enter-active h1,
.fade-slide-enter-active h2 {
  transition-delay: 0.18s;
}
.fade-slide-enter-active .subtitle,
.fade-slide-enter-active .description,
.fade-slide-enter-active .projects-intro {
  transition-delay: 0.26s;
}
.fade-slide-enter-active .projects-grid,
.fade-slide-enter-active .about-grid,
.fade-slide-enter-active .contact-form {
  transition-delay: 0.34s;
}
.fade-slide-enter-active .cta-container {
  transition-delay: 0.42s;
}

/* Responsividad */
@media (max-width: 900px) {
  .content-overlay {
    padding-left: 0;
    justify-content: center;
    align-items: center; /* Centrar verticalmente */
    padding-bottom: 0;
  }
  
  .card {
    max-width: 90%;
    padding: 30px 25px;
    max-height: calc(100vh - 200px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
  }
  
  h1 {
    font-size: 2.4rem;
  }
  h2 {
    font-size: 1.8rem;
  }
  
  .projects-grid {
    max-height: 240px;
  }

  .project-detail-grid {
    grid-template-columns: 1fr;
  }

  .project-detail-cta {
    order: 1;
  }

  .project-detail-summary {
    order: 2;
  }
}

@media (max-width: 480px) {
  .card {
    max-width: 95%;
    padding: 24px 18px;
    max-height: calc(100vh - 170px); /* MÃƒÂ¡s compacto para dejar espacio al nav y footer compactos */
  }
  
  h1 {
    font-size: clamp(1.8rem, 8vw, 2.2rem);
  }
  h2 {
    font-size: clamp(1.4rem, 6vw, 1.7rem);
  }
  
  .projects-grid {
    max-height: 190px;
    gap: 12px;
  }

  .cta-container {
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .btn {
    width: 100%;
    padding: 11px 20px;
  }

  .about-grid {
    gap: 15px;
  }

  .bio p {
    font-size: 0.88rem;
    line-height: 1.6;
  }
}
</style>



