<template>
  <nav class="nav-bar glassmorphic">
    <div class="logo-container" @click="$emit('navigate', 'home')">
      <!-- Modern Transparent Monogram Logo J & E -->
      <svg viewBox="0 0 100 100" class="logo-svg" xmlns="http://www.w3.org/2000/svg">
        <path 
          d="M 45,20 L 45,68 C 45,82 22,82 22,68 M 45,20 L 78,20 M 45,44 L 72,44 M 45,68 L 78,68" 
          fill="none" 
          stroke="currentColor" 
          stroke-width="8" 
          stroke-linecap="round" 
          stroke-linejoin="round"
        />
      </svg>
    </div>

    <!-- Menú Desktop -->
    <ul class="nav-links">
      <li 
        v-for="item in menuItems" 
        :key="item.key" 
        :class="{ active: activeSection === item.key }"
        @click="$emit('navigate', item.key)"
      >
        <span class="nav-label">{{ item.label }}</span>
        <span class="active-indicator"></span>
      </li>

      <!-- Botón Cambio de Tema Desktop -->
      <li class="theme-toggle-item" @click="$emit('toggle-theme')" title="Cambiar Tema">
        <div class="theme-icon-wrapper">
          <svg v-if="isDarkMode" class="theme-icon sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
          </svg>
          <svg v-else class="theme-icon moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
          </svg>
        </div>
      </li>
    </ul>

    <!-- Controles Móviles (Tema y Hamburguesa) -->
    <div class="mobile-controls-wrapper">
      <div class="mobile-theme-toggle" @click="$emit('toggle-theme')" title="Cambiar Tema">
        <svg v-if="isDarkMode" class="theme-icon sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"></circle>
          <line x1="12" y1="1" x2="12" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="23"></line>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
          <line x1="1" y1="12" x2="3" y2="12"></line>
          <line x1="21" y1="12" x2="23" y2="12"></line>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <svg v-else class="theme-icon moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
      </div>

      <div class="mobile-toggle" @click="toggleMobileMenu" :class="{ open: mobileMenuOpen }">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>

    <!-- Menú Mobile Overlay -->
    <transition name="slide-down">
      <div v-if="mobileMenuOpen" class="mobile-menu-overlay glassmorphic">
        <ul class="mobile-links">
          <li 
            v-for="item in menuItems" 
            :key="item.key" 
            :class="{ active: activeSection === item.key }"
            @click="handleMobileNavigate(item.key)"
          >
            <span class="mobile-label">{{ item.label }}</span>
          </li>
        </ul>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  activeSection: {
    type: String,
    required: true
  },
  isDarkMode: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['navigate', 'toggle-theme']);

const mobileMenuOpen = ref(false);

const menuItems = [
  { key: 'home', label: 'INICIO' },
  { key: 'about', label: 'SOBRE MÍ' },
  { key: 'projects', label: 'PROYECTOS' },
  { key: 'contact', label: 'CONTACTO' }
];

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
};

const handleMobileNavigate = (key) => {
  emit('navigate', key);
  mobileMenuOpen.value = false;
};
</script>

<style scoped>
.nav-bar {
  position: absolute;
  top: 30px;
  left: 5%;
  right: 5%;
  height: 70px;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 40px;
  border-radius: 15px;
  box-sizing: border-box;
  color: #ffffff;
}

.glassmorphic {
  background: var(--color-glass-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--color-glass-border);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.logo-container {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.logo-svg {
  height: 42px;
  width: 42px;
  color: var(--color-title);
  transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), color 0.4s ease, filter 0.4s ease;
  pointer-events: none;
}

.logo-container:hover .logo-svg {
  transform: scale(1.12) rotate(-5deg);
  color: var(--color-accent);
  filter: drop-shadow(0 0 8px var(--color-accent));
}

.nav-links {
  display: flex;
  list-style: none;
  gap: 35px;
  margin: 0;
  padding: 0;
  height: 100%;
  align-items: center;
}

.nav-links li {
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 0;
  opacity: 0.65;
  transition: opacity 0.3s ease;
  color: var(--color-text);
}

.nav-links li:hover {
  opacity: 1;
}

.nav-links li.active {
  opacity: 1;
}


.nav-label {
  font-size: 0.85rem;
  letter-spacing: 0.1em;
  font-family: 'Inter', sans-serif;
  text-transform: uppercase;
}

.active-indicator {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background-color: var(--color-accent);
  transition: width 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
  box-shadow: 0 0 8px var(--color-accent);
}

.nav-links li.active .active-indicator {
  width: 100%;
}

/* Menú móvil */
.mobile-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 22px;
  height: 16px;
  cursor: pointer;
}

.mobile-toggle span {
  display: block;
  width: 100%;
  height: 2px;
  background-color: var(--color-title);
  transition: all 0.3s ease;
}

.mobile-toggle.open span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.mobile-toggle.open span:nth-child(2) {
  opacity: 0;
}

.mobile-toggle.open span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}

.mobile-menu-overlay {
  display: none;
  position: absolute;
  top: 85px;
  left: 0;
  right: 0;
  padding: 30px;
  border-radius: 12px;
  z-index: 9;
  background: rgba(10, 10, 12, 0.92) !important;
  backdrop-filter: blur(25px) !important;
  -webkit-backdrop-filter: blur(25px) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.45);
}

:global(.theme-light) .mobile-menu-overlay {
  background: rgba(255, 255, 255, 0.95) !important;
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
}

.mobile-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.mobile-links li {
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  color: var(--color-title);
  border-bottom: 1px solid var(--color-glass-sub-border);
  transition: color 0.3s ease, border-color 0.8s ease;
}

.mobile-links li:hover,
.mobile-links li.active {
  color: var(--color-accent);
}

.mobile-label {
  font-size: 1.1rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

@keyframes pulse {
  0% {
    transform: scale(0.9);
    box-shadow: 0 0 4px rgba(229, 195, 178, 0.5);
  }
  100% {
    transform: scale(1.1);
    box-shadow: 0 0 12px rgba(229, 195, 178, 1);
  }
}

/* Botón de cambio de tema */
.theme-toggle-item {
  display: flex !important;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  padding: 0 !important;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  transition: background 0.3s ease, border-color 0.3s ease, transform 0.2s ease !important;
  opacity: 0.8 !important;
}

.theme-toggle-item:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.2);
  transform: scale(1.08);
  opacity: 1 !important;
}

.theme-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.theme-icon {
  width: 15px;
  height: 15px;
  color: var(--color-title);
  transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.theme-toggle-item:hover .theme-icon,
.mobile-theme-toggle:hover .theme-icon {
  transform: rotate(25deg);
}

.mobile-controls-wrapper {
  display: none;
  align-items: center;
  gap: 15px;
}

.mobile-theme-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--color-glass-sub-bg);
  border: 1px solid var(--color-glass-border);
  cursor: pointer;
  transition: background-color 0.3s ease;
}

/* Responsividad */
@media (max-width: 900px) {
  .nav-bar {
    padding: 0 24px;
    height: 60px;
    top: 20px;
  }
  
  .nav-links {
    display: none;
  }
  
  .mobile-controls-wrapper {
    display: flex;
  }
  
  .mobile-toggle {
    display: flex;
  }
  
  .mobile-menu-overlay {
    display: block;
  }
  
  .logo-text {
    font-size: 0.85rem;
  }
}

/* Transiciones de Menú Móvil */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  transform-origin: top;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: scaleY(0.8);
}
</style>
