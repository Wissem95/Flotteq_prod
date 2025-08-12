// client/src/services/authService.ts
import { api } from "@/lib/api";

export const login = async (email: string, password: string) => {
  // Backend attend 'login' au lieu d'email
  const loginData = {
    login: email,
    password: password,
  };
  
  const response = await api.post("/auth/login", loginData, {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  });
  return response.data;
};

// Fonction pour gérer la connexion réussie
export const handleLoginSuccess = (userData: unknown, token: string) => {
  try {
    // Stocker les données utilisateur et le token
    localStorage.setItem("token", token);
    localStorage.setItem("user", JSON.stringify(userData));
    
    // Vérifier que le stockage a réussi
    const storedToken = localStorage.getItem("token");
    const storedUser = localStorage.getItem("user");
    
    if (!storedToken || !storedUser) {
      throw new Error("Impossible de stocker les données d'authentification");
    }
    
    // Attendre un petit délai pour la synchronisation puis rediriger
    setTimeout(() => {
      // Nettoyage des sessions de callback Google pour éviter les boucles
      sessionStorage.removeItem('google_callback_processed');
      
      // Redirection vers le dashboard
      window.location.href = "/dashboard";
    }, 100);
    
  } catch (error) {
    console.error("Erreur lors de la connexion:", error);
    
    // En cas d'erreur, rediriger vers login
    setTimeout(() => {
      window.location.href = "/login?error=storage_failed";
    }, 100);
  }
};

// Fonction pour vérifier l'authentification
export const checkAuthentication = () => {
  try {
    const token = localStorage.getItem("token");
    const user = localStorage.getItem("user");
    return {
      isAuthenticated: !!token,
      user: user ? JSON.parse(user) : null,
      token
    };
  } catch (error) {
    console.error("Erreur lors de la vérification de l'authentification:", error);
    return {
      isAuthenticated: false,
      user: null,
      token: null
    };
  }
};




