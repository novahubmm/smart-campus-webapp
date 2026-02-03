<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Backend FCM Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin: 15px 0; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>🔥 Backend FCM Test</h1>
    
    <p><strong>This tests the backend FCM service directly (bypassing web client issues)</strong></p>
    
    <form id="fcmForm">
        <div class="form-group">
            <label for="token">FCM Token (from mobile developer):</label>
            <textarea id="token" rows="3" placeholder="Paste the real mobile FCM token here..."></textarea>
        </div>
        
        <div class="form-group">
            <label for="title">Notification Title:</label>
            <input type="text" id="title" value="Test Notification" />
        </div>
        
        <div class="form-group">
            <label for="body">Notification Body:</label>
            <input type="text" id="body" value="This is a test from Smart Campus backend!" />
        </div>
        
        <button type="submit">🚀 Send FCM Notification</button>
    </form>
    
    <div id="result"></div>
    
    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
        <h3>📱 For Mobile Developer:</h3>
        <p>The backend sends notifications in this format:</p>
        <pre style="background: #e9ecef; padding: 10px; border-radius: 4px; overflow-x: auto;">
{
  "to": "device_token",
  "notification": {
    "title": "Test Notification",
    "body": "This is a test from Smart Campus backend!"
  },
  "data": {
    "type": "announcement",
    "id": "123"
  }
}</pre>
    </div>

    <script>
        document.getElementById('fcmForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const token = document.getElementById('token').value.trim();
            const title = document.getElementById('title').value;
            const body = document.getElementById('body').value;
            const resultDiv = document.getElementById('result');
            
            if (!token) {
                resultDiv.innerHTML = '<div class="error">Please enter an FCM token</div>';
                return;
            }
            
            resultDiv.innerHTML = '<div>Sending notification...</div>';
            
            try {
                const response = await fetch('/api/backend-fcm-test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ token, title, body })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    resultDiv.innerHTML = `<div class="success">✅ ${result.message}</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">❌ ${result.message}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">❌ Error: ${error.message}</div>`;
            }
        });
    </script>
</body>
</html>