import { api } from "@/lib/api";


export interface GoogleAuthResponse {
  auth_url: string;
  state: string;
}

export interface GoogleCallbackResponse {
  message: string;
  user: {
    id: number;
    email: string;
    username: string;
    first_name: string;
    last_name: string;
    avatar?: string;
  };
  token: string;
  tenant: {
    id: number;
    name: string;
    domain: string;
  };
}

/**
 * Initier l'authentification Google
 */
export const initiateGoogleAuth = async (tenantId: number): Promise<GoogleAuthResponse> => {
  try {
    const response = await api.post("/auth/google/redirect", {
      tenant_id: tenantId
    });
    return response.data;
  } catch (error) {
    console.error('Erreur lors de l\'initiation de l\'authentification Google:', error);
    throw error;
  }
};

/**
 * Rediriger vers Google OAuth
 */
export const redirectToGoogle = async (tenantId: number): Promise<void> => {
  try {
    const { auth_url } = await initiateGoogleAuth(tenantId);
    
    // Redirection vers Google
    window.location.href = auth_url;
  } catch (error) {
    console.error("Erreur lors de l'initiation de l'authentification Google:", error);
    throw error;
  }
};

/**
 * Gérer le callback Google (appelé automatiquement par Google)
 */
export const handleGoogleCallback = async (
  code: string, 
  state: string
): Promise<GoogleCallbackResponse> => {
  try {
    const response = await api.get(`/auth/google/callback?code=${code}&state=${state}`);
    return response.data;
  } catch (error) {
    console.error('Erreur lors du callback Google:', error);
    throw error;
  }
};

/**
 * Lier un compte Google à un utilisateur existant
 */
export const linkGoogleAccount = async (): Promise<void> => {
  await api.post("/auth/google/link");
};

/**
 * Délier un compte Google
 */
export const unlinkGoogleAccount = async (): Promise<void> => {
  await api.post("/auth/google/unlink");
}; 