<?php
// encryption-tool.php - Application ARG pour dev_alpha
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Encryption Tool</title>
    <style>
        body { font-family: monospace; background: #0d1117; color: #c9d1d9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        textarea, input { width: 100%; background: #161b22; color: white; border: 1px solid #30363d; padding: 10px; }
        button { background: #238636; color: white; border: none; padding: 10px 20px; cursor: pointer; }
        .hidden { display: none; }
        .clue { background: #fff8c5; color: #d4a72c; padding: 10px; margin: 10px 0; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Alpha's Encryption Tool</h1>
        <p><em>"Simple XOR encryption for testing purposes"</em></p>
        
        <div>
            <label>Text to encrypt:</label>
            <textarea id="input" rows="4">Hello World</textarea>
        </div>
        
        <div>
            <label>Key:</label>
            <input type="text" id="key" value="alpha-key">
        </div>
        
        <button onclick="encrypt()">Encrypt</button>
        <button onclick="decrypt()">Decrypt</button>
        
        <div>
            <label>Result:</label>
            <textarea id="output" rows="4" readonly></textarea>
        </div>
        
        <!-- Indice caché -->
        <div class="clue" id="clue" style="display: none;">
            <strong>🔍 Hidden Message Found:</strong> 
            <p>When key = "beta-access": The secret is "gamma-watch-789"</p>
            <p>Try entering this in the search bar!</p>
        </div>
        
        <!-- Mode expert caché -->
        <div style="margin-top: 20px; text-align: center;">
            <a href="javascript:void(0)" onclick="showExpert()" style="color: #58a6ff; font-size: 12px;">
                Advanced Mode
            </a>
            <div id="expert" class="hidden">
                <h3>Expert Console</h3>
                <pre style="background: #161b22; padding: 10px;">
System Logs:
[ERROR] Unauthorized access detected: User "beta" 
[INFO] Next target: gamma
[DEBUG] Security token: alpha-secure-456</pre>
                <p><small>Hint: Check the XOR function in repository files</small></p>
            </div>
        </div>
    </div>
    
    <script>
        function xorEncrypt(text, key) {
            let result = '';
            for (let i = 0; i < text.length; i++) {
                result += String.fromCharCode(text.charCodeAt(i) ^ key.charCodeAt(i % key.length));
            }
            return btoa(result);
        }
        
        function xorDecrypt(encrypted, key) {
            try {
                const text = atob(encrypted);
                let result = '';
                for (let i = 0; i < text.length; i++) {
                    result += String.fromCharCode(text.charCodeAt(i) ^ key.charCodeAt(i % key.length));
                }
                return result;
            } catch {
                return "Invalid encrypted text";
            }
        }
        
        function encrypt() {
            const input = document.getElementById('input').value;
            const key = document.getElementById('key').value;
            const output = xorEncrypt(input, key);
            document.getElementById('output').value = output;
            
            // Révéler l'indice si la clé spéciale est utilisée
            if (key === 'beta-access') {
                document.getElementById('clue').style.display = 'block';
            }
        }
        
        function decrypt() {
            const input = document.getElementById('input').value;
            const key = document.getElementById('key').value;
            const output = xorDecrypt(input, key);
            document.getElementById('output').value = output;
        }
        
        function showExpert() {
            document.getElementById('expert').classList.toggle('hidden');
        }
        
        // Easter egg dans la console
        console.log("%c🔐 ARG Clue: Try key 'beta-access' in the encryption tool", 
                   "color: #ff6b6b; font-size: 14px; font-weight: bold;");
    </script>
</body>
</html>