<?php

/**
 * Script de test d'intégrité des endpoints critiques
 * Ce script vérifie que tous les endpoints principaux fonctionnent correctement
 */

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Route;

class EndpointIntegrityTest
{
    private $baseUrl;
    private $results = [];
    
    public function __construct($baseUrl = 'https://flotteq-backend-v2-production.up.railway.app')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    /**
     * Test des endpoints publics
     */
    public function testPublicEndpoints()
    {
        echo "🔍 Test des endpoints publics...\n";
        
        $endpoints = [
            '/api/health' => 'GET',
            '/api/internal/auth/health/database' => 'GET',
        ];
        
        foreach ($endpoints as $endpoint => $method) {
            $this->testEndpoint($endpoint, $method, null, 'Public');
        }
    }
    
    /**
     * Test des endpoints d'authentification
     */
    public function testAuthEndpoints()
    {
        echo "🔐 Test des endpoints d'authentification...\n";
        
        // Ces endpoints nécessitent des données mais ne devraient pas retourner 500
        $endpoints = [
            '/api/auth/login' => 'POST',
            '/api/internal/auth/login' => 'POST',
        ];
        
        foreach ($endpoints as $endpoint => $method) {
            // Test avec des données vides pour vérifier la validation (400/422 attendu)
            $this->testEndpoint($endpoint, $method, [], 'Auth', [400, 422]);
        }
    }
    
    /**
     * Test des endpoints internes (sans auth pour vérifier qu'ils retournent 401 et non 500)
     */
    public function testInternalEndpoints()
    {
        echo "🏢 Test des endpoints internes (sans auth)...\n";
        
        $endpoints = [
            '/api/internal/alerts' => 'GET',
            '/api/internal/subscriptions/stats' => 'GET',
            '/api/internal/support/statistics' => 'GET',
            '/api/internal/tenants' => 'GET',
            '/api/internal/employees' => 'GET',
        ];
        
        foreach ($endpoints as $endpoint => $method) {
            // Ces endpoints devraient retourner 401 (non autorisé) et non 500
            $this->testEndpoint($endpoint, $method, null, 'Internal', [401]);
        }
    }
    
    /**
     * Test d'un endpoint spécifique
     */
    private function testEndpoint($endpoint, $method, $data = null, $category = 'General', $expectedStatusCodes = [200, 201])
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        if ($data && ($method === 'POST' || $method === 'PUT')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $status = '❌ FAIL';
        $message = '';
        
        if ($error) {
            $message = "Erreur CURL: $error";
        } elseif (in_array($httpCode, $expectedStatusCodes)) {
            $status = '✅ PASS';
            $message = "Status: $httpCode";
        } elseif ($httpCode >= 500) {
            $status = '🚨 ERROR';
            $message = "Erreur serveur: $httpCode";
        } else {
            $status = '⚠️  WARN';
            $message = "Status inattendu: $httpCode";
        }
        
        echo sprintf("  %s %s %s - %s\n", $status, $method, $endpoint, $message);
        
        $this->results[$category][] = [
            'endpoint' => $endpoint,
            'method' => $method,
            'status' => $httpCode,
            'success' => in_array($httpCode, $expectedStatusCodes),
            'error' => $error,
        ];
        
        return $httpCode;
    }
    
    /**
     * Génère un rapport de test
     */
    public function generateReport()
    {
        echo "\n📊 RAPPORT DE TEST D'INTÉGRITÉ\n";
        echo str_repeat('=', 50) . "\n";
        
        $totalTests = 0;
        $successfulTests = 0;
        $errors = 0;
        
        foreach ($this->results as $category => $tests) {
            echo "\n📁 Catégorie: $category\n";
            
            foreach ($tests as $test) {
                $totalTests++;
                if ($test['success']) {
                    $successfulTests++;
                } elseif ($test['status'] >= 500) {
                    $errors++;
                }
            }
            
            $categorySuccess = array_sum(array_column($tests, 'success'));
            $categoryTotal = count($tests);
            echo "  Réussis: $categorySuccess/$categoryTotal\n";
        }
        
        echo "\n" . str_repeat('-', 50) . "\n";
        echo "📈 RÉSUMÉ GLOBAL:\n";
        echo "  Total des tests: $totalTests\n";
        echo "  Réussis: $successfulTests\n";
        echo "  Erreurs 500: $errors\n";
        echo "  Taux de réussite: " . round(($successfulTests / $totalTests) * 100, 1) . "%\n";
        
        if ($errors > 0) {
            echo "\n🚨 ERREURS CRITIQUES DÉTECTÉES!\n";
            echo "Des endpoints retournent des erreurs 500, ce qui indique des problèmes de serveur.\n";
        } else {
            echo "\n✅ AUCUNE ERREUR 500 DÉTECTÉE!\n";
            echo "Tous les endpoints testés fonctionnent correctement au niveau serveur.\n";
        }
    }
    
    /**
     * Exécute tous les tests
     */
    public function runAllTests()
    {
        echo "🚀 LANCEMENT DES TESTS D'INTÉGRITÉ FlotteQ\n";
        echo str_repeat('=', 50) . "\n";
        
        $this->testPublicEndpoints();
        $this->testAuthEndpoints();
        $this->testInternalEndpoints();
        
        $this->generateReport();
    }
}

// Exécution du script si appelé directement
if (php_sapi_name() === 'cli') {
    $tester = new EndpointIntegrityTest();
    $tester->runAllTests();
}