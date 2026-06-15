<template>
  <div ref="canvasContainer" class="webgl-container" :class="{ 'is-mobile': isMobile }">
    <canvas ref="webglCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import * as THREE from 'three';
import gsap from 'gsap';

const props = defineProps({
  isDarkMode: { type: Boolean, required: true }
});

const canvasContainer = ref(null);
const webglCanvas     = ref(null);
const isMobile        = ref(false);

let scene, camera, renderer, clock, animationFrameId;
let planeMesh, shaderMaterial;

// ── Configuración de Temas para Fondo Líquido ──────────────────────────────
const THEME_COLORS = {
  dark: {
    base: new THREE.Color(0x020203), // Negro azulado profundo
    low:  new THREE.Color(0x0a0a10), // Base azulada oscura
    high: new THREE.Color(0x5a3aa3), // Reflejos violeta eléctrico
    spec: new THREE.Color(0xffffff), // Destellos blancos
    glowIntensity: 0.20              // Brillo de ratón en modo oscuro (perfecto)
  },
  light: {
    base: new THREE.Color(0x9aa0ac), // Gris metálico más oscuro para dar contraste y profundidad
    low:  new THREE.Color(0xbcc1cd), // Base plateada
    high: new THREE.Color(0x8a63ff), // Reflejos violeta más definidos
    spec: new THREE.Color(0xffffff), // Destellos blancos especulares
    glowIntensity: 0.05              // Brillo de ratón en modo claro muy sutil para evitar sobreexposición
  }
};

// ── Mouse & Parallax (Desactivado para iluminación, conservado para ondas) ─────

// ── Splashes en GPU (Física de ondas de piedras en agua) ──────────────────────
const activeSplashes = [];
let lastSplashX = 0;
let lastSplashY = 0;

const spawnSplash = (x, y, intensity = 0.55, speed = 0.55, decay = 1.3) => {
  activeSplashes.push({ x, y, radius: 0.0, intensity, speed, decay });
  if (activeSplashes.length > 6) {
    activeSplashes.shift();
  }
};

// ── SHADERS ──────────────────────────────────────────────────────────────────
const vertexShader = /* glsl */`
  varying vec2 vUv;
  void main() {
    vUv = uv;
    gl_Position = vec4(position, 1.0);
  }
`;

const fragmentShader = /* glsl */`
  uniform float     uTime;
  uniform vec2      uMouse;
  uniform float     uAspect;
  uniform vec2      uSplashPos[6];
  uniform float     uSplashRadius[6];
  uniform float     uSplashIntensity[6];
  uniform float     uGlowIntensity;
  uniform vec3      uBaseColor;
  uniform vec3      uBgColorLow;
  uniform vec3      uBgColorHigh;
  uniform vec3      uSpecColor;
  varying vec2      vUv;

  // Hash & Noise helper functions
  float hash(vec2 p) {
    return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453123);
  }

  float noise(vec2 p) {
    vec2 i = floor(p);
    vec2 f = fract(p);
    vec2 u = f * f * (3.0 - 2.0 * f);
    return mix(mix(hash(i + vec2(0.0, 0.0)), hash(i + vec2(1.0, 0.0)), u.x),
               mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);
  }

  float fbm(vec2 p) {
    float v = 0.0;
    float a = 0.5;
    vec2 shift = vec2(100.0);
    mat2 rot = mat2(cos(0.5), sin(0.5), -sin(0.5), cos(0.5));
    #ifdef MOBILE
    for (int i = 0; i < 2; ++i) {
    #else
    for (int i = 0; i < 4; ++i) {
    #endif
      v += a * noise(p);
      p = rot * p * 2.0 + shift;
      a *= 0.5;
    }
    return v;
  }

  // Domain Warping para textura de metal líquido
  float pattern(in vec2 p, out vec2 q, out vec2 r, float time) {
    q.x = fbm(p + vec2(0.0, 0.0));
    q.y = fbm(p + vec2(5.2, 1.3) + time * 0.018); // Movimiento ultra lento base

    #ifdef MOBILE
    r = q;
    return fbm(p + 2.0 * q);
    #else
    r.x = fbm(p + 3.0 * q + vec2(1.7, 9.2) + time * 0.010);
    r.y = fbm(p + 3.0 * q + vec2(8.3, 2.8) + time * 0.012);

    return fbm(p + 3.0 * r);
    #endif
  }

  float getLiquidHeight(vec2 uv, float time) {
    vec2 q, r;
    return pattern(uv * 2.0, q, r, time); // Reducido de 3.0 a 2.0 para ondas más grandes y fluidas
  }

  // Ecuación de paquete de ondas físicas concéntricas (Piedra en el agua)
  float getRippleHeight(vec2 uv) {
    float totalWave = 0.0;
    for (int i = 0; i < 6; i++) {
      float intensity = uSplashIntensity[i];
      if (intensity <= 0.001) continue;

      vec2 center = uSplashPos[i];
      float R = uSplashRadius[i];

      // Corregir distancia por relación de aspecto para mantener ondas circulares perfectas
      vec2 diff = (uv - center) * vec2(uAspect, 1.0);
      float d = length(diff);

      // Ecuación oscilante con atenuación y paquete limitado
      // Frecuencia reducida (55) = menos anillos más separados, como agua real
      float wave = sin((d - R) * 55.0) * exp(-abs(d - R) * 30.0);

      // Suavizar inicio al nacer y amortiguar fuertemente ondas lejanas
      float ageAttenuation = smoothstep(0.0, 0.04, R);
      float distanceAttenuation = 1.0 / (1.0 + d * 9.0);

      totalWave += wave * intensity * ageAttenuation * distanceAttenuation;
    }
    return totalWave;
  }

  float getCompositeHeight(vec2 uv, float time) {
    float ripple = getRippleHeight(uv);
    // Deformar coordenadas FBM usando las ondas físicas para refracción
    vec2 warpedUv = uv + vec2(ripple * 0.03);
    float height = getLiquidHeight(warpedUv, time);
    return height * 0.35 + ripple * 0.38;
  }

  void main() {
    vec2 uv = vUv;
    
    // Altura total combinada para cálculos de luz
    float ripple = getRippleHeight(uv);
    float totalHeight = getCompositeHeight(uv, uTime);
    
    // Cálculo de normales por diferencias finitas
    vec2 texel = vec2(0.003, 0.003);
    float h_R = getCompositeHeight(uv + vec2(texel.x, 0.0), uTime);
    float h_U = getCompositeHeight(uv + vec2(0.0, texel.y), uTime);
    
    // Suavizado de pendientes: de 12.0 a 6.5, aumentando z de 0.30 a 0.45 para menor distorsión plástica
    vec3 normal = normalize(vec3((totalHeight - h_R) * 6.5, (totalHeight - h_U) * 6.5, 0.45));
    
    // Luz virtual que sigue al ratón
    vec3 lightPos = vec3(uMouse.x * 2.0, uMouse.y * 2.0, 1.2);
    vec3 fragPos = vec3(uv * 2.0 - 1.0, 0.0);
    vec3 lightDir = normalize(lightPos - fragPos);
    vec3 viewDir = vec3(0.0, 0.0, 1.0);
    
    // 1. Reflejo Especular Focal (Molten Glass Glare) - Refinado
    vec3 halfDir = normalize(lightDir + viewDir);
    float spec = pow(max(dot(normal, halfDir), 0.0), 64.0); // Exponente aumentado para destello más puntual y pulido
    
    // 2. Reflejo Especular Ancho
    float specWide = pow(max(dot(normal, halfDir), 0.0), 16.0);
    
    // 3. Contorno de Fresnel Potenciado en Crestas
    float fresnel = pow(1.0 - max(dot(normal, viewDir), 0.0), 4.0);
    
    // 4. Halo de luz de seguimiento radial al puntero (Más difuminado y sutil)
    vec2 mouseUv = uMouse * 0.5 + 0.5;
    float lightGlow = 0.10 / (0.15 + distance(uv, mouseUv));
    
    // Color del fluido
    vec3 baseColor = uBaseColor;
    float baseNoiseHeight = getLiquidHeight(uv + vec2(ripple * 0.07), uTime);
    vec3 fluidColor = mix(uBgColorLow, uBgColorHigh, baseNoiseHeight);
    
    vec3 color = mix(baseColor, fluidColor, 0.88);
    
    // Aplicar términos de iluminación suavizados
    color += spec * 0.70 * uSpecColor;       // Destellos reducidos de 1.35 a 0.70
    color += specWide * 0.25 * uBgColorHigh; // Brillos reducidos de 0.40 a 0.25
    color += fresnel * 0.50 * uSpecColor;    // Fresnel reducido de 0.85 a 0.50
    color += lightGlow * uGlowIntensity * uSpecColor; // Halo radial
    
    // Brillo luminoso sutil en las crestas de las ondas físicas
    color += vec3(ripple * 0.07) * uSpecColor;
    
    // Brillo ambiental
    color += baseNoiseHeight * 0.06 * uBgColorHigh;
    
    gl_FragColor = vec4(color, 1.0);
  }
`;

// ── onMounted ─────────────────────────────────────────────────────────────────
onMounted(() => {
  isMobile.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768;

  const W = canvasContainer.value.clientWidth;
  const H = canvasContainer.value.clientHeight;

  scene = new THREE.Scene();
  camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0.1, 10);
  camera.position.z = 1;

  renderer = new THREE.WebGLRenderer({
    canvas:           webglCanvas.value,
    antialias:        true,
    powerPreference: 'high-performance'
  });
  renderer.setSize(W, H);
  renderer.setPixelRatio(isMobile.value ? Math.min(window.devicePixelRatio, 1.0) : Math.min(window.devicePixelRatio, 2));

  clock = new THREE.Clock();

  // Configuración de Colores Iniciales basados en el tema
  const initTheme = props.isDarkMode ? THEME_COLORS.dark : THEME_COLORS.light;

  // Inicializar arrays de uniformes vacíos
  const splashPositions = [];
  const splashRadii = [0, 0, 0, 0, 0, 0];
  const splashIntensities = [0, 0, 0, 0, 0, 0];
  
  for (let i = 0; i < 6; i++) {
    splashPositions.push(new THREE.Vector2(-10, -10));
  }

  shaderMaterial = new THREE.ShaderMaterial({
    vertexShader,
    fragmentShader: (isMobile.value ? '#define MOBILE\n' : '') + fragmentShader,
    uniforms: {
      uTime:            { value: 0.0 },
      uMouse:           { value: new THREE.Vector2(0, 0) },
      uAspect:          { value: W / H },
      uSplashPos:       { value: splashPositions },
      uSplashRadius:    { value: splashRadii },
      uSplashIntensity: { value: splashIntensities },
      uGlowIntensity:   { value: initTheme.glowIntensity },
      uBaseColor:       { value: initTheme.base.clone() },
      uBgColorLow:      { value: initTheme.low.clone() },
      uBgColorHigh:     { value: initTheme.high.clone() },
      uSpecColor:       { value: initTheme.spec.clone() }
    },
    depthWrite: false,
    depthTest:  false
  });

  const geometry = new THREE.PlaneGeometry(2, 2);
  planeMesh = new THREE.Mesh(geometry, shaderMaterial);
  scene.add(planeMesh);

  // ── Eventos ───────────────────────────────────────────────────────────────
  const onMouseMove = (e) => {
    const uvX = e.clientX / window.innerWidth;
    const uvY = 1.0 - (e.clientY / window.innerHeight);

    const dx = uvX - lastSplashX;
    const dy = uvY - lastSplashY;
    const dist = Math.sqrt(dx * dx + dy * dy);

    if (dist > 0.07) {
      spawnSplash(uvX, uvY, 0.28, 0.22, 0.38);
      lastSplashX = uvX;
      lastSplashY = uvY;
    }
  };

  const onMouseDown = (e) => {
    const uvX = e.clientX / window.innerWidth;
    const uvY = 1.0 - (e.clientY / window.innerHeight);
    
    spawnSplash(uvX, uvY, 0.65, 0.25, 0.28);
    lastSplashX = uvX;
    lastSplashY = uvY;
  };

  const onTouchMove = (e) => {
    if (e.touches.length > 0) {
      const touch = e.touches[0];
      const uvX = touch.clientX / window.innerWidth;
      const uvY = 1.0 - (touch.clientY / window.innerHeight);
      
      const dx = uvX - lastSplashX;
      const dy = uvY - lastSplashY;
      const dist = Math.sqrt(dx * dx + dy * dy);

      if (dist > 0.07) {
        spawnSplash(uvX, uvY, 0.28, 0.22, 0.38);
        lastSplashX = uvX;
        lastSplashY = uvY;
      }
    }
  };

  const onTouchStart = (e) => {
    if (e.touches.length > 0) {
      const touch = e.touches[0];
      const uvX = touch.clientX / window.innerWidth;
      const uvY = 1.0 - (touch.clientY / window.innerHeight);
      
      spawnSplash(uvX, uvY, 0.65, 0.25, 0.28);
      lastSplashX = uvX;
      lastSplashY = uvY;
    }
  };

  const onResize = () => {
    if (!canvasContainer.value) return;
    const w = canvasContainer.value.clientWidth;
    const h = canvasContainer.value.clientHeight;
    renderer.setSize(w, h);
    if (shaderMaterial) {
      shaderMaterial.uniforms.uAspect.value = w / h;
    }
  };

  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mousedown', onMouseDown);
  window.addEventListener('touchmove', onTouchMove, { passive: true });
  window.addEventListener('touchstart', onTouchStart, { passive: true });
  window.addEventListener('resize',    onResize);

  // ── Bucle de animación ────────────────────────────────────────────────────
  const animate = () => {
    const delta = clock.getDelta();
    const time = clock.elapsedTime;
    const dt = Math.min(delta, 0.03); // Evitar saltos de pestañas inactivas

    // Actualizar estados de ondas activas en JS
    for (let i = activeSplashes.length - 1; i >= 0; i--) {
      const s = activeSplashes[i];
      s.radius += s.speed * dt;
      s.intensity -= s.decay * dt;
      if (s.intensity <= 0) {
        activeSplashes.splice(i, 1);
      }
    }

    if (shaderMaterial) {
      shaderMaterial.uniforms.uTime.value = time;

      // Re-asignar arrays de floats para forzar a Three.js a re-subir los uniformes
      const newRadii = [];
      const newIntensities = [];

      for (let i = 0; i < 6; i++) {
        if (i < activeSplashes.length) {
          const s = activeSplashes[i];
          shaderMaterial.uniforms.uSplashPos.value[i].set(s.x, s.y);
          newRadii.push(s.radius);
          newIntensities.push(s.intensity);
        } else {
          shaderMaterial.uniforms.uSplashPos.value[i].set(-10, -10);
          newRadii.push(0.0);
          newIntensities.push(0.0);
        }
      }

      shaderMaterial.uniforms.uSplashRadius.value = newRadii;
      shaderMaterial.uniforms.uSplashIntensity.value = newIntensities;
    }

    renderer.render(scene, camera);
    animationFrameId = requestAnimationFrame(animate);
  };

  animate();

  onUnmounted(() => {
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mousedown', onMouseDown);
    window.removeEventListener('touchmove', onTouchMove);
    window.removeEventListener('touchstart', onTouchStart);
    window.removeEventListener('resize',    onResize);
    cancelAnimationFrame(animationFrameId);
    geometry.dispose();
    shaderMaterial.dispose();
    renderer.dispose();
  });
});

// ── Transición de Tema Claro/Oscuro ───────────────────────────────────────────
watch(() => props.isDarkMode, (newVal) => {
  const target = newVal ? THEME_COLORS.dark : THEME_COLORS.light;
  if (!shaderMaterial) return;

  gsap.to(shaderMaterial.uniforms.uBaseColor.value, {
    r: target.base.r,
    g: target.base.g,
    b: target.base.b,
    duration: 1.2,
    ease: 'power2.inOut'
  });

  gsap.to(shaderMaterial.uniforms.uBgColorLow.value, {
    r: target.low.r,
    g: target.low.g,
    b: target.low.b,
    duration: 1.2,
    ease: 'power2.inOut'
  });

  gsap.to(shaderMaterial.uniforms.uBgColorHigh.value, {
    r: target.high.r,
    g: target.high.g,
    b: target.high.b,
    duration: 1.2,
    ease: 'power2.inOut'
  });

  gsap.to(shaderMaterial.uniforms.uSpecColor.value, {
    r: target.spec.r,
    g: target.spec.g,
    b: target.spec.b,
    duration: 1.2,
    ease: 'power2.inOut'
  });

  gsap.to(shaderMaterial.uniforms.uGlowIntensity, {
    value: target.glowIntensity,
    duration: 1.2,
    ease: 'power2.inOut'
  });
});
</script>

<style scoped>
.webgl-container {
  position: absolute;
  inset: 0;
  z-index: 1;
  background-color: #050507;
  overflow: hidden;
}

/* Mobile-optimized background: zero GPU/CPU cost, buttery-smooth CSS transition */
.webgl-container.is-mobile {
  background: radial-gradient(circle at 80% 20%, #1c113e 0%, #050507 80%);
}

.webgl-container.is-mobile::before {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 80% 20%, #e3dcff 0%, #bcc1cd 85%);
  opacity: 0;
  transition: opacity 1.0s ease-in-out;
  z-index: 1;
}

:global(.theme-light) .webgl-container.is-mobile::before {
  opacity: 1;
}

canvas {
  display: block;
  width: 100%;
  height: 100%;
}
</style>
