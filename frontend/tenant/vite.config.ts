import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

export default defineConfig(({ mode }) => ({
  server: {
    host: "localhost", // Utiliser 127.0.0.1 au lieu de "::" pour éviter les problèmes WebSocket
    port: 9092,
    hmr: {
      port: 9093,
      host: "localhost",
      overlay: false // Désactive l'overlay d'erreur pour les warnings WebSocket
    },
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false
      },
    },
  },
  define: {
    'import.meta.env.VITE_API_URL': '"http://localhost:8000"',
  },
  optimizeDeps: {
    include: ['react', 'react-dom']
  },
  plugins: [
    react()
    // si vous aviez d’autres plugins (lovable-tagger, etc.), gardez-les ici
  ].filter(Boolean),
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src")
    }
  }
}));

