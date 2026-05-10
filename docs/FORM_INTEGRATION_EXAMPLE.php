<?php
/**
 * Exemple d'intégration du formulaire de création de post
 * avec support pour l'adresse et la corrélation environnementale
 * 
 * INTÉGRATION : Copier/adapter ce code dans votre vue create_post.php
 */

// Supposons que vous avez une vue avec un formulaire
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un nouveau post</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input[type="text"],
        textarea,
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        textarea:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        textarea {
            resize: vertical;
            min-height: 200px;
        }
        .helper-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #1565c0;
        }
        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        button:active {
            transform: translateY(0);
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Créer un nouveau post</h1>

        <!-- Section d'information sur la corrélation environnementale -->
        <div class="info-box">
            <strong>🌍 Détection automatique de corrélation environnementale</strong>
            Si vous indiquez une adresse, le système va :
            <br/>1. 📍 Géocoder l'adresse (localisation)
            <br/>2. 🌤️ Récupérer la météo actuelle
            <br/>3> 🤖 Analyser votre texte pour détecter des tags (Inondation, Tempête, etc.)
            <br/>4. 🚨 Assigner automatiquement un statut (Alerte Climatique si corrélation détectée)
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            
            <!-- Section : Informations de base -->
            <div class="section-title">ℹ️ Informations de base</div>

            <div class="form-group">
                <label for="title">Titre du post *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    required 
                    placeholder="Ex: Alerte inondation à Paris"
                    maxlength="150"
                >
                <div class="helper-text">Maximum 150 caractères</div>
            </div>

            <div class="form-group">
                <label for="content">Contenu du post *</label>
                <textarea 
                    id="content" 
                    name="content" 
                    required 
                    placeholder="Décrivez votre post en détail. Le système analysera automatiquement le contenu pour détecter des thèmes environnementaux..."
                ></textarea>
                <div class="helper-text">Minimum 10 caractères recommandé</div>
            </div>

            <!-- Section : Localisation et météo -->
            <div class="section-title">📍 Localisation (optionnel - pour la détection environnementale)</div>

            <div class="form-group">
                <label for="address">Adresse ou Lieu</label>
                <input 
                    type="text" 
                    id="address" 
                    name="address" 
                    placeholder="Ex: 5 Avenue des Champs-Élysées, Paris, France"
                >
                <div class="helper-text">
                    L'adresse sera géocodée via OpenStreetMap Nominatim pour récupérer les coordonnées et la météo.
                </div>
            </div>

            <!-- Section : Métadonnées (affichage informatif) -->
            <div class="section-title">ℹ️ Données calculées (affichage après soumission)</div>
            <div class="info-box" style="background: #f5f5f5; border-left-color: #999;">
                <strong>Les données suivantes seront calculées automatiquement :</strong>
                <br/>• Latitude / Longitude (depuis l'adresse)
                <br/>• Condition météo actuelle (pluie, ciel clair, etc.)
                <br/>• Tag IA détecté (Inondation, Tempête, Sécheresse, etc.)
                <br/>• Statut du post (Actif ou Alerte Climatique)
                <br/><br/>
                <em>Note : Laissez l'adresse vide pour une création simple sans analyse environnementale.</em>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" name="create_post" value="1">
                ✨ Créer le post
            </button>

        </form>
    </div>

    <!-- Script JavaScript optionnel pour validation côté client -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!title || !content) {
                e.preventDefault();
                alert('❌ Le titre et le contenu sont obligatoires.');
                return false;
            }

            if (content.length < 10) {
                e.preventDefault();
                alert('❌ Le contenu doit contenir au minimum 10 caractères.');
                return false;
            }

            // Afficher un message de chargement
            alert('⏳ Traitement en cours... Cela peut prendre quelques secondes pour analyser l\'adresse et la météo.');
        });
    </script>
</body>
</html>

<?php
/**
 * CODE À INSÉRER DANS VOTRE LOGIQUE DE FORMULAIRE (create_post.php)
 * 
 * Si vous recevez une soumission POST avec create_post = 1 :
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    require_once __DIR__ . '/controllers/ForumController.php';

    $userId = $_SESSION['user_id'] ?? 1;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $address = trim($_POST['address'] ?? '');  // ← NOUVEAU PARAMÈTRE

    $controller = new ForumController();
    
    // Appel au contrôleur avec support pour l'adresse
    // Le contrôleur va automatiquement traiter la corrélation environnementale
    $success = $controller->createPost(
        $userId,
        $title,
        $content,
        null,  // $lat (peut être pré-rempli)
        null,  // $lng (peut être pré-rempli)
        $address  // ← NOUVEAU PARAMÈTRE pour la corrélation environnementale
    );

    if ($success) {
        echo "<div style='color: green; font-weight: bold;'>✅ Post créé avec succès!</div>";
        echo "
            <p>Le post a été créé et le système a automatiqu ement :</p>
            <ul>
                <li>📍 Géocodé l'adresse si fournie</li>
                <li>🌤️ Récupéré les données météo</li>
                <li>🤖 Analysé le contenu pour détecter des tags</li>
                <li>🚨 Assigné un statut approprié</li>
            </ul>
        ";
        // Redirection optionnelle après succès
        // header('Location: index.php?page=post&id=' . $postId);
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Erreur lors de la création du post.</div>";
    }
}
?>
