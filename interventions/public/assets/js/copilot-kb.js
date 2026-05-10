/**
 * CityZen Copilot — Knowledge Base
 * Local JSON data for the rule-based assistant
 */
const CopilotKB = {

    // Category keywords for form auto-assist
    categories: {
        'Voirie & Routes': {
            keywords: ['route','voirie','trou','nid de poule','chaussée','trottoir','bitume','asphalte','goudron','fissure','dos-d\'âne','ralentisseur','passage piéton','carrefour','intersection','rond-point'],
            icon: 'fa-road', color: '#E67E22', id: 1
        },
        'Éclairage Public': {
            keywords: ['lampadaire','éclairage','lumière','ampoule','lampe','sombre','nuit','obscurité','panne lumière','poteau électrique','réverbère'],
            icon: 'fa-lightbulb', color: '#F1C40F', id: 2
        },
        'Espaces Verts': {
            keywords: ['arbre','parc','jardin','pelouse','gazon','haie','branche','feuille','plante','végétation','espace vert','taille','élagage','fleur'],
            icon: 'fa-leaf', color: '#27AE60', id: 3
        },
        'Déchets & Propreté': {
            keywords: ['déchet','poubelle','ordure','saleté','propreté','dépôt sauvage','encombrant','nettoyage','balayage','recyclage','benne','conteneur','pollution'],
            icon: 'fa-trash', color: '#8E44AD', id: 4
        },
        'Eau & Assainissement': {
            keywords: ['eau','fuite','canalisation','égout','inondation','assainissement','plomberie','robinet','bouche','regard','conduite','tuyau','aqua'],
            icon: 'fa-tint', color: '#2980B9', id: 5
        },
        'Sécurité': {
            keywords: ['sécurité','danger','accident','risque','vandalisme','vol','agression','insécurité','caméra','surveillance','police','barrière','clôture'],
            icon: 'fa-shield-alt', color: '#E74C3C', id: 6
        },
        'Bâtiments Publics': {
            keywords: ['bâtiment','immeuble','mur','façade','toiture','fenêtre','porte','escalier','ascenseur','mairie','école','bibliothèque','administration'],
            icon: 'fa-building', color: '#2C3E50', id: 7
        },
        'Transports': {
            keywords: ['bus','transport','arrêt','gare','train','tramway','métro','taxi','parking','stationnement','circulation','embouteillage','feu rouge','signalisation'],
            icon: 'fa-bus', color: '#16A085', id: 8
        },
        'Mobilier Urbain': {
            keywords: ['banc','poubelle','abribus','panneau','affichage','fontaine','jeux','balançoire','toboggan','clôture','grille','borne','mobilier'],
            icon: 'fa-chair', color: '#D35400', id: 9
        }
    },

    // Priority keywords
    priority: {
        urgente: ['urgent','urgence','danger','dangereux','blessure','blessé','accident','grave','critique','immédiat','vie','mort','risque mortel','effondrement','incendie'],
        haute: ['important','rapide','vite','dépêcher','détériore','empire','aggrave','sérieux','préoccupant','gros','énorme'],
        moyenne: ['gênant','problème','souci','signaler','remarqué','constaté','observé'],
        faible: ['mineur','petit','léger','esthétique','cosmétique','éventuel','quand possible','pas urgent']
    },

    // Status explanations
    statuses: {
        nouveau: { label: 'Nouveau', emoji: '🔵', desc: 'Votre signalement a été reçu et est en attente de traitement par nos équipes.' },
        en_attente: { label: 'En attente', emoji: '⏳', desc: 'Votre signalement est en cours d\'analyse. Un technicien sera bientôt assigné.' },
        en_cours: { label: 'En cours', emoji: '🔧', desc: 'Un technicien travaille activement sur votre signalement.' },
        resolu: { label: 'Résolu', emoji: '✅', desc: 'Le problème a été corrigé ! N\'hésitez pas à vérifier sur place.' },
        ferme: { label: 'Fermé', emoji: '📁', desc: 'Ce dossier est clôturé. Si le problème persiste, créez un nouveau signalement.' },
        planifiee: { label: 'Planifiée', emoji: '📅', desc: 'L\'intervention est programmée et sera effectuée à la date prévue.' },
        terminee: { label: 'Terminée', emoji: '✅', desc: 'L\'intervention est terminée avec succès.' },
        annulee: { label: 'Annulée', emoji: '❌', desc: 'L\'intervention a été annulée. Contactez-nous pour plus d\'informations.' }
    },

    // FAQ entries: [keywords[], question, answer]
    faq: [
        // =============== SIGNALEMENTS ===============
        [['signaler','créer','nouveau','problème','comment signaler','faire un signalement','déclarer'],
         'Comment signaler un problème ?',
         '📝 <b>Étape par étape :</b><br>1️⃣ Cliquez sur <b>"Signalements"</b> → <b>"Créer"</b> dans la barre de navigation<br>2️⃣ Entrez un titre décrivant le problème<br>3️⃣ Sélectionnez la catégorie (Voirie, Éclairage, etc.)<br>4️⃣ Décrivez le problème en détail<br>5️⃣ Indiquez la localisation (clic sur "Me localiser" ou saisie manuelle)<br>6️⃣ Uploadez une photo si possible<br>7️⃣ Définissez le niveau de priorité<br>8️⃣ Cliquez sur <b>"Soumettre"</b><br><br>✅ Votre signalement sera traité sous peu !'],
        
        [['suivi','suivre','référence','tracking','où en est','mon signalement','l\'état'],
         'Comment suivre mon signalement ?',
         '🔍 <b>Deux façons de suivre :</b><br><br><b>1. Si vous êtes connecté :</b><br>• Cliquez sur <b>"Suivi"</b> dans la barre de navigation<br>• Votre liste de signalements s\'affiche<br>• Cliquez sur l\'un d\'eux pour voir les détails<br><br><b>2. Si vous n\'êtes pas connecté :</b><br>• Vous pouvez cependant voir votre signalement via sa référence (donnée lors de la création)<br>• Une mini-carte et l\'historique s\'affichent<br><br>🔔 Vous serez aussi notifié par email de chaque mise à jour !'],
        
        [['carte','map','localisation','voir','géographique','tous les signalements','global'],
         'Comment voir la carte des signalements ?',
         '🗺️ <b>Consultez la carte interactive :</b><br>1. Cliquez sur <b>"Carte"</b> dans la navigation<br>2. Vous verrez tous les signalements avec des marqueurs colorés<br><br>🎨 <b>Légende des couleurs :</b><br>🔴 Urgent (rouge)<br>🟠 Haute priorité (orange)<br>🟡 Priorité moyenne (jaune)<br>🟢 Priorité faible (vert)<br><br>💡 Cliquez sur un marqueur pour voir les détails complets du signalement.'],
        
        [['inscription','inscrire','compte','créer compte','register','m\'enregistrer'],
         'Comment créer un compte ?',
         '👤 <b>Créez votre compte en 2 minutes :</b><br>1. Cliquez sur <b>"Connexion"</b> en haut à droite<br>2. Cliquez sur <b>"S\'inscrire"</b> ou <b>"Créer un compte"</b><br>3. Remplissez le formulaire :<br>   • Nom et Prénom<br>   • Email (vous recevrez les confirmations ici)<br>   • Mot de passe sécurisé<br>   • Téléphone (optionnel)<br>4. Acceptez les conditions<br>5. Cliquez sur <b>"Créer mon compte"</b><br><br>✅ Vous pouvez maintenant vous connecter et créer des signalements !'],
        
        [['connexion','connecter','login','mot de passe','se connecter','s\'identifier'],
         'Comment me connecter ?',
         '🔐 <b>Se connecter à CityZen :</b><br>1. Cliquez sur <b>"Connexion"</b> en haut à droite<br>2. Entrez votre email et mot de passe<br>3. Cliquez sur <b>"Se connecter"</b><br><br>❓ <b>Problème de connexion ?</b><br>• Vérifiez que votre email et mot de passe sont corrects<br>• Assurez-vous que CAPS LOCK n\'est pas activé<br>• Si vous avez oublié votre mot de passe, contactez l\'administration<br>• Vous devez être enregistré d\'abord (voir "Comment créer un compte?")'],
        
        [['catégorie','type','sorte','quel type','types de signalement'],
         'Quelles catégories de problèmes puis-je signaler ?',
         '📋 <b>Les 9 catégories disponibles :</b><br><br>🛣️ <b>Voirie & Routes</b> — Trous, nids de poule, fissures, passage piéton<br>💡 <b>Éclairage Public</b> — Lampadaires cassés, lumières faibles<br>🌳 <b>Espaces Verts</b> — Parcs, arbres, pelouse, maintenance<br>🗑️ <b>Déchets & Propreté</b> — Ordures, dépôts sauvages, nettoyage<br>💧 <b>Eau & Assainissement</b> — Fuites, inondations, canalisations<br>🛡️ <b>Sécurité</b> — Vandalisme, accidents, dangers<br>🏢 <b>Bâtiments Publics</b> — Dégâts, façades, toitures<br>🚌 <b>Transports</b> — Arrêts de bus, signalisation, stationnement<br>🪑 <b>Mobilier Urbain</b> — Bancs cassés, fontaines, jeux<br><br>💡 Conseil : Choisissez la catégorie la plus proche de votre problème !'],
        
        [['priorité','urgent','niveau','gravité','importance','niveau de priorité'],
         'Quels sont les niveaux de priorité ?',
         '⏰ <b>Il y a 4 niveaux de priorité :</b><br><br>🟢 <b>Faible</b><br>• Problèmes mineurs, sans danger<br>• Délai : 2-4 semaines<br>• Exemple : Banc cassé, peinture écaillée<br><br>🟡 <b>Moyenne</b><br>• Gêne modérée pour la vie quotidienne<br>• Délai : 1-2 semaines<br>• Exemple : Lampadaire cassé, herbes hautes<br><br>🟠 <b>Haute</b><br>• Problème sérieux nécessitant action rapide<br>• Délai : 3-5 jours<br>• Exemple : Trou dangereux dans la route<br><br>🔴 <b>Urgente</b><br>• Danger immédiat pour la sécurité<br>• Délai : 24-48h<br>• Exemple : Effondrement, blessure, risque d\'accident<br><br>💡 Choisissez le bon niveau pour que votre signalement soit traité rapidement !'],
        
        [['délai','temps','combien','durée','quand','résolu','combien de temps'],
         'Quel est le délai de traitement ?',
         '⏱️ <b>Délais selon la priorité :</b><br><br>🔴 <b>Urgente :</b> 24-48h ⚡<br>🟠 <b>Haute :</b> 3-5 jours<br>🟡 <b>Moyenne :</b> 1-2 semaines<br>🟢 <b>Faible :</b> 2-4 semaines<br><br>⚠️ <b>Important :</b><br>• Ces délais sont <b>indicatifs</b><br>• Les interventions d\'urgence sont prioritaires<br>• Vous serez notifié de chaque étape<br>• Plus vous donnez de détails, plus vite le problème est résolu !'],
        
        [['photo','image','pièce jointe','fichier','upload','ajouter photo'],
         'Puis-je ajouter une photo à mon signalement ?',
         '📸 <b>Oui, absolument !</b><br><br><b>Format et taille :</b><br>• Formats acceptés : JPG, PNG, GIF, WebP<br>• Taille maximale : 5 Mo<br>• Résolution : 1920x1080 recommandée<br><br><b>Pourquoi une photo ?</b><br>• Les techniciens voient exactement le problème<br>• Accélère le diagnostic et l\'intervention<br>• Preuves du avant/après<br><br><b>Conseils :</b><br>• Prenez plusieurs angles si possible<br>• Assurez-vous que l\'image est claire<br>• Une photo = traitement 2x plus rapide !'],
        
        [['géolocalisation','localiser','gps','position','adresse automatique','coordonnées','où'],
         'Comment utiliser la géolocalisation ?',
         '📍 <b>2 façons de localiser votre signalement :</b><br><br><b>1. Géolocalisation automatique (GPS) :</b><br>• Cliquez sur le bouton <b>"Me localiser"</b> (icône 📍)<br>• Acceptez la permission de localisation<br>• Votre position s\'affiche automatiquement<br>• Une mini-carte confirme votre localisation<br><br><b>2. Saisie manuelle :</b><br>• Tapez l\'adresse dans le champ<br>• Des suggestions apparaissent<br>• Sélectionnez l\'adresse correcte<br>• La carte se met à jour<br><br>💡 <b>Conseil :</b> La géolocalisation précise aide les équipes à trouver rapidement le lieu du problème !'],
        
        // =============== INTERVENTIONS ===============
        [['intervention','demande','technicien','réparation','demander','créer intervention'],
         'Comment demander une intervention ?',
         '🔧 <b>Créer une demande d\'intervention :</b><br><br>1️⃣ Cliquez sur <b>"Interventions"</b> → <b>"Nouvelle demande"</b><br>2️⃣ Sélectionnez le type d\'intervention<br>3️⃣ Décrivez ce qui doit être fait<br>4️⃣ Indiquez la localisation<br>5️⃣ Choisissez le niveau d\'urgence<br>6️⃣ Sélectionnez une date préférée<br>7️⃣ Cliquez sur <b>"Demander une intervention"</b><br><br>✅ Un technicien sera assigné et vous contactera !'],
        
        [['intervention en cours','modifier intervention','annuler intervention','changer intervention'],
         'Comment modifier ou annuler une intervention ?',
         '✏️ <b>Gérer vos interventions :</b><br><br><b>Pour modifier :</b><br>1. Allez dans <b>"Interventions"</b><br>2. Cliquez sur l\'intervention à modifier<br>3. Si le statut est "Planifiée", vous pouvez la modifier<br>4. Changez les détails et sauvegardez<br><br><b>Pour annuler :</b><br>1. Allez dans <b>"Interventions"</b><br>2. Cliquez sur l\'intervention<br>3. Cliquez sur <b>"Annuler l\'intervention"</b><br>4. Confirmez l\'annulation<br><br>⚠️ <b>Important :</b> Une intervention "En cours" ne peut pas être modifiée (contactez l\'admin)'],
        
        [['technicien assigné','qui va faire','responsable','assignation'],
         'Comment sait-on qui va faire l\'intervention ?',
         '👨‍🔧 <b>Attribution des techniciens :</b><br><br>• Un technicien est automatiquement assigné à votre intervention<br>• Vous recevrez un email avec ses coordonnées<br>• Son profil affiche son domaine de compétence<br>• Vous pouvez le contacter directement<br><br>📧 <b>Information fournie :</b><br>• Nom et prénom du technicien<br>• Email de contact<br>• Numéro de téléphone<br>• Domaines de spécialité<br><br>💡 Conseil : Contactez le technicien avant sa visite pour préciser les détails !'],
        
        [['historique','versions','modifications','changement statut','log'],
         'Comment voir l\'historique des interventions ?',
         '📊 <b>Consultez l\'historique complet :</b><br><br>1. Allez dans <b>"Interventions"</b><br>2. Cliquez sur l\'intervention qui vous intéresse<br>3. Descendez à la section <b>"Historique"</b><br><br>ℹ️ <b>Vous verrez :</b><br>• Chaque changement de statut<br>• Les dates et heures<br>• Les actions effectuées<br>• Les notes des techniciens<br>• Les modifications de dates<br><br>💡 Cet historique vous aide à suivre la progression !'],
        
        // =============== NOTIFICATIONS ===============
        [['notification','alerte','cloche','bell','notifié','alerté','nouveau message'],
         'Comment fonctionnent les notifications ?',
         '🔔 <b>Système de notifications :</b><br><br><b>Vous êtes notifié quand :</b><br>📧 Nouveau signalement créé<br>📧 Changement de statut<br>📧 Technicien assigné<br>📧 Intervention planifiée<br>📧 Mise à jour de priorité<br><br><b>Moyens de notification :</b><br>• 🔔 Badge sur l\'icône cloche (si connecté)<br>• 📧 Email (toujours)<br>• 💬 Centre de notifications (page dédiée)<br><br><b>Consulter les notifications :</b><br>1. Cliquez sur l\'icône 🔔 en haut à droite<br>2. Vous voyez vos notifications non lues<br>3. Cliquez pour voir les détails<br><br>⚙️ Vous pouvez gérer vos préférences dans votre profil'],
        
        // =============== STATUTS ===============
        [['statut','état','status','nouveau','en attente','en cours','résolu','fermé'],
         'Quels sont les statuts de signalement ?',
         '📊 <b>Les 5 statuts d\'un signalement :</b><br><br>🔵 <b>Nouveau</b><br>• Votre signalement vient d\'être créé<br>• En attente de première évaluation<br><br>⏳ <b>En attente</b><br>• Évaluation en cours<br>• Un technicien sera assigné bientôt<br><br>🔧 <b>En cours</b><br>• Intervention active sur le terrain<br>• Le problème est en train d\'être résolu<br><br>✅ <b>Résolu</b><br>• Le problème a été corrigé<br>• Vérifiez le résultat sur place<br><br>📁 <b>Fermé</b><br>• Dossier complètement clôturé<br>• Pour les mêmes problèmes, créez un nouveau signalement'],
        
        [['statut intervention','planning','planifiée','terminée','annulée'],
         'Quels sont les statuts d\'intervention ?',
         '📋 <b>Les 4 statuts d\'une intervention :</b><br><br>📅 <b>Planifiée</b><br>• Intervention programmée à une date précise<br>• Vous serez prévenu avant<br>• Vous pouvez la modifier à ce stade<br><br>🔧 <b>En cours</b><br>• Le technicien travaille actuellement<br>• Ne peut pas être modifiée<br>• Consultez les notes en temps réel<br><br>✅ <b>Terminée</b><br>• Intervention complètement finalisée<br>• Le problème a été résolu<br>• Vous recevrez un rapport<br><br>❌ <b>Annulée</b><br>• L\'intervention a été annulée<br>• Raison expliquée dans les notes<br>• Vous pouvez en demander une nouvelle'],
        
        // =============== COMPTE & SÉCURITÉ ===============
        [['mot de passe oublié','réinitialiser','reset','lost password'],
         'J\'ai oublié mon mot de passe, que faire ?',
         '🔐 <b>Réinitialiser votre mot de passe :</b><br><br>1. Sur la page de connexion, cliquez sur <b>"Mot de passe oublié ?"</b><br>2. Entrez votre email<br>3. Vérifiez votre email (vérifiez les spams)<br>4. Cliquez sur le lien de réinitialisation<br>5. Entrez un nouveau mot de passe sécurisé<br>6. Confirmez et connectez-vous<br><br>⚠️ Si vous ne recevez pas d\'email :<br>• Vérifiez que votre email est correct<br>• Attendez 5 minutes<br>• Vérifiez le dossier des spams<br>• Contactez l\'administration'],
        
        [['changer mot de passe','mise à jour sécurité','paramètres mot de passe'],
         'Comment changer mon mot de passe ?',
         '🔐 <b>Changer votre mot de passe :</b><br><br>1. Connectez-vous à votre compte<br>2. Cliquez sur votre profil (en haut à droite)<br>3. Allez dans <b>"Paramètres"</b> ou <b>"Sécurité"</b><br>4. Sélectionnez <b>"Changer le mot de passe"</b><br>5. Entrez votre ancien mot de passe<br>6. Entrez le nouveau mot de passe (2x)<br>7. Cliquez sur <b>"Valider"</b><br><br>💡 <b>Conseil :</b><br>• Utilisez au moins 8 caractères<br>• Mélangez majuscules, minuscules, chiffres<br>• Changez régulièrement votre mot de passe'],
        
        [['supprimer compte','désactiver','fermer compte','quitter'],
         'Comment supprimer mon compte ?',
         '⚠️ <b>Suppression de compte :</b><br><br>1. Connectez-vous<br>2. Allez dans <b>"Paramètres"</b> → <b>"Compte"</b><br>3. Cliquez sur <b>"Supprimer mon compte"</b><br>4. Confirmez la suppression<br>5. Entrez votre mot de passe pour confirmer<br><br>❌ <b>Attention :</b><br>• Cette action est irréversible<br>• Tous vos signalements seront anonymisés<br>• Vos données seront supprimées en 30 jours<br>• Vous ne pourrez pas vous reconnecter<br><br>💡 Si vous avez des questions avant de supprimer, contactez-nous !'],
        
        // =============== CONTACT & SUPPORT ===============
        [['contact','joindre','téléphone','email','adresse','horaire','support'],
         'Comment contacter CityZen ?',
         '📞 <b>Nous contacter :</b><br><br>📍 <b>Adresse :</b><br>15 Avenue Habib Bourguiba<br>Tunis, 1000, Tunisie<br><br>📧 <b>Email :</b><br>contact@cityzen.tn<br><br>📞 <b>Téléphone :</b><br>+216 71 000 001<br><br>🕐 <b>Heures d\'ouverture :</b><br>Lundi - Vendredi : 08h00 - 17h00<br>Samedi - Dimanche : Fermé<br><br>💬 Ou utilisez notre <a href="javascript:void(0)" onclick="window.location.href=window.COPILOT_APP_URL+\'/contact\'">formulaire de contact</a> en ligne !'],
        
        // =============== À PROPOS ===============
        [['cityzen','c\'est quoi','à propos','plateforme','application','qu\'est-ce'],
         'C\'est quoi CityZen ?',
         '🏙️ <b>CityZen — Plateforme Smart City</b><br><br><b>Mission :</b><br>Connecter les citoyens et les services municipaux pour une gestion rapide et transparente des problèmes urbains.<br><br><b>Fonctionnalités principales :</b><br>✅ Signaler les problèmes en 2 minutes<br>✅ Suivre en temps réel<br>✅ Carte interactive des problèmes<br>✅ Notifications automatiques<br>✅ Assignation intelligente des techniciens<br><br><b>Avantages :</b><br>🌍 Ville plus propre et sûre<br>⚡ Interventions plus rapides<br>📊 Transparence totale<br>👥 Engagement citoyens<br><br>Nous modernisons la gestion urbaine !'],
        
        [['liste','signalements','tous','voir','afficher','donne','consulter','mes signalements'],
         'Comment voir la liste de tous les signalements ?',
         '📋 <b>Consulter les signalements :</b><br><br><b>1. Voir tous les signalements :</b><br>• Cliquez sur <b>"Signalements"</b> dans la barre de navigation<br>• Vous verrez la liste complète de tous les signalements<br>• Utilisez les filtres pour affiner votre recherche :<br>   🔍 Par statut (Nouveau, En attente, En cours, etc.)<br>   🔍 Par priorité (Faible, Moyenne, Haute, Urgente)<br>   🔍 Par catégorie (Voirie, Éclairage, etc.)<br>   🔍 Par mots-clés (recherche textuelle)<br><br><b>2. Voir vos signalements personnels :</b><br>• Connectez-vous à votre compte<br>• Allez sur <b>"Suivi"</b><br>• Vous verrez tous vos signalements<br>• Cliquez sur un signalement pour voir les détails complets<br><br><b>3. Informations disponibles :</b><br>• Titre et description<br>• Statut actuel<br>• Priorité<br>• Localisation sur la carte<br>• Historique des modifications<br>• Photos jointes<br>• Technicien assigné'],
        
        [['aide','menu','quoi faire','options','commandes'],
         'Que peux-tu faire pour moi ?',
         '🤖 <b>Je suis Zeno, votre assistant CityZen !</b><br><br><b>Je peux vous aider avec :</b><br><br>📝 <b>Signalements :</b><br>• Comment créer un signalement<br>• Quelles catégories choisir<br>• Comment suivre votre demande<br>• Voir tous les signalements<br><br>🔧 <b>Interventions :</b><br>• Comment demander une intervention<br>• Modifier ou annuler<br>• Suivre un technicien<br><br>📊 <b>Statuts & Priorités :</b><br>• Explication des statuts<br>• Niveaux de priorité<br>• Délais de traitement<br><br>🔐 <b>Compte & Sécurité :</b><br>• Créer/modifier/supprimer un compte<br>• Réinitialiser un mot de passe<br>• Gérer les notifications<br><br>❓ <b>Général :</b><br>• À propos de CityZen<br>• Comment nous contacter<br>• FAQ complète'],
    ],

    // Page-specific tips
    pageTips: {
        '/signalement/creer': {
            greeting: '📝 Vous êtes sur le formulaire de signalement !',
            tips: ['Je peux vous aider à choisir la bonne catégorie', 'Décrivez votre problème et je suggère la priorité', 'N\'oubliez pas d\'ajouter une photo !']
        },
        '/signalements': {
            greeting: '📋 Voici la liste des signalements.',
            tips: ['Cherchez-vous un signalement en particulier ?', 'Cliquez sur un signalement pour voir les détails']
        },
        '/carte': {
            greeting: '🗺️ Bienvenue sur la carte interactive !',
            tips: ['Les couleurs des marqueurs indiquent la priorité', 'Cliquez sur un marqueur pour les détails', 'Zoomez pour voir une zone spécifique']
        },
        '/suivi': {
            greeting: '🔍 Page de suivi des signalements.',
            tips: ['Entrez votre numéro de référence pour suivre', 'Je peux expliquer chaque statut']
        },
        '/intervention': {
            greeting: '🔧 Section Interventions',
            tips: ['Besoin d\'aide pour remplir le formulaire ?', 'Je peux expliquer les niveaux d\'urgence']
        },
        '/contact': {
            greeting: '✉️ Vous souhaitez nous contacter ?',
            tips: ['Posez-moi votre question d\'abord, je peux peut-être y répondre !', 'Nos horaires : Lun-Ven 08h-17h']
        },
        '/admin': {
            greeting: '⚙️ Bienvenue dans l\'administration !',
            tips: ['Je peux expliquer les statistiques', 'Besoin d\'aide pour gérer les interventions ?']
        },
        '/': {
            greeting: '👋 Bienvenue sur CityZen !',
            tips: ['Signaler un problème', 'Suivre un signalement', 'Explorer la carte']
        }
    },

    // Greeting based on time of day
    getGreeting() {
        const h = new Date().getHours();
        if (h < 12) return 'Bonjour';
        if (h < 18) return 'Bon après-midi';
        return 'Bonsoir';
    }
};
