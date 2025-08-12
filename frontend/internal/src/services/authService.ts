// client/src/services/authService.ts
import axios from "@/lib/api";

export const login = async (email: string, password: string) => {
  // L'API interne attend email et password en JSON
  const response = await axios.post("/auth/login", {
    email: email,
    password: password,
  });
  return response.data;
};




