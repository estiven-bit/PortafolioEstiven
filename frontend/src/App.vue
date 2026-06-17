<template>
  <div class="app-wrapper" :class="[isDarkMode ? 'theme-dark' : 'theme-light', 'section-' + activeSection, { 'is-transitioning': isTransitioning }]">
    <!-- Fondo WebGL con Shaders 3D y Efecto de Ondas -->
    <WebGLCanvas 
      :is-dark-mode="isDarkMode"
    />

    <!-- Menú Superior Glassmorphic -->
    <Navigation 
      :active-section="activeSection" 
      :is-dark-mode="isDarkMode" 
      @navigate="handleNavigation" 
      @toggle-theme="toggleTheme" 
    />

    <!-- Tarjetas de Información -->
    <SectionCard 
      :active-section="activeSection" 
      @navigate="handleNavigation" 
      @open-contact-modal="openContactModal"
    />

    <!-- Pie de página / Indicador visual -->
    <div class="footer-info">
      <div class="social-links">
        <a href="https://github.com/estiven-bit" target="_blank" rel="noopener noreferrer" title="GitHub">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="social-icon">
            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
          </svg>
        </a>
        <a href="https://www.linkedin.com/in/jhon-estiven-d%C3%A1vila-valencia-834a25396/" target="_blank" rel="noopener noreferrer" title="LinkedIn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="social-icon">
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
            <rect x="2" y="9" width="4" height="12"></rect>
            <circle cx="4" cy="4" r="2"></circle>
          </svg>
        </a>
        <a href="#" @click.prevent="openContactModal" title="Email">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="social-icon">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
        </a>
      </div>
    </div>

    <!-- Modal de Contacto Integrado -->
    <transition name="fade-scale">
      <div v-if="showContactModal" class="modal-overlay" @click.self="closeContactModal">
        <div class="modal-card glassmorphic">
          <button class="modal-close-btn" @click="closeContactModal" title="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="close-icon">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
          
          <span class="eyebrow">¿HABLAMOS?</span>
          <h3>Enviar un mensaje</h3>
          
          <form @submit.prevent="handleContactSubmit" class="contact-form" v-if="!formSubmitted">
            <div class="form-group">
              <label for="modal-nombre">Nombre</label>
              <input 
                type="text" 
                id="modal-nombre" 
                v-model="contactForm.nombre" 
                required 
                placeholder="Tu nombre completo"
              />
            </div>

            <div class="form-group">
              <label for="modal-email">Email</label>
              <input 
                type="email" 
                id="modal-email" 
                v-model="contactForm.email" 
                required 
                placeholder="tu@email.com"
              />
            </div>

            <div class="form-group">
              <label for="modal-asunto">Asunto</label>
              <input 
                type="text" 
                id="modal-asunto" 
                v-model="contactForm.asunto" 
                required 
                placeholder="Asunto del mensaje"
              />
            </div>

            <div class="form-group">
              <label for="modal-mensaje">Mensaje</label>
              <textarea 
                id="modal-mensaje" 
                v-model="contactForm.mensaje" 
                required 
                rows="4" 
                placeholder="Escribe tu mensaje aquí..."
              ></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-submit" :disabled="formSubmitting">
              <span v-if="formSubmitting">Enviando...</span>
              <span v-else>Enviar Mensaje</span>
            </button>
            
            <p v-if="formError" class="form-message error">{{ formError }}</p>
          </form>

          <div v-else class="form-success-container">
            <div class="success-icon">✓</div>
            <h3>¡Mensaje Enviado!</h3>
            <p>{{ formSuccessMessage }}</p>
            <button @click="resetContactForm" class="btn btn-secondary">Cerrar</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
/**
 * Componente Principal de la Aplicación (App.vue)
 * 
 * Gestiona el estado global de la aplicación, incluyendo la navegación entre secciones,
 * el tema (Modo Claro / Modo Oscuro) y la visibilidad del modal de contacto integrado.
 * También controla el proceso de envío del formulario de contacto hacia el backend en PHP.
 */
import { ref, reactive } from 'vue';
import WebGLCanvas from './components/WebGLCanvas.vue'; // Canvas WebGL que renderiza el fondo 3D con shaders
import Navigation from './components/Navigation.vue';     // Menú superior de navegación y controles de tema
import SectionCard from './components/SectionCard.vue';   // Tarjeta central que contiene la información de cada sección

// Sección actualmente activa ('home', 'about', 'projects', 'contact')
const activeSection = ref('home');

// Flag para bloquear interacciones del usuario durante las transiciones animadas entre secciones
const isTransitioning = ref(false);

// Estado del tema visual (true = Modo Oscuro / false = Modo Claro)
const isDarkMode = ref(true);

// Estado de visibilidad y envío del Modal de Contacto
const showContactModal = ref(false);
const formSubmitting = ref(false);     // true mientras el POST está en curso
const formSubmitted = ref(false);      // true si el envío fue exitoso
const formError = ref(null);            // Mensaje de error para mostrar en la interfaz
const formSuccessMessage = ref('');    // Mensaje de éxito devuelto por el servidor

// Datos reactivos vinculados con los campos del formulario de contacto
const contactForm = reactive({
  nombre: '',
  email: '',
  asunto: '',
  mensaje: ''
});

/**
 * Controla el cambio de sección activa aplicando efectos visuales
 * 
 * @param {string} sectionKey - Clave identificadora de la sección destino
 */
const handleNavigation = (sectionKey) => {
  // Evitar solapamiento si ya está en transición o si se pulsa sobre la sección actual
  if (isTransitioning.value || activeSection.value === sectionKey) return;
  
  isTransitioning.value = true;
  activeSection.value = sectionKey;
  
  // Liberar el bloqueo de transición tras la finalización de las animaciones CSS (350ms)
  setTimeout(() => {
    isTransitioning.value = false;
  }, 350);
};

/**
 * Alterna el tema de la aplicación entre Modo Oscuro y Modo Claro
 */
const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
};

/**
 * Abre el modal del formulario de contacto
 */
const openContactModal = () => {
  showContactModal.value = true;
};

/**
 * Cierra el modal de contacto limpiando datos si ya se había enviado con éxito
 */
const closeContactModal = () => {
  showContactModal.value = false;
  if (formSubmitted.value) {
    resetContactForm();
  }
};

/**
 * Gestiona el envío del formulario de contacto mediante petición AJAX (fetch POST)
 */
const handleContactSubmit = async () => {
  formSubmitting.value = true;
  formError.value = null;

  try {
    // Determinar la URL del endpoint basándose en variables de entorno (Vite local vs producción)
    const apiUrl = import.meta.env.VITE_API_URL || '/portfolio/backend/api/contact.php';
    
    // Realizar la petición POST enviando los datos serializados en JSON
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(contactForm)
    });

    // Validar si la respuesta del servidor es un formato JSON legible
    let result;
    try {
      result = await response.json();
    } catch (e) {
      throw new Error('La respuesta del servidor no es un JSON válido.');
    }

    // Si el servidor responde correctamente (status 200 y success true)
    if (response.ok && result.success) {
      formSubmitted.value = true;
      formSuccessMessage.value = result.message;
      
      // Limpiar los campos del formulario reactivo
      contactForm.nombre = '';
      contactForm.email = '';
      contactForm.asunto = '';
      contactForm.mensaje = '';
    } else {
      // Mostrar el error devuelto por la lógica del backend
      formError.value = result.message || 'Ocurrió un error al enviar el formulario.';
    }
  } catch (error) {
    console.error('Error al enviar formulario:', error);
    
    // Si la conexión falla del todo (ej. error de red), mostramos mensajes contextuales
    const isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    if (isLocal) {
      // Mensaje de asistencia técnica para desarrollo local
      formError.value = 'No se pudo conectar con el servidor backend PHP. Verifica que XAMPP (Apache) esté encendido.';
    } else {
      // Mensaje limpio y profesional para producción en Internet
      formError.value = 'No se pudo conectar con el servidor de correos. Por favor, inténtalo de nuevo más tarde o envíame un correo directamente a davila.va.23@gmail.com.';
    }
  } finally {
    formSubmitting.value = false;
  }
};

/**
 * Restablece los estados del modal de contacto al estado inicial
 */
const resetContactForm = () => {
  formSubmitted.value = false;
  formError.value = null;
  formSuccessMessage.value = '';
  showContactModal.value = false;
};
</script>

<style>
/* Estilos globales */
:root {
  /* Modo Oscuro (Por defecto) */
  --color-bg: #0a0a0c;
  --color-text: #efded9;
  --color-primary: #ffffff;
  --color-accent: #e5c3b2;
  --color-glass-bg: rgba(10, 10, 12, 0.55);
  --color-glass-border: rgba(255, 255, 255, 0.08);
  --color-glass-sub-bg: rgba(255, 255, 255, 0.02);
  --color-glass-sub-border: rgba(255, 255, 255, 0.05);
  --color-glass-sub-hover-bg: rgba(255, 255, 255, 0.04);
  --color-glass-sub-hover-border: rgba(255, 255, 255, 0.12);
  --color-detail-card-bg: rgba(15, 11, 24, 0.94);
  --color-detail-card-border: rgba(255, 255, 255, 0.12);
  --color-title: #ffffff;
  --color-eyebrow: #e5c3b2;
  --color-input-bg: rgba(255, 255, 255, 0.03);
  --color-input-border: rgba(255, 255, 255, 0.1);
  --color-input-focus-bg: rgba(255, 255, 255, 0.06);
  --color-input-text: #ffffff;
  --modal-overlay-bg: radial-gradient(circle at top, rgba(90, 58, 163, 0.26), rgba(10, 10, 12, 0.82) 70%);
  --modal-card-bg: linear-gradient(180deg, rgba(30, 22, 58, 0.94), rgba(18, 14, 32, 0.92));
  --modal-card-border: rgba(214, 185, 255, 0.28);
  --modal-card-text: #f6f2ff;
  --modal-label: #e7c7ff;
  --modal-field-bg: rgba(255, 255, 255, 0.08);
  --modal-field-border: rgba(255, 255, 255, 0.16);
  --modal-field-focus-border: rgba(231, 199, 255, 0.8);
  --modal-field-focus-bg: rgba(255, 255, 255, 0.14);
  --modal-field-text: #ffffff;
  --modal-placeholder: rgba(255, 255, 255, 0.55);
  --modal-button-bg: linear-gradient(135deg, #ffffff, #d9baff);
  --modal-button-text: #221535;
  --modal-button-hover-bg: linear-gradient(135deg, #e9d4ff, #b89cff);
  --modal-button-hover-text: #140d21;
  --modal-error-text: #ffb4c8;
  --modal-error-bg: rgba(255, 107, 107, 0.1);
  --modal-error-border: rgba(255, 107, 107, 0.18);
}

.theme-light {
  /* Modo Claro */
  --color-bg: #f3f4f6;
  --color-text: #27272a;
  --color-primary: #0a0a0c;
  --color-accent: #5a3aa3;
  --color-glass-bg: rgba(255, 255, 255, 0.45);
  --color-glass-border: rgba(0, 0, 0, 0.08);
  --color-glass-sub-bg: rgba(0, 0, 0, 0.02);
  --color-glass-sub-border: rgba(0, 0, 0, 0.05);
  --color-glass-sub-hover-bg: rgba(0, 0, 0, 0.04);
  --color-glass-sub-hover-border: rgba(0, 0, 0, 0.12);
  --color-detail-card-bg: rgba(255, 255, 255, 0.97);
  --color-detail-card-border: rgba(0, 0, 0, 0.1);
  --color-title: #0f0f12;
  --color-eyebrow: #5a3aa3;
  --color-input-bg: rgba(0, 0, 0, 0.02);
  --color-input-border: rgba(0, 0, 0, 0.08);
  --color-input-focus-bg: rgba(0, 0, 0, 0.04);
  --color-input-text: #000000;
  --modal-overlay-bg: radial-gradient(circle at top, rgba(255, 255, 255, 0.22), rgba(243, 244, 246, 0.72) 60%);
  --modal-card-bg: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(244, 240, 252, 0.94));
  --modal-card-border: rgba(90, 58, 163, 0.12);
  --modal-card-text: #1f1b2e;
  --modal-label: #5a3aa3;
  --modal-field-bg: rgba(255, 255, 255, 0.58);
  --modal-field-border: rgba(90, 58, 163, 0.12);
  --modal-field-focus-border: rgba(90, 58, 163, 0.55);
  --modal-field-focus-bg: rgba(255, 255, 255, 0.8);
  --modal-field-text: #1f1b2e;
  --modal-placeholder: rgba(31, 27, 46, 0.34);
  --modal-button-bg: linear-gradient(135deg, #5a3aa3, #7b5bd8);
  --modal-button-text: #ffffff;
  --modal-button-hover-bg: linear-gradient(135deg, #6b4ac0, #8d6ded);
  --modal-button-hover-text: #ffffff;
  --modal-error-text: #9d1635;
  --modal-error-bg: rgba(157, 22, 53, 0.08);
  --modal-error-border: rgba(157, 22, 53, 0.16);
}

/* ==========================================
   DISEÑO PREMIUM DE BARRAS DE DESPLAZAMIENTO (SCROLLBARS)
   ========================================== */

/* Habilitar el esquema de color correcto para que el navegador adapte los elementos nativos */
html {
  color-scheme: dark;
}

/* Ocultar los botones de flechas para un look minimalista y moderno */
::-webkit-scrollbar-button {
  display: none !important;
}

/* Scrollbar general para navegadores Webkit (Chrome, Safari, Edge, Opera, etc.) */
::-webkit-scrollbar {
  width: 8px !important;
  height: 8px !important;
  display: block !important;
}

/* El canal por donde se desliza la barra (Track) */
::-webkit-scrollbar-track {
  background: rgba(20, 16, 35, 0.4) !important; /* Fondo oscuro premium */
  border-radius: 10px !important;
}

.theme-light ::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.06) !important;
}

/* El indicador deslizable (Thumb) */
::-webkit-scrollbar-thumb {
  background: var(--color-accent, #5a3aa3) !important;
  border-radius: 10px !important;
  border: 2px solid transparent !important;
  background-clip: padding-box !important;
  transition: background-color 0.3s ease;
}

/* En modo oscuro, usamos un degradado premium violeta/dorado */
.theme-dark ::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, var(--color-accent, #e5c3b2), #7b5bd8) !important;
}

::-webkit-scrollbar-thumb:hover {
  background: #ffffff !important;
  background-clip: padding-box !important;
}

.theme-light ::-webkit-scrollbar-thumb:hover {
  background: var(--color-primary, #0a0a0c) !important;
  background-clip: padding-box !important;
}

/* Soporte para Firefox */
* {
  scrollbar-width: thin !important;
  scrollbar-color: var(--color-accent, #5a3aa3) rgba(20, 16, 35, 0.4) !important;
}

.theme-light * {
  scrollbar-color: var(--color-accent, #5a3aa3) rgba(0, 0, 0, 0.06) !important;
}

.theme-dark * {
  scrollbar-color: var(--color-accent, #e5c3b2) rgba(20, 16, 35, 0.4) !important;
}

body, html {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background-color: var(--color-bg);
  font-family: 'Inter', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  transition: background-color 0.8s ease, color 0.8s ease;
}

/* Transiciones de color globales de componentes */
.card, .nav-bar, .logo-text, .nav-links li, .footer-info, .social-links a {
  transition: background-color 0.8s ease, color 0.8s ease, border-color 0.8s ease, box-shadow 0.8s ease;
}

.app-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

/* Efectos de transición en UI */
.app-wrapper.is-transitioning .card {
  pointer-events: none;
}

.app-wrapper.is-transitioning .nav-bar {
  pointer-events: none;
}

/* Transición por defecto suave para retorno */
.card {
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
}

.nav-bar {
  transition: opacity 0.3s ease;
}

/* Indicador de pie de página */
.footer-info {
  position: absolute;
  bottom: 25px;
  left: 5%;
  right: 5%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 0.7rem;
  letter-spacing: 0.2em;
  color: rgba(239, 222, 217, 0.45);
  font-weight: 500;
  z-index: 5;
  pointer-events: none;
}

.social-links {
  display: flex;
  gap: 24px;
  align-items: center;
  pointer-events: auto; /* Permitir interactividad de enlaces */
  background: var(--color-glass-bg);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  border: 1px solid var(--color-glass-border);
  padding: 10px 24px;
  border-radius: 30px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  transition: background-color 0.8s ease, border-color 0.8s ease, box-shadow 0.8s ease, transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.social-links:hover {
  border-color: var(--color-accent);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
  transform: scale(1.05);
}

.social-links a {
  color: var(--color-text);
  opacity: 0.65;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: color 0.4s ease, opacity 0.4s ease, transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.social-icon {
  width: 18px;
  height: 18px;
}

.social-links a:hover {
  color: var(--color-accent);
  opacity: 1;
  transform: translateY(-3px) scale(1.1);
}

@media (max-width: 480px) {
  .footer-info {
    bottom: 15px;
  }
  .social-links {
    padding: 8px 18px;
    gap: 18px;
  }
  .social-icon {
    width: 16px;
    height: 16px;
  }
}

/* Modal de Contacto */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: var(--modal-overlay-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-card {
  max-width: 500px;
  width: 90%;
  padding: 35px;
  border-radius: 20px;
  box-sizing: border-box;
  position: relative;
  max-height: 90vh;
  overflow-y: auto;
  color: var(--modal-card-text);
  background: var(--modal-card-bg);
  border: 1px solid var(--modal-card-border);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.03) inset;
}



.modal-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: transparent;
  border: none;
  color: var(--modal-card-text);
  opacity: 0.8;
  cursor: pointer;
  transition: opacity 0.3s ease, transform 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
}

.modal-close-btn:hover {
  opacity: 1;
  transform: scale(1.1);
}

.close-icon {
  width: 20px;
  height: 20px;
}

.modal-card h3 {
  font-family: 'Outfit', sans-serif;
  font-size: 1.8rem;
  margin: 0 0 20px 0;
  color: var(--modal-card-text);
}

/* Formulario en Modal */
.contact-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.form-group label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--modal-label);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.form-group input,
.form-group textarea {
  font-family: 'Inter', sans-serif;
  font-size: 0.92rem;
  background: var(--modal-field-bg);
  border: 1px solid var(--modal-field-border);
  border-radius: 10px;
  padding: 12px 14px;
  color: var(--modal-field-text);
  outline: none;
  transition: all 0.3s ease, background-color 0.8s ease, border-color 0.8s ease, color 0.8s ease;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: var(--modal-field-focus-border);
  background: var(--modal-field-focus-bg);
  box-shadow: 0 0 0 3px rgba(90, 58, 163, 0.18);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: var(--modal-placeholder);
}

.btn-submit {
  background: var(--modal-button-bg);
  color: var(--modal-button-text);
  font-weight: 600;
  margin-top: 5px;
  border: none;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.btn-submit:hover {
  background: var(--modal-button-hover-bg);
  color: var(--modal-button-hover-text);
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(184, 156, 255, 0.35);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--modal-field-bg);
  color: var(--modal-field-text);
  border: 1px solid var(--modal-field-border);
  font-weight: 600;
  margin-top: 15px;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.btn-secondary:hover {
  background: var(--modal-field-focus-bg);
  border-color: var(--modal-field-focus-border);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.form-message {
  font-size: 0.8rem;
  padding: 6px;
  border-radius: 4px;
  text-align: center;
  color: var(--modal-card-text);
}

.form-message.error {
  color: var(--modal-error-text);
  background: var(--modal-error-bg);
  border: 1px solid var(--modal-error-border);
}

.form-success-container {
  text-align: center;
  padding: 15px 0;
}

.success-icon {
  width: 50px;
  height: 50px;
  background: rgba(107, 255, 181, 0.1);
  color: #6bffb5;
  border: 1px solid rgba(107, 255, 181, 0.3);
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  margin: 0 auto 15px auto;
}

/* Transiciones del Modal */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.4s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
}

.fade-scale-enter-active .modal-card,
.fade-scale-leave-active .modal-card {
  transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.fade-scale-enter-from .modal-card,
.fade-scale-leave-to .modal-card {
  transform: scale(0.9) translateY(20px);
}

</style>
