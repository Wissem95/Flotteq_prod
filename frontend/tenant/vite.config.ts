import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

export default defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 9092,
    proxy: {
      '/api': 'http://localhost:8000',
    },
  },
  define: {
    'import.meta.env.VITE_API_URL': '"http://localhost:8000"',
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

