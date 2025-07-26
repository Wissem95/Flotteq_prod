
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import Index from "./pages/Index";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<Index />} />
          <Route path="/fleet-status" element={<Index />} />
          <Route path="/financial-status" element={<Index />} />
          <Route path="/trouver-garage" element={<Index />} />
          <Route path="/trouver-centre-ct" element={<Index />} />
          <Route path="/trouver-assurance" element={<Index />} />
          <Route path="/vehicules" element={<Index />} />
          <Route path="/vehicules/liste" element={<Index />} />
          <Route path="/vehicules/maintenance" element={<Index />} />
          <Route path="/vehicules/historique" element={<Index />} />
          <Route path="/vehicules/achats-reventes" element={<Index />} />
          <Route path="/vehicules/statistiques" element={<Index />} />
          <Route path="/mes-reservations" element={<Index />} />
          <Route path="/mes-reservations-ct" element={<Index />} />
          <Route path="/mes-factures" element={<Index />} />
          <Route path="/mes-proces-verbaux" element={<Index />} />
          <Route path="/utilisateurs" element={<Index />} />
          <Route path="/utilisateurs/roles" element={<Index />} />
          <Route path="/notifications" element={<Index />} />
          <Route path="/parametres" element={<Index />} />
          {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
          <Route path="*" element={<NotFound />} />
        </Routes>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
