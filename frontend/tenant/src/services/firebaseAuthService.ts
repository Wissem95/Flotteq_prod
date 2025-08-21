import { signInWithPopup, User } from 'firebase/auth';
import { auth, googleProvider } from '@/lib/firebase';
import { api } from '@/lib/api';
import { handleLoginSuccess } from './authService';

export interface FirebaseAuthResponse {
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
 * Authentification Google via Firebase (remplace l'ancienne méthode)
 */
export const signInWithGoogle = async (): Promise<void> => {
  try {
    // 1. Authentification Firebase
    const result = await signInWithPopup(auth, googleProvider);
    const user = result.user;
    
    // 2. Récupérer le token Firebase (sera vérifié par le backend)
    const firebaseToken = await user.getIdToken();
    
    // 3. Envoyer le token à votre backend Laravel (NOUVEAU ENDPOINT)
    const response = await api.post('/auth/firebase', {
      firebase_token: firebaseToken,
      // Optionnel: passer des infos supplémentaires si besoin
      user_data: {
        email: user.email,
        name: user.displayName,
        avatar: user.photoURL,
        google_id: user.uid
      }
    });
    
    // 4. Utiliser EXACTEMENT la même logique que votre code actuel
    const { user: userData, token } = response.data;
    handleLoginSuccess(userData, token);
    
  } catch (error) {
    console.error('Erreur Firebase Auth:', error);
    throw error;
  }
};

/**
 * Déconnexion Firebase
 */
export const signOutFromFirebase = async (): Promise<void> => {
  try {
    await auth.signOut();
    // Nettoyer le localStorage comme dans votre logique actuelle
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  } catch (error) {
    console.error('Erreur déconnexion Firebase:', error);
    throw error;
  }
};

/**
 * Vérifier l'état d'authentification Firebase
 */
export const getCurrentFirebaseUser = (): User | null => {
  return auth.currentUser;
};

/**
 * Observer les changements d'authentification Firebase
 */
export const onAuthStateChanged = (callback: (user: User | null) => void) => {
  return auth.onAuthStateChanged(callback);
};