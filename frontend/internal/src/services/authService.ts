// client/src/services/authService.ts
import axios from "@/lib/api";

export const login = async (email: string, password: string) => {
  // Utiliser FormData au lieu de JSON car l'API attend du form data
  const formData = new FormData();
  formData.append('login', email);
  formData.append('password', password);
  
  const response = await axios.post("/auth/login", formData, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
  });
  return response.data;
};




