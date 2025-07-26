// Clients/src/pages/Dashboard.tsx

import React from "react";
import { Label } from "@/components/ui/label";

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { 
  AlertTriangle, 
  Car, 
  CheckCircle, 
  Clock, 
  Calendar,
  ArrowRight
} from "lucide-react";
import { Button } from "@/components/ui/button";

const Dashboard: React.FC = () => {
  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <Car className="mr-2 text-flotteq-blue" size={20} />
              Véhicules
            </CardTitle>
            <CardDescription>État de votre flotte</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">24</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-green-500 font-medium">92%</span> en état de marche
            </div>
            <Progress value={92} className="h-2 mt-2" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <Calendar className="mr-2 text-flotteq-blue" size={20} />
              Contrôles techniques
            </CardTitle>
            <CardDescription>Prochaines échéances</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">5</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-amber-500 font-medium">3</span> dans le mois à venir
            </div>
            <Progress value={60} className="h-2 mt-2" />
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-lg flex items-center">
              <AlertTriangle className="mr-2 text-flotteq-blue" size={20} />
              Alertes
            </CardTitle>
            <CardDescription>Problèmes à résoudre</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold">7</div>
            <div className="mt-2 text-sm text-muted-foreground">
              <span className="text-red-500 font-medium">2</span> urgences à traiter
            </div>
            <Progress value={30} className="h-2 mt-2" />
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Entretiens à venir</CardTitle>
            <CardDescription>Planning des 7 prochains jours</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {[
                { vehicle: "Renault Clio", plate: "AB-123-CD", date: "Aujourd'hui", type: "Vidange", status: "pending" },
                { vehicle: "Peugeot 308", plate: "EF-456-GH", date: "Demain", type: "Révision complète", status: "pending" },
                { vehicle: "Citroën C3", plate: "IJ-789-KL", date: "Dans 3 jours", type: "Contrôle technique", status: "scheduled" },
              ].map((item, index) => (
                <div key={index} className="flex items-center p-3 rounded-md border border-slate-100 hover:bg-slate-50 transition-colors">
                  <div className={`h-10 w-10 rounded-full flex items-center justify-center ${item.status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-sky-100 text-sky-600'}`}>
                    {item.status === 'pending' ? <Clock size={20} /> : <Calendar size={20} />}
                  </div>
                  <div className="ml-4 flex-1">
                    <p className="font-medium">{item.vehicle}</p>
                    <p className="text-sm text-slate-500">{item.plate} • {item.type}</p>
                  </div>
                  <div className="text-sm font-medium text-slate-500">{item.date}</div>
                </div>
              ))}
              <Button variant="ghost" className="w-full border border-dashed mt-2 text-slate-500 hover:text-flotteq-blue">
                Voir tous les entretiens
                <ArrowRight className="ml-2" size={16} />
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Alertes prioritaires</CardTitle>
            <CardDescription>Nécessitant une action</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {[
                { vehicle: "Ford Focus", plate: "MN-012-OP", issue: "Défaillance système de freinage", severity: "high" },
                { vehicle: "Volkswagen Golf", plate: "QR-345-ST", issue: "Niveau huile critique", severity: "medium" },
                { vehicle: "Toyota Yaris", plate: "UV-678-WX", issue: "Contrôle technique expiré", severity: "high" },
              ].map((item, index) => (
                <div key={index} className="flex items-center p-3 rounded-md border border-slate-100 hover:bg-slate-50 transition-colors">
                  <div className={`h-10 w-10 rounded-full flex items-center justify-center ${item.severity === 'high' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'}`}>
                    <AlertTriangle size={20} />
                  </div>
                  <div className="ml-4 flex-1">
                    <p className="font-medium">{item.vehicle}</p>
                    <p className="text-sm text-slate-500">{item.plate} • {item.issue}</p>
                  </div>
                  <Button size="sm" variant="outline" className="text-xs">
                    Traiter
                  </Button>
                </div>
              ))}
              <Button variant="ghost" className="w-full border border-dashed mt-2 text-slate-500 hover:text-flotteq-blue">
                Voir toutes les alertes
                <ArrowRight className="ml-2" size={16} />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Statut de la flotte</CardTitle>
          <CardDescription>Vue d'ensemble des véhicules</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {[
              { status: "En service", count: 22, color: "bg-green-100 text-green-600", icon: <CheckCircle size={20} /> },
              { status: "Hors service", count: 2, color: "bg-red-100 text-red-600", icon: <AlertTriangle size={20} /> },
              { status: "En maintenance", count: 3, color: "bg-amber-100 text-amber-600", icon: <Clock size={20} /> },
              { status: "À inspecter", count: 5, color: "bg-sky-100 text-sky-600", icon: <Calendar size={20} /> },
            ].map((item, index) => (
              <div key={index} className="p-4 rounded-lg border border-slate-100 flex items-center">
                <div className={`h-12 w-12 rounded-full flex items-center justify-center ${item.color}`}>
                  {item.icon}
                </div>
                <div className="ml-4">
                  <div className="text-2xl font-bold">{item.count}</div>
                  <div className="text-sm text-slate-500">{item.status}</div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default Dashboard;
