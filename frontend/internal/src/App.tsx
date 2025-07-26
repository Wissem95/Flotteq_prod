// 📁 frontend/internal/src/App.tsx
import React from "react";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import AdminRoutes from "./pages/admin/AdminRoutes";

const App: React.FC = () => (
  <BrowserRouter>
    <Routes>
      {/* Toutes les routes admin (dashboard, employes, tools…) */}
      <Route path="/*" element={<AdminRoutes />} />
      {/* Pour être sûr qu’on n’atterrisse jamais sur rien */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  </BrowserRouter>
);

export default App;

