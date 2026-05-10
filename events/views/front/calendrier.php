<?php
// ================================================
//  VUE  : views/front/calendrier.php
//  RÔLE : Calendrier interactif FullCalendar des événements
// ================================================
$titrePage = 'Calendrier des événements';
require __DIR__ . '/../layouts/front_header.php';
?>

<!-- FullCalendar CSS + JS (CDN) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
#calendrier-wrapper {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px 60px;
}
.fc {
    font-family: 'Montserrat', sans-serif;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 32px rgba(30,58,95,.10);
    padding: 20px;
    border: 1px solid #e2e8f0;
}
.fc .fc-toolbar-title { font-size: 1.3rem; color: #1e3a5f; font-weight: 700; }
.fc .fc-button-primary {
    background: #1e3a5f !important;
    border-color: #1e3a5f !important;
    border-radius: 8px !important;
}
.fc .fc-button-primary:hover { background: #E67E22 !important; border-color: #E67E22 !important; }
.fc .fc-button-primary:disabled { background: #94a3b8 !important; border-color: #94a3b8 !important; }
.fc-daygrid-day-number, .fc-col-header-cell-cushion { color: #1e3a5f !important; text-decoration: none !important; }
.fc-day-today { background: #fff7ed !important; }
.fc-event { border-radius: 6px !important; font-size: .82rem !important; cursor: pointer !important; padding: 2px 6px !important; }
/* Popup tooltip */
#event-popup {
    display: none;
    position: fixed;
    z-index: 9999;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 12px 48px rgba(0,0,0,.18);
    padding: 22px 24px;
    min-width: 280px;
    max-width: 340px;
    border-top: 4px solid #E67E22;
    animation: popupIn .2s ease;
}
@keyframes popupIn {
    from { opacity:0; transform:translateY(-10px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
#popup-close {
    position: absolute;
    top: 10px; right: 14px;
    background: none; border: none;
    font-size: 1.3rem; cursor: pointer; color: #94a3b8;
}
#popup-close:hover { color: #1e3a5f; }
.popup-badge {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    border-radius: 50px;
    padding: 3px 12px;
    font-size: .78rem;
    margin: 4px 4px 0 0;
}
.legend-dot {
    width: 12px; height: 12px; border-radius: 50%;
    display: inline-block; margin-right: 6px;
}
</style>

<div id="calendrier-wrapper">

    <div style="text-align:center;margin:40px 0 32px;">
        <h1 style="color:#1e3a5f;font-size:2rem;margin-bottom:8px;">📅 Calendrier des événements</h1>
        <p style="color:#64748b;font-size:1rem;">Cliquez sur un événement pour voir ses détails</p>

        <!-- Légende -->
        <div style="display:flex;justify-content:center;gap:24px;margin-top:18px;flex-wrap:wrap;">
            <span><span class="legend-dot" style="background:#1e3a5f;"></span>Événement à venir</span>
            <span><span class="legend-dot" style="background:#94a3b8;"></span>Événement passé</span>
        </div>
    </div>

    <!-- Vue switcher -->
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:16px;">
        <button onclick="calendar.changeView('dayGridMonth')" class="btn btn-gris" id="btn-month">Mois</button>
        <button onclick="calendar.changeView('listWeek')"    class="btn btn-gris" id="btn-week">Liste semaine</button>
        <button onclick="calendar.changeView('listMonth')"   class="btn btn-gris" id="btn-list">Liste mois</button>
    </div>

    <div id="fc-calendar"></div>

</div>

<!-- Popup événement -->
<div id="event-popup">
    <button id="popup-close" onclick="closePopup()">✕</button>
    <div id="popup-content"></div>
</div>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('fc-calendar');

    calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        firstDay: 1,
        height: 'auto',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  ''
        },
        buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', list: 'Liste' },
        events: 'index.php?page=api_events_json',
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            showPopup(info.event, info.jsEvent);
        },
        eventMouseEnter: function (info) {
            info.el.style.opacity = '.85';
            info.el.style.transform = 'scale(1.04)';
        },
        eventMouseLeave: function (info) {
            info.el.style.opacity = '';
            info.el.style.transform = '';
        },
        dayCellDidMount: function (info) {
            // Surligner aujourd'hui
        }
    });

    calendar.render();
});

function showPopup(event, jsEvent) {
    const props = event.extendedProps;
    const date  = event.start ? new Date(event.start).toLocaleDateString('fr-FR', {
        weekday:'long', year:'numeric', month:'long', day:'numeric'
    }) : '';

    document.getElementById('popup-content').innerHTML = `
        <div style="font-size:.78rem;letter-spacing:1.5px;text-transform:uppercase;color:#E67E22;font-weight:700;margin-bottom:8px;">Événement</div>
        <h3 style="margin:0 0 14px;color:#1e3a5f;font-size:1.1rem;">${escapeHtml(event.title)}</h3>
        <span class="popup-badge">📅 ${date}</span>
        <span class="popup-badge">📍 ${escapeHtml(props.lieu || '')}</span>
        <span class="popup-badge">💼 ${escapeHtml(props.sponsor || '')}</span>
        <p style="margin:14px 0 16px;color:#555;font-size:.88rem;line-height:1.6;">${escapeHtml(props.description || '')}</p>
        <a href="${escapeHtml(event.url)}"
           style="display:inline-block;background:linear-gradient(135deg,#1e3a5f,#2d4f7c);color:#fff;padding:10px 22px;border-radius:8px;text-decoration:none;font-weight:600;font-size:.9rem;">
            Voir l'événement →
        </a>
    `;

    const popup = document.getElementById('event-popup');
    popup.style.display = 'block';

    // Positionner
    const W = window.innerWidth, H = window.innerHeight;
    let x = jsEvent.clientX + 16, y = jsEvent.clientY + 16;
    if (x + 360 > W) x = jsEvent.clientX - 360;
    if (y + 340 > H) y = jsEvent.clientY - 340;
    popup.style.left = Math.max(8, x) + 'px';
    popup.style.top  = Math.max(8, y) + 'px';
}

function closePopup() {
    document.getElementById('event-popup').style.display = 'none';
}

// Fermer popup en cliquant ailleurs
document.addEventListener('click', function(e) {
    const popup = document.getElementById('event-popup');
    if (popup.style.display === 'block' && !popup.contains(e.target)) {
        closePopup();
    }
});

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
