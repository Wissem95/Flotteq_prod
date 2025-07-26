import React, { useEffect, useState } from 'react';
import axios from '@/lib/api'; // ✅ ton instance axios bien configurée

const TestAPI = () => {
  const [status, setStatus] = useState('⏳ Test en cours...');
  const [error, setError] = useState('');

  useEffect(() => {
    const testBackend = async () => {
      try {
        const res = await axios.get('/auth/test'); // crée une route temporaire si besoin
        setStatus(`✅ Réponse du backend : ${res.data.message || 'OK'}`);
      } catch (err: any) {
        console.error('Erreur test API :', err);
        setError(err.response?.data?.error || 'Erreur de connexion');
        setStatus('❌ Le frontend ne peut pas accéder au backend.');
      }
    };

    testBackend();
  }, []);

  return (
    <div className="p-8 text-center">
      <h1 className="text-xl font-bold mb-4">Test de connexion au backend</h1>
      <p>{status}</p>
      {error && <pre className="text-red-500 mt-4">{error}</pre>}
    </div>
  );
};

export default TestAPI;

