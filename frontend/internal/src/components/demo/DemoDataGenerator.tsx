// DemoDataGenerator.tsx - Générateur de données de test FlotteQ

import React, { useState } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Database, Users, Building2, Car, FileText, CheckCircle, AlertTriangle, Loader2, Play, Trash2, } from "lucide-react";

interface GenerationTask {
  id: string;
  name: string;
  description: string;
  count: number;
  status: 'pending' | 'running' | 'completed' | 'error';
  progress: number;
  duration?: number;
}

const DemoDataGenerator: React.FC = () => {
  const [tasks, setTasks] = useState<GenerationTask[]>([
    {
      id: 'tenants',
      name: 'Tenants / Entreprises',
      description: 'Génère des comptes clients avec profils complets',
      count: 50,
      status: 'pending',
      progress: 0,
    },
    {
      id: 'users',
      name: 'Utilisateurs',
      description: 'Crée des utilisateurs avec rôles variés',
      count: 200,
      status: 'pending',
      progress: 0,
    },
    {
      id: 'vehicles',
      name: 'Véhicules',
      description: 'Ajoute des véhicules avec historiques',
      count: 1000,
      status: 'pending',
      progress: 0,
    },
    {
      id: 'partners',
      name: 'Partenaires',
      description: 'Génère garages, centres CT et assurances',
      count: 75,
      status: 'pending',
      progress: 0,
    },
    {
      id: 'subscriptions',
      name: 'Abonnements',
      description: 'Crée des abonnements avec historiques de paiement',
      count: 100,
      status: 'pending',
      progress: 0,
    },
    {
      id: 'support_tickets',
      name: 'Tickets Support',
      description: 'Génère des tickets avec conversations',
      count: 300,
      status: 'pending',
      progress: 0,
    },
    {
      id: 'analytics_data',
      name: 'Données Analytics',
      description: 'Crée des métriques et événements historiques',
      count: 5000,
      status: 'pending',
      progress: 0,
    },
  ]);

  const [isGenerating, setIsGenerating] = useState(false);
  const [overallProgress, setOverallProgress] = useState(0);

  const generateData = async (taskId: string) => {
    const task = tasks.find(t => t.id === taskId);
    if (!task) return;

    // Mise à jour du statut
    setTasks(prev => prev.map(t => 
      t.id === taskId ? { ...t, status: 'running', progress: 0 } : t
    ));

    const startTime = Date.now();

    // Simulation de génération avec progression
    for (let i = 0; i <= 100; i += 5) {
      await new Promise(resolve => setTimeout(resolve, 100));
      
      setTasks(prev => prev.map(t => 
        t.id === taskId ? { ...t, progress: i } : t
      ));
    }

    const endTime = Date.now();
    const duration = endTime - startTime;

    // Finalisation
    setTasks(prev => prev.map(t => 
      t.id === taskId ? { 
        ...t, 
        status: 'completed', 
        progress: 100,
        duration: Math.round(duration / 1000)
      } : t
    ));
  };

  const generateAllData = async () => {
    setIsGenerating(true);
    setOverallProgress(0);

    const totalTasks = tasks.length;
    let completedTasks = 0;

    for (const task of tasks) {
      await generateData(task.id);
      completedTasks++;
      setOverallProgress((completedTasks / totalTasks) * 100);
    }

    setIsGenerating(false);
  };

  const clearAllData = async () => {
    setTasks(prev => prev.map(task => ({
      ...task,
      status: 'pending',
      progress: 0,
      duration: undefined,
    })));
    setOverallProgress(0);
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'pending':
        return <Badge variant="outline">En attente</Badge>;
      case 'running':
        return <Badge className="bg-blue-500">En cours</Badge>;
      case 'completed':
        return <Badge className="bg-green-500">Terminé</Badge>;
      case 'error':
        return <Badge variant="destructive">Erreur</Badge>;
      default:
        return <Badge variant="outline">Inconnu</Badge>;
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'running':
        return <Loader2 className="w-4 h-4 animate-spin" />;
      case 'completed':
        return <CheckCircle className="w-4 h-4 text-green-500" />;
      case 'error':
        return <AlertTriangle className="w-4 h-4 text-red-500" />;
      default:
        return null;
    }
  };

  const getTotalCount = () => tasks.reduce((sum, task) => sum + task.count, 0);
  const getCompletedCount = () => tasks.filter(task => task.status === 'completed').reduce((sum, task) => sum + task.count, 0);

  const demoDataSets = [
    {
      name: "Dataset Minimal",
      description: "Données essentielles pour tests rapides",
      tenants: 5,
      users: 20,
      vehicles: 50,
      duration: "~2 minutes"
    },
    {
      name: "Dataset Standard",
      description: "Données complètes pour démonstrations",
      tenants: 25,
      users: 100,
      vehicles: 500,
      duration: "~10 minutes"
    },
    {
      name: "Dataset Complet",
      description: "Données volumineuses pour tests de performance",
      tenants: 100,
      users: 500,
      vehicles: 2000,
      duration: "~30 minutes"
    },
  ];

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Générateur de Données de Test</h1>
          <p className="text-gray-600">Créez des données de démonstration pour FlotteQ</p>
        </div>
        <div className="flex gap-2">
          <Button 
            variant="outline" 
            onClick={clearAllData}
            disabled={isGenerating}
            className="flex items-center gap-2"
          >
            <Trash2 className="w-4 h-4" />
            Réinitialiser
          </Button>
          <Button 
            onClick={generateAllData}
            disabled={isGenerating}
            className="flex items-center gap-2"
          >
            {isGenerating ? (
              <Loader2 className="w-4 h-4 animate-spin" />
            ) : (
              <Play className="w-4 h-4" />
            )}
            {isGenerating ? 'Génération...' : 'Générer tout'}
          </Button>
        </div>
      </div>

      {/* Progression globale */}
      {isGenerating && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Database className="w-5 h-5" />
              Progression globale
            </CardTitle>
          </CardHeader>
          <CardContent>
            <Progress value={overallProgress} className="w-full" />
            <p className="text-sm text-gray-600 mt-2">
              {Math.round(overallProgress)}% - Génération de {getCompletedCount().toLocaleString()} / {getTotalCount().toLocaleString()} enregistrements
            </p>
          </CardContent>
        </Card>
      )}

      {/* Onglets */}
      <Tabs defaultValue="generator" className="space-y-4">
        <TabsList>
          <TabsTrigger value="generator">Générateur</TabsTrigger>
          <TabsTrigger value="presets">Datasets Prédéfinis</TabsTrigger>
          <TabsTrigger value="stats">Statistiques</TabsTrigger>
        </TabsList>

        {/* Générateur */}
        <TabsContent value="generator">
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {tasks.map((task) => (
              <Card key={task.id}>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-lg flex items-center gap-2">
                      {task.id === 'tenants' && <Building2 className="w-5 h-5" />}
                      {task.id === 'users' && <Users className="w-5 h-5" />}
                      {task.id === 'vehicles' && <Car className="w-5 h-5" />}
                      {task.id === 'partners' && <Building2 className="w-5 h-5" />}
                      {task.id === 'subscriptions' && <FileText className="w-5 h-5" />}
                      {task.id === 'support_tickets' && <FileText className="w-5 h-5" />}
                      {task.id === 'analytics_data' && <Database className="w-5 h-5" />}
                      {task.name}
                    </CardTitle>
                    {getStatusIcon(task.status)}
                  </div>
                  <CardDescription>{task.description}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Quantité</span>
                    <Badge variant="outline">{task.count.toLocaleString()}</Badge>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Statut</span>
                    {getStatusBadge(task.status)}
                  </div>
                  
                  {task.status === 'running' && (
                    <div>
                      <Progress value={task.progress} className="w-full" />
                      <p className="text-xs text-gray-500 mt-1">{task.progress}%</p>
                    </div>
                  )}
                  
                  {task.status === 'completed' && task.duration && (
                    <div className="text-xs text-green-600">
                      Terminé en {task.duration}s
                    </div>
                  )}
                  
                  <Button 
                    onClick={() => generateData(task.id)}
                    disabled={isGenerating || task.status === 'running'}
                    className="w-full"
                    size="sm"
                  >
                    {task.status === 'running' ? 'En cours...' : 'Générer'}
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>

        {/* Datasets Prédéfinis */}
        <TabsContent value="presets">
          <div className="grid gap-4 md:grid-cols-3">
            {demoDataSets.map((dataset, index) => (
              <Card key={index}>
                <CardHeader>
                  <CardTitle>{dataset.name}</CardTitle>
                  <CardDescription>{dataset.description}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-3">
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span>Tenants</span>
                      <span className="font-medium">{dataset.tenants}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Utilisateurs</span>
                      <span className="font-medium">{dataset.users}</span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span>Véhicules</span>
                      <span className="font-medium">{dataset.vehicles}</span>
                    </div>
                  </div>
                  
                  <div className="pt-2 border-t">
                    <div className="text-xs text-gray-500 mb-2">
                      Durée estimée: {dataset.duration}
                    </div>
                    <Button className="w-full" size="sm">
                      Générer ce dataset
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>

        {/* Statistiques */}
        <TabsContent value="stats">
          <div className="grid gap-4 md:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle>Progression par catégorie</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {tasks.map((task) => (
                    <div key={task.id} className="flex items-center justify-between">
                      <span className="text-sm">{task.name}</span>
                      <div className="flex items-center gap-2">
                        <Progress value={task.progress} className="w-20" />
                        <span className="text-xs w-8">{task.progress}%</span>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Résumé global</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <div className="flex justify-between">
                    <span>Total d'enregistrements</span>
                    <span className="font-medium">{getTotalCount().toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Générés</span>
                    <span className="font-medium text-green-600">{getCompletedCount().toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Tâches terminées</span>
                    <span className="font-medium">
                      {tasks.filter(t => t.status === 'completed').length} / {tasks.length}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span>Temps total estimé</span>
                    <span className="font-medium">~45 minutes</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <Alert>
            <AlertTriangle className="h-4 w-4" />
            <AlertDescription>
              <strong>Note importante :</strong> Les données générées sont fictives et destinées uniquement aux tests et démonstrations. 
              Assurez-vous d'être en environnement de développement avant de lancer la génération.
            </AlertDescription>
          </Alert>
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default DemoDataGenerator; 