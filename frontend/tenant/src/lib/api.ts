// clients/src/lib/api.ts

import axios from "axios";

const API = axios.create({
  baseURL: "/api", // Utiliser le proxy configuré dans Vite
  withCredentials: true, // Important : inclure les cookies pour CSRF
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest', // Important pour Laravel
  },
});



// Fonction pour récupérer le token CSRF
const getCsrfToken = async () => {
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
      withCredentials: true
    });
  } catch (error) {
    console.warn("Erreur lors de la récupération du token CSRF:", error);
  }
};

// Fonction utilitaire pour extraire un cookie
function getCookieValue(name: string): string | null {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    return parts.pop()?.split(';').shift() || null;
  }
  return null;
}

// Intercepteur pour ajouter le token d'authentification
API.interceptors.request.use(async (config) => {
  // Pour les requêtes POST/PUT/DELETE, récupérer le token CSRF d'abord
  if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
    await getCsrfToken();
    
    // Extraire le token CSRF du cookie XSRF-TOKEN
    const csrfToken = getCookieValue('XSRF-TOKEN');
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }
  }

  // Ajouter l'en-tête Tenant ID requis par le backend multitenancy
  config.headers['X-Tenant-ID'] = '1'; // FlotteQ Demo - tenant pour démonstration

  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

// Intercepteur pour gérer les erreurs de réponse
API.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    // Si l'erreur est 401 (Unauthorized), rediriger vers la page de connexion
    if (error.response?.status === 401) {
      console.warn("Token expiré ou invalide, redirection vers la page de connexion");
      
      // Nettoyer le localStorage
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      
      // Rediriger vers la page de connexion seulement si on n'y est pas déjà
      if (window.location.pathname !== "/login") {
        window.location.href = "/login?error=session_expired";
      }
    }
    
    return Promise.reject(error);
  }
);

export default API;
export { API as api };

