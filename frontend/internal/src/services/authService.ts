// client/src/services/authService.ts
import axios from "@/lib/api";

export const login = async (email: string, password: string) => {
  const response = await axios.post("/api/auth/login", {
    email,
    mot_de_passe: password, // ⚠️ ce champ doit s’appeler mot_de_passe comme côté backend
  });
  return response.data;
};




