import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

/**
 * Configuración de Vite para el Frontend
 * 
 * Configura los plugins de Vite (Vue 3) y redirige el directorio de compilación final
 * a la raíz del monorepo (`../dist`) para facilitar el despliegue automático en Vercel.
 */
export default defineConfig({
  plugins: [vue()],
  build: {
    // Definir la salida del build en la carpeta /dist de la raíz del monorepo
    outDir: '../dist',
    // Limpiar el directorio de salida antes de cada proceso de compilación
    emptyOutDir: true
  }
})
