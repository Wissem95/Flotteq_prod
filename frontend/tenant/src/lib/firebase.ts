import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider } from 'firebase/auth';

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID
};

// Configuration par défaut pour les tests (vous remplacerez avec vos vraies values)
const defaultConfig = {
  apiKey: "demo-key",
  authDomain: "flotteq-demo.firebaseapp.com", 
  projectId: "flotteq-demo",
  storageBucket: "flotteq-demo.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:demo"
};

// Utiliser la config env ou celle par défaut
const config = firebaseConfig.apiKey ? firebaseConfig : defaultConfig;

// Initialize Firebase
const app = initializeApp(config);

// Initialize Firebase Auth and get a reference to the service
export const auth = getAuth(app);

// Initialize Google Auth Provider  
export const googleProvider = new GoogleAuthProvider();

// Configurer les scopes pour récupérer les mêmes données que votre backend actuel
googleProvider.addScope('profile');
googleProvider.addScope('email');
googleProvider.addScope('openid');

export default app;