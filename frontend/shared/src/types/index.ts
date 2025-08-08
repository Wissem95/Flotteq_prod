// Common types for FLOTTEQ applications

export interface User {
  id: string;
  email: string;
  nom: string;
  prenom: string;
  telephone?: string;
  role?: string;
  status?: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface Vehicle {
  id: string;
  immatriculation: string;
  marque: string;
  modele: string;
  annee: number;
  kilometrage: number;
  status: 'active' | 'maintenance' | 'inactive' | 'sold';
  type_carburant: string;
  couleur?: string;
  vin?: string;
  date_mise_service?: string;
  prochaine_revision?: string;
  prochain_ct?: string;
  user_id: string;
  created_at: string;
  updated_at: string;
}

export interface Maintenance {
  id: string;
  vehicle_id: string;
  type: string;
  description: string;
  date_maintenance: string;
  cout: number;
  statut: 'planifiee' | 'en_cours' | 'terminee' | 'annulee';
  garage?: string;
  pieces_changees?: string[];
  kilometrage_maintenance?: number;
  created_at: string;
  updated_at: string;
}

export interface Garage {
  id: string;
  nom: string;
  adresse: string;
  ville: string;
  code_postal: string;
  telephone: string;
  email?: string;
  services: string[];
  note?: number;
  latitude?: number;
  longitude?: number;
  horaires?: Record<string, string>;
}

export interface Reservation {
  id: string;
  user_id: string;
  garage_id: string;
  vehicle_id: string;
  date_reservation: string;
  heure: string;
  service: string;
  statut: 'confirmee' | 'en_attente' | 'annulee' | 'terminee';
  description?: string;
  prix_estime?: number;
  created_at: string;
  updated_at: string;
}

export interface Notification {
  id: string;
  user_id: string;
  title: string;
  message: string;
  type: 'info' | 'warning' | 'error' | 'success';
  read: boolean;
  created_at: string;
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

// Form types
export interface LoginForm {
  email: string;
  password: string;
}

export interface VehicleForm {
  immatriculation: string;
  marque: string;
  modele: string;
  annee: number;
  kilometrage: number;
  type_carburant: string;
  couleur?: string;
  vin?: string;
  date_mise_service?: string;
}

export interface UserForm {
  email: string;
  nom: string;
  prenom: string;
  telephone?: string;
  role?: string;
  password?: string;
}

// Configuration types
export interface AppConfig {
  apiBaseUrl: string;
  features: {
    multiUser: boolean;
    userRoles: boolean;
    analytics: boolean;
  };
  branding: {
    title: string;
    logo?: string;
  };
}