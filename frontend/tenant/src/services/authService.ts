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

// Fonction pour résoudre le tenant depuis le domaine
export const resolveTenantFromDomain = async () => {
  try {
    const response = await api.get("/auth/tenant-from-host");
    return response.data.tenant;
  } catch (error) {
    console.error("Erreur lors de la résolution du tenant:", error);
    throw new Error("Impossible de déterminer le tenant pour ce domaine");
  }
};

// Fonction d'inscription pour les utilisateurs tenant
export const registerTenantUser = async (userData: {
  email: string;
  username: string;
  first_name: string;
  last_name: string;
  password: string;
  password_confirmation: string;
  company_name?: string;
  phone?: string;
}) => {
  try {
    // Résoudre le tenant depuis le domaine
    const tenant = await resolveTenantFromDomain();
    
    if (!tenant?.id) {
      throw new Error("Impossible de déterminer le tenant pour ce domaine");
    }

    // Préparer les données d'inscription avec le tenant_id
    const registrationData = {
      ...userData,
      tenant_id: tenant.id,
    };

    const response = await api.post("/auth/register-tenant-user", registrationData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    return response.data;
  } catch (error) {
    console.error("Erreur lors de l'inscription:", error);
    throw error;
  }
};

// Fonction pour l'inscription classique (création d'un nouveau tenant - admin)
export const register = async (userData: {
  email: string;
  username: string;
  first_name: string;
  last_name: string;
  password: string;
  password_confirmation: string;
  company_name: string;
  domain?: string;
}) => {
  try {
    const response = await api.post("/auth/register", userData, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    return response.data;
  } catch (error) {
    console.error("Erreur lors de l'inscription admin:", error);
    throw error;
  }
};




