<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un événement</title>
    <style>
        form { width: 50%; margin: auto; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px 0; }
        button { background: green; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .cancel { background: gray; color: white; padding: 10px 20px; text-decoration: none; }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Ajouter un événement</h1>
    <form method="POST">
        <label>Nom de l'événement :</label>
        <input type="text" name="name" required>
        
        <label>Description :</label>
        <textarea name="description" rows="5"></textarea>
        
        <label>Date et heure :</label>
        <input type="datetime-local" name="event_date" required>
        
        <label>Lieu :</label>
        <input type="text" name="location" required>
        
        <button type="submit">Enregistrer</button>
        <a href="index.php?controller=event&action=index" class="cancel">Annuler</a>
    </form>
</body>
</html>