import React, { useEffect, useRef, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import axios from "@/lib/api";
import { gsap } from "gsap";
import { useGSAP } from '@gsap/react';
import { redirectToGoogle } from "@/services/googleAuthService";

import '/backgrounds/Background_road.svg'


const Login = () => {
  const [bgImage, setBgImage] = useState("");
  const [currentUser, setCurrentUser] = useState<unknown | null>(null);
  const [changeUser, setChangeUser] = useState(false);
  const [users, setUsers] = useState<unknown[]>([]);
  const [identifiant, setIdentifiant] = useState("");
  const [password, setPassword] = useState("");
  const [domaine, setDomaine] = useState("");
  const [error, setError] = useState("");
  const [currentCard, setCurrentCard] = useState(0);
  const [particles, setParticles] = useState([]);
  const [loading, setLoading] = useState(false);

  // Info pour le register

  const [email, setEmail] = useState("");
  const [first_name, setFirst_name] = useState("");
  const [last_name, setLast_name] = useState("");
  const [username, setUsername] = useState("");
  const [company_name, setCompany_name] = useState("");
  const [registerPassword, setRegisterPassword] = useState("");
  const [confirmedPassword, setConfirmedPassword] = useState("");


  const navigate = useNavigate();

  const handleGoogleAuth = async () => {
    try {
      setLoading(true);
      // Pour l'instant, utilisons le tenant ID 1 (3wss)
      await redirectToGoogle(1);
    } catch (error) {
      console.error("Erreur Google Auth:", error);
      setError("Erreur lors de la connexion Google. Veuillez réessayer.");
      setLoading(false);
    }
  };

  const connexionCard = useRef(null);
  const inscriptionCard = useRef(null);
  const backgroundRef = useRef(null);


  // ✅ Redirection si déjà connecté
  useEffect(() => {
    const token = localStorage.getItem("token");
    if (token) {
      navigate("/dashboard");
    }
  }, [navigate]);

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    // ✅ Validation des mots de passe
    if (confirmedPassword !== registerPassword) {
      setError("Les mots de passe ne correspondent pas.");
      setLoading(false);
      return;
    }

    try {
      // ✅ Formatage correct des données pour Laravel
      const registrationData = {
        first_name,
        last_name,
        username,
        email,
        password: registerPassword,
        password_confirmation: confirmedPassword,
        company_name: company_name || `${first_name} ${last_name} Entreprise`,
      };

      const response = await axios.post("/api/auth/register", registrationData);
    
      const { token, user } = response.data;
      
      // ✅ Stockage correct dans localStorage
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
      
      navigate("/dashboard");
    
    } catch (err: unknown) {
      // ✅ Gestion d'erreur améliorée
      const axiosError = err as { response?: { status?: number; data?: { errors?: Record<string, string[]>; message?: string } } };
      if (axiosError.response?.status === 422 && axiosError.response?.data?.errors) {
        const validationErrors = axiosError.response.data.errors;
        const errorMessages = Object.values(validationErrors).flat();
        setError(errorMessages.join(", "));
      } else if (axiosError.response?.data?.message) {
        setError(axiosError.response.data.message);
      } else {
        setError("Erreur lors de l'inscription.");
      }
    } finally {
      setLoading(false);
    }
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    // setLoading(true);

    try {
      const response = await axios.post("/api/auth/login", {
        login: identifiant,
        password,
        domaine,
      });

      const { user, token } = response.data;
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
      navigate("/dashboard");

      // ✅ Utiliser la fonction handleLoginSuccess pour gérer la redirection
      const { handleLoginSuccess } = await import("@/services/authService");
      handleLoginSuccess(user, token);
      
    } catch (err: unknown) {
      const error = err as { response?: { data?: { error?: string } } };
      setError(error.response?.data?.error || "Erreur lors de la connexion.");
    } finally {
      setLoading(false);
    }
  };

  // const handleUserSelect = (email: string) => {
  //   const selected = users.find((u) => u.email === email);
  //   setCurrentUser(selected);
  //   setIdentifiant(selected?.email || selected?.username || "");
  //   setChangeUser(false);
  // };

  // const handleRemoveUser = (email: string) => {
  //   const updated = users.filter((u) => u.email !== email);
  //   localStorage.setItem("localUsers", JSON.stringify(updated));
  //   setUsers(updated);
  //   setCurrentUser(null);
  //   setIdentifiant("");
  //   setChangeUser(false);
  // };

  // Synchronisation GSAP <-> état React
  useGSAP(() => {
    if (backgroundRef.current) {
      gsap.set(backgroundRef.current, { x: "0vw" });
    }
  }, []);

  const LeftCardChange = () => {
    setCurrentCard(1);
    if (backgroundRef.current) {
      gsap.to(backgroundRef.current, {
        x: "-100vw",
        duration: 1,
        ease: "power2.inOut",
      });
    }
  };

  const RightCardChange = () => {
    setCurrentCard(0);
    if (backgroundRef.current) {
      gsap.to(backgroundRef.current, {
        x: "0vw",
        duration: 1,
        ease: "power2.inOut",
      });
    }
  };

  useEffect(() => {
    // Génère les particules une seule fois
    const generated = Array.from({ length: 20 }).map(() => ({
      left: `${Math.random() * 100}%`,
      top: `${Math.random() * 100}%`,
      duration: 3 + Math.random() * 10,
      delay: Math.random() * 1,
    }));
    setParticles(generated);
  }, []);

  return (
    <div ref={backgroundRef} className="absolute inset-0 overflow-hidden min-h-screen w-[200%] bg-gradient-to-tr from-[#0D2F4A] to-[#188994] flex items-center justify-center">
      {particles.map((p, i) => (
        <div
          key={i}
          className="absolute w-2 h-2 bg-white rounded-full opacity-30"
          style={{
            left: p.left,
            top: p.top,
            animation: `float ${p.duration}s ease-in-out infinite`,
            animationDelay: `${p.delay}s`
          }}
        />
      ))}
      <img className="absolute w-full" src="/backgrounds/Background_road.svg" />
      <div className="relative w-full overflow-hidden">
        <div className="flex transition-transform duration-500 ease-in-out">
          
          {/* Première carte - Inscription */}
          <div className="flex justify-center items-center w-screen h-screen px-0">
            <div ref={inscriptionCard} className="w-[500px] m-3">
              <div className="flex flex-col w-full bg-white/10 backdrop-blur-lg border border-white/75 rounded-3xl p-4 py-5 text-white shadow-lg">
                <button className="text-base font-extralight text-white opacity-75 mb-6 flex items-center gap-2 hover:underline">
                  <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF" opacity={0.7}><path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/></svg>
                  Retour au site principal
                </button>

                <h2 className="text-2xl font-extralight text-center mb-6">Bienvenue sur Flotteq</h2>

                <form className="space-y-4 px-10" onSubmit={handleRegister}>
                  <div className="grid grid-cols-2 gap-3">
                    <input
                      type="email"
                      placeholder="Email"
                      className="col-span-2 px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                      onChange={(e) => setEmail(e.target.value)}
                      required
                    />
                    <input
                      type="text"
                      placeholder="Prenom"
                      className="px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                      onChange={(e) => setFirst_name(e.target.value)}
                      required
                    />
                    <input
                      type="text"
                      placeholder="Nom"
                      className="px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                      onChange={(e) => setLast_name(e.target.value)}
                      required
                    />
                    <input
                      type="text"
                      placeholder="Entreprise"
                      className="px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                      onChange={(e) => setCompany_name(e.target.value)}
                      required
                    />
                    <input
                      type="text"
                      placeholder="Nom d'utilisateur"
                      className="px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                      required
                      onChange={(e) => setUsername(e.target.value)}
                    />
                    <input
                      type="password"
                      placeholder="Mot de passe"
                      className="px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    />
                    <input
                      type="password"
                      placeholder="Confirmation"
                      className="px-4 py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    />
                  </div>

                  <div className="flex justify-center w-full">
                    <div className="relative bg-gradient-to-br from-[#FFFFFF] to-[#6AB1B0] p-[1px] rounded-full w-min">
                      <button className="flex justify-center w-min self-center relative px-12 py-3 rounded-full bg-[#18A8A5] overflow-hidden">
                        <span className="text-white text-sm font-extralight">Inscription</span>
                      </button>
                    </div>
                  </div>
                </form>

                <div className="my-3 flex items-center justify-center">
                  <hr className="w-1/4 border-white/30" />
                    <span className="mx-2 text-sm text-white/70">ou</span>
                  <hr className="w-1/4 border-white/30" />
                </div>

                <div className="flex justify-center w-full">
                  <div className="relative bg-gradient-to-br from-[#FFFFFF] to-[#6AB1B0] p-[1px] rounded-full w-auto">
                    <button onClick={handleGoogleAuth} className="flex justify-center w-full self-center relative px-8 py-3 rounded-full bg-[#18A8A5] overflow-hidden">
                      <span className="text-white text-sm font-extralight">Connexion via <span className="font-semibold">Google</span></span>
                    </button>
                  </div>
                </div>

                <p className="text-base text-white/80 mt-8 text-center">
                  Vous avez déjà un compte ?{" "}
                  <button onClick={LeftCardChange} className="text-cyan-300 hover:underline">Se connecter</button>
                </p>
              </div>
            </div>
          </div>
          {/* Deuxième carte - Connexion */}
          <div className="flex justify-center items-center w-screen min-h-screen px-4">
            <div ref={connexionCard} className="flex-shrink-0 w-full max-w-[500px]">
              <div className="flex flex-col w-full bg-white/10 backdrop-blur-lg border border-white/75 rounded-3xl p-4 sm:p-8 text-white shadow-lg">
                <button className="text-base font-extralight text-white opacity-75 mb-6 sm:mb-10 flex items-center gap-2 hover:underline">
                  <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF" opacity={0.7}><path d="m313-440 224 224-57 56-320-320 320-320 57 56-224 224h487v80H313Z"/></svg>
                  Retour au site principal
                </button>

                <h2 className="text-xl sm:text-2xl font-extralight text-center mb-6 sm:mb-10">Bienvenue sur Flotteq</h2>

                <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-6 px-2 sm:px-10">
                  <div className="space-y-3">
                    <input
                      type="email"
                      placeholder="Email"
                      value={identifiant}
                      onChange={(e) => setIdentifiant(e.target.value)}
                      className="w-full px-4 py-2 sm:py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin opacity focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    />
                    <input
                      type="password"
                      placeholder="Mot de passe"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="w-full px-4 py-2 sm:py-3 rounded-xl bg-white/20 placeholder-white text-white font-thin focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    />
                  </div>

                  {error && (
                    <div className="text-red-300 text-sm text-center">{error}</div>
                  )}

                  <div className="flex justify-center w-full">
                    <div className="relative bg-gradient-to-br from-[#FFFFFF] to-[#6AB1B0] p-[1px] rounded-full w-full">
                      <button type="submit" className="flex justify-center w-full self-center relative px-6 py-2 sm:py-3 rounded-full bg-[#18A8A5] overflow-hidden">
                        <span className="text-white text-sm font-extralight">Connexion</span>
                      </button>
                    </div>
                  </div>
                </form>

                <div className="my-4 sm:my-6 flex items-center justify-center">
                  <hr className="w-1/4 border-white/30" />
                    <span className="mx-2 text-sm text-white/70">ou</span>
                  <hr className="w-1/4 border-white/30" />
                </div>

                <div className="flex justify-center w-full">
                  <div className="relative bg-gradient-to-br from-[#FFFFFF] to-[#6AB1B0] p-[1px] rounded-full w-full">
                    <button onClick={handleGoogleAuth} className="flex justify-center w-full self-center relative px-6 py-2 rounded-full bg-[#18A8A5] overflow-hidden">
                      <span className="text-white text-sm font-extralight">Connexion via <span className="font-semibold">Google</span></span>
                    </button>
                  </div>
                </div>

                <p className="text-sm text-white/80 mt-6 text-center">
                  Vous n'avez pas de compte ?{" "}
                  <button onClick={RightCardChange} className="text-cyan-300 hover:underline">S'inscrire</button>
                </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  );
};

export default Login;