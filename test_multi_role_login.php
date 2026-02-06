<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Api\V1\UnifiedAuthController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

echo "🧪 Testing Multi-Role Login API\n";
echo "================================\n\n";

// Create a mock request
$request = Request::create('/api/v1/auth/login', 'POST', [
    'login' => 'konyeinchan@smartcampusedu.com',
    'password' => 'password',
    'device_name' => 'Test Device',
]);

$request->headers->set('Accept', 'application/json');
$request->headers->set('Content-Type', 'application/json');

// Create controller instance
$controller = app(UnifiedAuthController::class);

// Create LoginRequest instance
$loginRequest = LoginRequest::createFrom($request);

try {
    // Call the login method
    $response = $controller->login($loginRequest);
    $data = json_decode($response->getContent(), true);
    
    echo "✅ Login Successful!\n\n";
    
    if (isset($data['data'])) {
        $responseData = $data['data'];
        
        echo "📊 Response Analysis:\n";
        echo "-------------------\n";
        echo "User Type: " . ($responseData['user_type'] ?? 'N/A') . "\n";
        echo "Available Roles: " . (isset($responseData['available_roles']) ? json_encode($responseData['available_roles']) : 'Not present') . "\n";
        echo "Has Tokens Object: " . (isset($responseData['tokens']) ? 'Yes' : 'No') . "\n";
        
        if (isset($responseData['tokens'])) {
            echo "\n🔑 Tokens:\n";
            foreach ($responseData['tokens'] as $role => $token) {
                echo "  - $role: " . substr($token, 0, 20) . "...\n";
            }
        }
        
        if (isset($responseData['user_data'])) {
            echo "\n👤 User Data:\n";
            foreach ($responseData['user_data'] as $role => $userData) {
                echo "  - $role: Profile data present\n";
            }
        }
        
        echo "\n✅ Multi-Role Response Format: " . (isset($responseData['available_roles']) && isset($responseData['tokens']) ? 'CORRECT' : 'INCORRECT') . "\n";
        
    } else {
        echo "❌ No data in response\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n================================\n";
echo "Test Complete!\n";
