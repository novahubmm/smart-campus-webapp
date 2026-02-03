<?php
// Simple HTTPS server for FCM testing
$context = stream_context_create([
    'ssl' => [
        'local_cert' => __DIR__ . '/cert.pem',
        'local_pk' => __DIR__ . '/key.pem',
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ],
]);

$server = stream_socket_server(
    'ssl://0.0.0.0:8443',
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    $context
);

if (!$server) {
    die("Failed to create server: $errstr ($errno)\n");
}

echo "HTTPS Server running on https://10.180.162.219:8443\n";
echo "Press Ctrl+C to stop\n\n";

while (true) {
    $client = stream_socket_accept($server);
    if ($client) {
        $request = fread($client, 4096);
        
        // Simple HTTP response
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/html\r\n";
        $response .= "Connection: close\r\n\r\n";
        $response .= "<h1>HTTPS Server Running</h1>";
        $response .= "<p>Visit your Laravel app at: <a href='https://10.180.162.219:8443'>https://10.180.162.219:8443</a></p>";
        
        fwrite($client, $response);
        fclose($client);
    }
}
?>