// internal/src/lib/api.ts - Configuration API pour l'interface d'administration

import axios from "axios";

// Configuration API pour l'interface d'administration FlotteQ
const InternalAPI = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/internal', // Endpoint spécifique à l'administration
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Fonction pour récupérer le token CSRF
const getCsrfToken = async () => {
  try {
    const baseURL = import.meta.env.VITE_API_URL;
    const csrfUrl = baseURL.replace('/api', '') + '/sanctum/csrf-cookie';
    await axios.get(csrfUrl, {
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

// Intercepteur pour ajouter le token d'authentification admin
InternalAPI.interceptors.request.use(async (config) => {
  // Pour les requêtes POST/PUT/DELETE, récupérer le token CSRF d'abord
  if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
    await getCsrfToken();
    
    // Extraire le token CSRF du cookie XSRF-TOKEN
    const csrfToken = getCookieValue('XSRF-TOKEN');
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
    }
  }

  // Ajouter l'en-tête d'authentification admin
  const token = localStorage.getItem("internal_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  // En-tête pour identifier les requêtes internes
  config.headers['X-Internal-Request'] = 'true';

  return config;
});

// Intercepteur pour gérer les erreurs d'authentification
InternalAPI.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expiré ou invalide, rediriger vers la connexion
      localStorage.removeItem("internal_token");
      localStorage.removeItem("internal_user");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  }
);

export default InternalAPI;
export { InternalAPI as api };

