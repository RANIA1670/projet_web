<?php
/**
 * test_environmental_complete.php
 * Tests complets du module Corrélation Environnementale et Météorologique
 * 
 * Usage : Ouvrir dans le navigateur à http://localhost/web%20mardi/test_environmental_complete.php
 */

// Configuration
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/services/EnvironmentalWeatherService.php';
require_once __DIR__ . '/controllers/ForumController.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests - Corrélation Environnementale</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            margin-bottom: 30px;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        h2 {
            color: #569cd6;
            margin-top: 40px;
            margin-bottom: 15px;
        }
        .test-section {
            background: #252526;
            border-left: 4px solid #007acc;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .test-section h3 {
            color: #dcdcaa;
            margin-bottom: 10px;
        }
        .test-input {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        input, textarea {
            background: #1e1e1e;
            color: #d4d4d4;
            border: 1px solid #007acc;
            padding: 8px;
            border-radius: 4px;
            font-family: inherit;
            font-size: 12px;
        }
        button {
            background: #007acc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background: #005a9e;
        }
        .result {
            background: #1a1a1a;
            border: 1px solid #444;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        .result h4 {
            color: #4ec9b0;
            margin-bottom: 10px;
        }
        .success {
            color: #4ec9b0;
        }
        .error {
            color: #f48771;
        }
        .warning {
            color: #dcdcaa;
        }
        .info {
            color: #569cd6;
        }
        pre {
            background: #1a1a1a;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            color: #d4d4d4;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            margin: 5px 0;
        }
        .status-alert {
            background: #ff6b6b;
            color: white;
        }
        .status-active {
            background: #51cf66;
            color: white;
        }
        .spinner {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Tests - Corrélation Environnementale et Météorologique</h1>

        <div class="grid">
            
            <!-- TEST 1 : Géocoding -->
            <div class="test-section">
                <h3>📍 Test 1 : Géocoding (Nominatim)</h3>
                <div class="test-input">
                    <input type="text" id="addr1" placeholder="Adresse..." value="Paris, France">
                    <button onclick="testGeocode()">Tester</button>
                </div>
                <div id="result1" class="result" style="display:none;"></div>
            </div>

            <!-- TEST 2 : Météo -->
            <div class="test-section">
                <h3>🌤️ Test 2 : Météo (OpenWeatherMap)</h3>
                <div class="test-input">
                    <input type="number" id="lat2" placeholder="Latitude" value="48.8566" step="0.0001">
                    <input type="number" id="lng2" placeholder="Longitude" value="2.3522" step="0.0001">
                    <button onclick="testWeather()">Tester</button>
                </div>
                <div id="result2" class="result" style="display:none;"></div>
            </div>

            <!-- TEST 3 : Analyse IA -->
            <div class="test-section">
                <h3>🤖 Test 3 : Analyse IA (Contenu)</h3>
                <textarea id="content3" placeholder="Contenu à analyser..." style="width: 100%; height: 80px;">L'eau monte dangereusement, c'est une inondation terrible!</textarea>
                <button onclick="testAI()" style="margin-top: 10px; width: 100%;">Analyser</button>
                <div id="result3" class="result" style="display:none;"></div>
            </div>

            <!-- TEST 4 : Logique métier -->
            <div class="test-section">
                <h3>⚙️ Test 4 : Logique Métier</h3>
                <div class="test-input">
                    <select id="tag4" style="flex: 1;">
                        <option value="Inondation">Inondation</option>
                        <option value="Tempête">Tempête</option>
                        <option value="Sécheresse">Sécheresse</option>
                        <option value="Neige">Neige</option>
                    </select>
                    <select id="weather4" style="flex: 1;">
                        <option value="Rain">Rain (Pluie)</option>
                        <option value="Thunderstorm">Thunderstorm (Orage)</option>
                        <option value="Clear">Clear (Ciel clair)</option>
                        <option value="Clouds">Clouds (Nuageux)</option>
                    </select>
                    <button onclick="testLogic()">Tester</button>
                </div>
                <div id="result4" class="result" style="display:none;"></div>
            </div>

            <!-- TEST 5 : Workflow complet -->
            <div class="test-section">
                <h3>🚀 Test 5 : Workflow Complet</h3>
                <div class="test-input" style="flex-direction: column;">
                    <input type="text" id="addr5" placeholder="Adresse..." value="Barcelona, Spain">
                    <textarea id="content5" placeholder="Contenu..." style="width: 100%;">Un orage violent avec des inondations!</textarea>
                </div>
                <button onclick="testFullWorkflow()" style="width: 100%; margin-top: 10px;">Lancer le workflow</button>
                <div id="result5" class="result" style="display:none;"></div>
            </div>

            <!-- TEST 6 : Création de post -->
            <div class="test-section">
                <h3>📝 Test 6 : Création de post via ForumController</h3>
                <div class="test-input" style="flex-direction: column;">
                    <input type="text" id="title6" placeholder="Titre..." value="Test Post">
                    <textarea id="content6" placeholder="Contenu..." style="width: 100%;">L'eau monte partout!</textarea>
                    <input type="text" id="addr6" placeholder="Adresse..." value="London, UK">
                </div>
                <button onclick="testCreatePost()" style="width: 100%; margin-top: 10px;">Créer le post</button>
                <div id="result6" class="result" style="display:none;"></div>
            </div>

        </div>

        <!-- Section des logs -->
        <h2>📋 Logs et Messages</h2>
        <div class="result">
            <h4>Console d'exécution</h4>
            <div id="logs"></div>
        </div>

    </div>

    <script>
        function addLog(message, type = 'info') {
            const logsDiv = document.getElementById('logs');
            const time = new Date().toLocaleTimeString();
            const className = type; // success, error, warning, info
            logsDiv.innerHTML += `<div class="${className}">[${time}] ${message}</div>`;
            logsDiv.parentElement.scrollTop = logsDiv.parentElement.scrollHeight;
        }

        function displayResult(elementId, title, data, success = true) {
            const element = document.getElementById(elementId);
            element.style.display = 'block';
            let html = `<h4>${title}</h4>`;
            
            if (success) {
                html += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                addLog(`${title} - Succès`, 'success');
            } else {
                html += `<div class="error">${data}</div>`;
                addLog(`${title} - Erreur: ${data}`, 'error');
            }
            
            element.innerHTML = html;
        }

        async function testGeocode() {
            const addr = document.getElementById('addr1').value;
            if (!addr) {
                addLog('Erreur: Veuillez entrer une adresse', 'error');
                return;
            }

            addLog(`🔄 Test géocoding: "${addr}"`, 'warning');
            
            try {
                const response = await fetch('services/test_api.php?action=geocode&address=' + encodeURIComponent(addr));
                const data = await response.json();
                displayResult('result1', '📍 Résultat Géocoding', data, data.success);
            } catch (err) {
                displayResult('result1', '📍 Résultat Géocoding', err.message, false);
            }
        }

        async function testWeather() {
            const lat = document.getElementById('lat2').value;
            const lng = document.getElementById('lng2').value;
            
            if (!lat || !lng) {
                addLog('Erreur: Veuillez entrer lat/lng', 'error');
                return;
            }

            addLog(`🔄 Test météo: ${lat}, ${lng}`, 'warning');
            
            try {
                const response = await fetch(`services/test_api.php?action=weather&lat=${lat}&lng=${lng}`);
                const data = await response.json();
                displayResult('result2', '🌤️ Résultat Météo', data, data.success);
            } catch (err) {
                displayResult('result2', '🌤️ Résultat Météo', err.message, false);
            }
        }

        async function testAI() {
            const content = document.getElementById('content3').value;
            if (!content) {
                addLog('Erreur: Veuillez entrer du contenu', 'error');
                return;
            }

            addLog(`🔄 Test analyse IA...`, 'warning');
            
            try {
                const response = await fetch('services/test_api.php?action=ai&content=' + encodeURIComponent(content));
                const data = await response.json();
                displayResult('result3', '🤖 Résultat Analyse IA', data, data.success);
            } catch (err) {
                displayResult('result3', '🤖 Résultat Analyse IA', err.message, false);
            }
        }

        async function testLogic() {
            const tag = document.getElementById('tag4').value;
            const weather = document.getElementById('weather4').value;

            addLog(`🔄 Test logique métier: Tag=${tag}, Weather=${weather}`, 'warning');
            
            try {
                const response = await fetch(`services/test_api.php?action=logic&tag=${tag}&weather=${weather}`);
                const data = await response.json();
                displayResult('result4', '⚙️ Résultat Logique Métier', data, data.success);
            } catch (err) {
                displayResult('result4', '⚙️ Résultat Logique Métier', err.message, false);
            }
        }

        async function testFullWorkflow() {
            const addr = document.getElementById('addr5').value;
            const content = document.getElementById('content5').value;

            if (!addr || !content) {
                addLog('Erreur: Veuillez remplir tous les champs', 'error');
                return;
            }

            addLog(`🔄 Test workflow complet...`, 'warning');
            
            try {
                const response = await fetch('services/test_api.php?action=workflow', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ address: addr, content: content, title: 'Test' })
                });
                const data = await response.json();
                displayResult('result5', '🚀 Résultat Workflow Complet', data, data.success);
            } catch (err) {
                displayResult('result5', '🚀 Résultat Workflow Complet', err.message, false);
            }
        }

        async function testCreatePost() {
            const title = document.getElementById('title6').value;
            const content = document.getElementById('content6').value;
            const addr = document.getElementById('addr6').value;

            if (!title || !content) {
                addLog('Erreur: Titre et contenu obligatoires', 'error');
                return;
            }

            addLog(`🔄 Création de post...`, 'warning');
            
            try {
                const response = await fetch('services/test_api.php?action=create_post', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        user_id: 1,
                        title: title, 
                        content: content, 
                        address: addr
                    })
                });
                const data = await response.json();
                displayResult('result6', '📝 Résultat Création Post', data, data.success);
            } catch (err) {
                displayResult('result6', '📝 Résultat Création Post', err.message, false);
            }
        }

        // Log initial
        addLog('✅ Page de tests chargée', 'success');
        addLog('ℹ️ Remplissez les champs et cliquez sur les boutons pour tester', 'info');
    </script>
</body>
</html>
