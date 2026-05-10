/**
 * CityZen Copilot — "Zeno" Smart Assistant
 * 100% Client-Side, No API Required
 */
(function() {
    'use strict';

    const APP = window.COPILOT_APP_URL || '';
    const userRole = window.COPILOT_USER_ROLE || 'guest';
    const userName = window.COPILOT_USER_NAME || '';

    // ── State ──
    let isOpen = false;
    let chatHistory = JSON.parse(sessionStorage.getItem('copilot_history') || '[]');
    let hasGreeted = sessionStorage.getItem('copilot_greeted') === '1';

    // ── Build DOM ──
    function init() {
        document.body.classList.add('copilot-active');
        createBubble();
        createPanel();
        bindEvents();
        if (chatHistory.length > 0) restoreHistory();
        else if (!hasGreeted) showWelcome();
    }

    function createBubble() {
        const b = document.createElement('button');
        b.id = 'copilotBubble';
        b.innerHTML = '<span class="bubble-icon"><i class="fas fa-robot"></i></span><span class="copilot-shortcut-hint">Ctrl+K</span>';
        document.body.appendChild(b);
    }

    function createPanel() {
        const p = document.createElement('div');
        p.id = 'copilotPanel';
        p.innerHTML = `
            <div class="copilot-header">
                <div class="copilot-avatar">🤖</div>
                <div class="copilot-header-info">
                    <div class="copilot-header-name">Zeno — Assistant CityZen</div>
                    <div class="copilot-header-status">En ligne • Prêt à aider</div>
                </div>
                <button class="copilot-close" id="copilotClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="copilot-messages" id="copilotMessages"></div>
            <div class="copilot-powered">Propulsé par <span>Zeno AI</span> — 100% local</div>
            <div class="copilot-input-area">
                <div class="copilot-input-wrap">
                    <input type="text" id="copilotInput" placeholder="Posez votre question..." autocomplete="off">
                </div>
                <button class="copilot-send-btn" id="copilotSend"><i class="fas fa-paper-plane"></i></button>
            </div>`;
        document.body.appendChild(p);
    }

    // ── Events ──
    function bindEvents() {
        document.getElementById('copilotBubble').addEventListener('click', toggle);
        document.getElementById('copilotClose').addEventListener('click', toggle);
        document.getElementById('copilotSend').addEventListener('click', handleSend);
        document.getElementById('copilotInput').addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); }
        });
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); toggle(); }
            if (e.key === 'Escape' && isOpen) toggle();
        });
    }

    function toggle() {
        isOpen = !isOpen;
        document.getElementById('copilotPanel').classList.toggle('visible', isOpen);
        document.getElementById('copilotBubble').classList.toggle('open', isOpen);
        if (isOpen) {
            setTimeout(() => document.getElementById('copilotInput').focus(), 350);
            if (!hasGreeted && chatHistory.length === 0) { showWelcome(); hasGreeted = true; sessionStorage.setItem('copilot_greeted', '1'); }
        }
    }

    // ── Messages ──
    function addMsg(text, type, save = true) {
        const container = document.getElementById('copilotMessages');
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');

        const msg = document.createElement('div');
        msg.className = `copilot-msg ${type}`;
        const avatarContent = type === 'bot' ? '🤖' : (userName ? userName.charAt(0).toUpperCase() : '👤');
        msg.innerHTML = `
            <div class="copilot-msg-avatar">${avatarContent}</div>
            <div class="copilot-msg-content">
                <div class="copilot-msg-bubble">${text}</div>
                <div class="copilot-msg-time">${timeStr}</div>
            </div>`;
        container.appendChild(msg);
        container.scrollTop = container.scrollHeight;

        if (save) {
            chatHistory.push({ text, type, time: timeStr });
            sessionStorage.setItem('copilot_history', JSON.stringify(chatHistory));
        }
    }

    function addChips(chips) {
        const container = document.getElementById('copilotMessages');
        const div = document.createElement('div');
        div.className = 'copilot-chips';
        div.style.paddingLeft = '38px';
        chips.forEach(c => {
            const chip = document.createElement('button');
            chip.className = 'copilot-chip';
            chip.innerHTML = `<i class="${c.icon}"></i> ${c.label}`;
            chip.addEventListener('click', () => {
                div.remove();
                if (c.action) c.action();
                else if (c.query) { addMsg(c.label, 'user'); processQuery(c.label); }
            });
            div.appendChild(chip);
        });
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function addQuickGrid() {
        const container = document.getElementById('copilotMessages');
        const div = document.createElement('div');
        div.style.paddingLeft = '38px';
        div.innerHTML = `
            <div class="copilot-quick-grid">
                <div class="copilot-quick-card card-report" data-action="report">
                    <i class="fas fa-plus-circle"></i>
                    <div class="quick-label">Signaler</div>
                    <div class="quick-desc">Créer un signalement</div>
                </div>
                <div class="copilot-quick-card card-map" data-action="map">
                    <i class="fas fa-map-marked-alt"></i>
                    <div class="quick-label">Carte</div>
                    <div class="quick-desc">Voir la carte</div>
                </div>
                <div class="copilot-quick-card card-track" data-action="track">
                    <i class="fas fa-search-location"></i>
                    <div class="quick-label">Suivi</div>
                    <div class="quick-desc">Suivre un signalement</div>
                </div>
                <div class="copilot-quick-card card-help" data-action="help">
                    <i class="fas fa-question-circle"></i>
                    <div class="quick-label">Aide</div>
                    <div class="quick-desc">FAQ & Questions</div>
                </div>
            </div>`;
        div.querySelectorAll('.copilot-quick-card').forEach(card => {
            card.addEventListener('click', () => {
                const a = card.dataset.action;
                if (a === 'report') window.location.href = APP + '/signalement/creer';
                else if (a === 'map') window.location.href = APP + '/carte';
                else if (a === 'track') window.location.href = APP + '/suivi';
                else if (a === 'help') { addMsg('Aide', 'user'); showFAQMenu(); }
            });
        });
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function showTyping() {
        const container = document.getElementById('copilotMessages');
        const div = document.createElement('div');
        div.className = 'copilot-typing';
        div.id = 'copilotTypingIndicator';
        div.innerHTML = `<div class="copilot-msg-avatar">🤖</div><div class="copilot-typing-dots"><span></span><span></span><span></span></div>`;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function hideTyping() {
        const el = document.getElementById('copilotTypingIndicator');
        if (el) el.remove();
    }

    function botReply(text, chips) {
        showTyping();
        const delay = Math.min(300 + text.length * 3, 1200);
        setTimeout(() => {
            hideTyping();
            addMsg(text, 'bot');
            if (chips) addChips(chips);
        }, delay);
    }

    // ── Welcome ──
    function showWelcome() {
        const greeting = CopilotKB.getGreeting();
        const nameStr = userName ? ` ${userName}` : '';
        const pageTip = getPageTip();

        addMsg(`${greeting}${nameStr} ! 👋 Je suis <b>Zeno</b>, votre assistant CityZen.<br><br>${pageTip ? pageTip.greeting + '<br><br>' : ''}Comment puis-je vous aider ?`, 'bot', false);
        addQuickGrid();

        if (pageTip && pageTip.tips.length > 0) {
            setTimeout(() => {
                addChips(pageTip.tips.map(t => ({ label: t, icon: 'fas fa-lightbulb', query: t })));
            }, 600);
        }
    }

    function getPageTip() {
        const path = window.location.pathname;
        for (const [route, data] of Object.entries(CopilotKB.pageTips)) {
            if (route === '/' && (path.endsWith('/public/') || path.endsWith('/public/index.php') || path === '/')) return data;
            if (route !== '/' && path.includes(route)) return data;
        }
        return null;
    }

    // ── Handle Send ──
    function handleSend() {
        const input = document.getElementById('copilotInput');
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        addMsg(text, 'user');
        processQuery(text);
    }

    // ── Query Processing ──
    function processQuery(query) {
        const q = query.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        // Empty input
        if (query.trim().length === 0) {
            botReply('Vous avez oublié d\'écrire votre question ! 😊 Que puis-je faire pour vous ?');
            return;
        }

        // Greeting
        if (matchAny(q, ['bonjour','bonsoir','salut','hello','hi','hey','coucou','salam','ca va','ça va'])) {
            const g = CopilotKB.getGreeting();
            botReply(`${g} ! 😊 Comment puis-je vous aider aujourd'hui ?`, getMainChips());
            return;
        }

        // Thanks
        if (matchAny(q, ['merci','thanks','super','parfait','genial','excellent','top','bravo'])) {
            botReply('Avec plaisir ! 😊 N\'hésitez pas si vous avez d\'autres questions.', getMainChips());
            return;
        }

        // Help / menu
        if (matchAny(q, ['aide','help','menu','quoi faire','que faire','option','commandes','possibilite','possibilités'])) {
            showFAQMenu();
            return;
        }

        // Check for navigation intents first
        if (matchAny(q, ['aller','naviguer','ouvrir','voir','page','envoie moi','emmenez moi','comment aller','acceder'])) {
            const nav = detectNavigation(q);
            if (nav) { botReply(nav.msg, [{ label: 'Y aller →', icon: 'fas fa-arrow-right', action: () => window.location.href = nav.url }]); return; }
        }

        // Check for status explanation
        if (matchAny(q, ['statut','status','état','signifie','veut dire','c\'est quoi','qu\'est-ce','sert','sens','definition'])) {
            const statusInfo = detectStatus(q);
            if (statusInfo) { botReply(statusInfo); return; }
            botReply(getAllStatuses()); return;
        }

        // Check for category help (form assist)
        if (isOnSignalementPage() && matchAny(q, ['catégorie','categorie','type','quel','choisir','suggère','suggere','conseil','aide categorie'])) {
            botReply('Décrivez le problème en quelques mots et je vous suggérerai la catégorie la plus adaptée ! 🎯');
            return;
        }

        // Try category detection (for form assist)
        if (isOnSignalementPage()) {
            const cat = detectCategory(q);
            if (cat) {
                botReply(`🎯 D'après votre description, je vous suggère :<br><br>` +
                    `<div class="copilot-suggestion" onclick="document.getElementById('categorie_id').value='${cat.id}';this.style.borderColor='#4ECDC4';">` +
                    `<div class="suggestion-icon" style="background:${cat.color}22;color:${cat.color}"><i class="fas ${cat.icon}"></i></div>` +
                    `<div><div class="suggestion-text">${cat.name}</div><div class="suggestion-desc">Cliquez pour sélectionner</div></div></div>`,
                    getPriorityChips(q));
                return;
            }
        }

        // Try priority detection
        if (matchAny(q, ['priorité','priorite','urgent','grave','danger','niveau','urgence','rapide','vite'])) {
            const pri = detectPriority(q);
            if (pri) { botReply(pri); return; }
        }

        // Specific question about listing signalements
        if ((q.includes('donne') || q.includes('donne moi') || q.includes('donne-moi') || q.includes('liste') || q.includes('voir') || q.includes('afficher') || q.includes('montre'))
            && (q.includes('signalement') || q.includes('signalements'))) {
            botReply('📋 <b>Pour voir tous les signalements :</b><br>1. Cliquez sur <b>"Signalements"</b> dans la barre de navigation.<br>2. Vous verrez la liste complète.<br>3. Utilisez les filtres par statut, priorité ou catégorie.<br>4. Cliquez sur un signalement pour voir les détails.', getFollowUpChips());
            return;
        }

        // FAQ matching - LOWERED THRESHOLD for better matching
        const faqResult = matchFAQ(q);
        if (faqResult) {
            botReply(faqResult, getFollowUpChips());
            return;
        }

        // Fallback with smart suggestions
        const suggestions = generateSmartSuggestions(q);
        botReply(`Hmm, je ne comprends pas exactement votre question. 🤔<br><br>${suggestions}`,
            [
                { label: '📚 Voir l\'aide complète', icon: 'fas fa-book', query: 'aide' },
                { label: '💬 Rephrasez votre question', icon: 'fas fa-edit', query: '' }
            ]);
    }

    // ── Detection Helpers ──
    function matchAny(text, keywords) {
        return keywords.some(k => text.includes(k));
    }

    function detectCategory(text) {
        let best = null, bestScore = 0;
        for (const [name, data] of Object.entries(CopilotKB.categories)) {
            let score = 0;
            data.keywords.forEach(kw => {
                if (text.includes(kw.normalize('NFD').replace(/[\u0300-\u036f]/g, ''))) score += kw.length;
            });
            if (score > bestScore) { bestScore = score; best = { name, ...data }; }
        }
        return bestScore >= 3 ? best : null;
    }

    function detectPriority(text) {
        for (const [level, keywords] of Object.entries(CopilotKB.priority)) {
            if (keywords.some(k => text.includes(k))) {
                const emojis = { urgente: '🔴', haute: '🟠', moyenne: '🟡', faible: '🟢' };
                const descs = {
                    urgente: 'Danger immédiat, nécessite une intervention dans les 24-48h.',
                    haute: 'Problème sérieux à traiter rapidement (3-5 jours).',
                    moyenne: 'Gêne modérée, traitement dans 1-2 semaines.',
                    faible: 'Problème mineur, pas de danger immédiat.'
                };
                return `D'après vos mots, je recommande le niveau :<br><br>${emojis[level]} <b>${level.charAt(0).toUpperCase() + level.slice(1)}</b><br>${descs[level]}`;
            }
        }
        return null;
    }

    function detectStatus(text) {
        for (const [key, data] of Object.entries(CopilotKB.statuses)) {
            if (text.includes(key) || text.includes(data.label.toLowerCase())) {
                return `${data.emoji} <b>${data.label}</b><br><br>${data.desc}`;
            }
        }
        return null;
    }

    function getAllStatuses() {
        let html = '📊 Voici tous les statuts possibles :<br><br>';
        for (const [key, data] of Object.entries(CopilotKB.statuses)) {
            html += `<span class="copilot-status-badge s-${key}">${data.emoji} ${data.label}</span> ${data.desc}<br><br>`;
        }
        return html;
    }

    function detectNavigation(text) {
        const routes = [
            { keys: ['signalement','signaler','creer','créer','nouveau','rapporter','déclarer','probleme','problème','soumettre','faire un signalement'], url: APP + '/signalement/creer', msg: '📝 Je peux vous emmener au formulaire de signalement !' },
            { keys: ['carte','map','localisation','géographique','tous les signalements','voir la carte'], url: APP + '/carte', msg: '🗺️ Je peux ouvrir la carte interactive pour vous !' },
            { keys: ['suivi','suivre','tracking','mon signalement','mon demande','mes tickets','historique'], url: APP + '/suivi', msg: '🔍 Direction la page de suivi !' },
            { keys: ['contact','contacter','ecrire','écrire','nous contacter','support','question','nous joindre'], url: APP + '/contact', msg: '✉️ Je peux vous emmener au formulaire de contact !' },
            { keys: ['intervention','demande','intervention demande','technicien','réparation','travaux','service'], url: APP + '/intervention/demande', msg: '🔧 Direction la demande d\'intervention !' },
            { keys: ['connexion','login','connecter','s\'identifier','authentification','logged','se connecter'], url: APP + '/auth/connexion', msg: '🔑 Direction la page de connexion !' },
            { keys: ['inscription','inscrire','register','enregistrement','créer compte','creer compte','nouveau compte'], url: APP + '/auth/inscription', msg: '📋 Direction la page d\'inscription !' },
            { keys: ['accueil','home','principal','page d\'accueil','début','start'], url: APP + '/', msg: '🏠 Retour à la page d\'accueil !' },
            { keys: ['admin','administration','backoffice','panneau','gestion','management'], url: APP + '/admin', msg: '⚙️ Direction le panneau d\'administration !' },
        ];
        for (const r of routes) {
            if (r.keys.some(k => text.includes(k))) return r;
        }
        return null;
    }

    function matchFAQ(text) {
        let best = null, bestScore = 0;
        for (const [keywords, question, answer] of CopilotKB.faq) {
            let score = 0;
            keywords.forEach(kw => {
                const normalized = kw.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                if (text.includes(normalized)) score += normalized.length;
            });
            if (score > bestScore) { bestScore = score; best = answer; }
        }
        // REDUCED THRESHOLD from 4 to 2 for better matching
        return bestScore >= 2 ? best : null;
    }

    function generateSmartSuggestions(q) {
        // Try to detect what the user might want
        if (q.length < 3) {
            return 'Votre question est trop courte. Essayez d\'être plus précis. Par exemple :<br>• "Comment signaler un problème ?"<br>• "Quel est mon numéro de référence ?"';
        }
        
        // Check for key topics
        if (matchAny(q, ['signalement','probleme','problème','déclaration','dépôt','déclarer','rapporter'])) {
            return 'Vous avez une question sur les signalements ? Essayez :<br>• "Comment créer un signalement ?"<br>• "Comment suivre mon signalement ?"';
        }
        
        if (matchAny(q, ['intervention','technicien','réparation','travaux','demande'])) {
            return 'Vous demandez une intervention ? Essayez :<br>• "Comment demander une intervention ?"<br>• "Comment modifier une intervention ?"';
        }
        
        if (matchAny(q, ['compte','profil','connexion','password','mot passe'])) {
            return 'Question sur votre compte ? Essayez :<br>• "Comment me connecter ?"<br>• "J\'ai oublié mon mot de passe"';
        }
        
        if (matchAny(q, ['carte','localisation','position','gps','géolocalisation','map'])) {
            return 'Vous cherchez la carte ? Essayez :<br>• "Comment voir la carte ?"<br>• "Comment ajouter ma localisation ?"';
        }
        
        if (matchAny(q, ['notification','alerte','cloche','email','message'])) {
            return 'Question sur les notifications ? Essayez :<br>• "Comment fonctionnent les notifications ?"<br>• "Comment activer les alertes ?"';
        }
        
        return 'Pouvez-vous être plus précis ? Ou tapez <b>"aide"</b> pour voir tous les sujets disponibles.';
    }

    function isOnSignalementPage() {
        return window.location.pathname.includes('/signalement/creer') || window.location.pathname.includes('/signalement/créer');
    }

    // ── Chip Generators ──
    function getMainChips() {
        return [
            { label: '📝 Signaler', icon: 'fas fa-plus', action: () => window.location.href = APP + '/signalement/creer' },
            { label: '🔍 Suivi', icon: 'fas fa-search', action: () => window.location.href = APP + '/suivi' },
            { label: '❓ FAQ', icon: 'fas fa-question', query: 'aide' }
        ];
    }

    function getFollowUpChips() {
        return [
            { label: 'Autre question', icon: 'fas fa-comment', query: 'aide' },
            { label: 'Merci !', icon: 'fas fa-heart', query: 'merci' }
        ];
    }

    function getPriorityChips(text) {
        if (isOnSignalementPage()) {
            return [
                { label: '🟢 Faible', icon: '', action: () => { setSel('priorite','faible'); botReply('✅ Priorité mise à <b>Faible</b>.'); }},
                { label: '🟡 Moyenne', icon: '', action: () => { setSel('priorite','moyenne'); botReply('✅ Priorité mise à <b>Moyenne</b>.'); }},
                { label: '🟠 Haute', icon: '', action: () => { setSel('priorite','haute'); botReply('✅ Priorité mise à <b>Haute</b>.'); }},
                { label: '🔴 Urgente', icon: '', action: () => { setSel('priorite','urgente'); botReply('✅ Priorité mise à <b>Urgente</b>.'); }}
            ];
        }
        return [];
    }

    function setSel(id, val) {
        const el = document.getElementById(id);
        if (el) { el.value = val; el.dispatchEvent(new Event('change')); }
    }

    function showFAQMenu() {
        const topics = [
            { label: 'Comment signaler ?', icon: 'fas fa-plus-circle', query: 'comment signaler un problème' },
            { label: 'Suivre un signalement', icon: 'fas fa-search', query: 'comment suivre mon signalement' },
            { label: 'Les catégories', icon: 'fas fa-tag', query: 'quelles catégories' },
            { label: 'Niveaux de priorité', icon: 'fas fa-flag', query: 'niveaux de priorité' },
            { label: 'Délais de traitement', icon: 'fas fa-clock', query: 'délai de traitement' },
            { label: 'Nous contacter', icon: 'fas fa-envelope', query: 'comment contacter' }
        ];
        botReply('Voici les sujets les plus demandés 📚 :', topics);
    }

    // ── History ──
    function restoreHistory() {
        chatHistory.forEach(m => addMsg(m.text, m.type, false));
    }

    // ── Init ──
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
