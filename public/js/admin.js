// JavaScript pour l'administration
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des formulaires d'administration
    const adminForms = document.querySelectorAll('.admin-form');

    adminForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Traitement...';

                // Remettre le bouton à l'état normal après 2 secondes
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 2000);
            }
        });
    });

    // Animation des cartes du dashboard
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Gestion des confirmations de suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const itemType = this.closest('tr') ? 'cet élément' : 'cet élément';
            if (!confirm(`Êtes-vous sûr de vouloir supprimer ${itemType} ? Cette action est irréversible.`)) {
                e.preventDefault();
            }
        });
    });

    // Mise à jour automatique des statistiques (toutes les 30 secondes)
    if (document.querySelector('.dashboard')) {
        setInterval(() => {
            // Ici on pourrait ajouter une requête AJAX pour mettre à jour les stats
            console.log('Mise à jour des statistiques...');
        }, 30000);
    }

    // Gestion des onglets/sidebar responsive
    const sidebarToggle = document.createElement('button');
    sidebarToggle.textContent = '☰';
    sidebarToggle.className = 'sidebar-toggle';
    sidebarToggle.style.display = 'none';
    sidebarToggle.style.position = 'fixed';
    sidebarToggle.style.top = '1rem';
    sidebarToggle.style.left = '1rem';
    sidebarToggle.style.zIndex = '1001';
    sidebarToggle.style.background = '#2563eb';
    sidebarToggle.style.color = 'white';
    sidebarToggle.style.border = 'none';
    sidebarToggle.style.padding = '0.5rem';
    sidebarToggle.style.borderRadius = '0.5rem';
    sidebarToggle.style.cursor = 'pointer';

    document.body.appendChild(sidebarToggle);

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('sidebar-open');
        }
    }

    sidebarToggle.addEventListener('click', toggleSidebar);

    // Afficher le bouton toggle sur mobile
    function checkScreenSize() {
        if (window.innerWidth <= 1024) {
            sidebarToggle.style.display = 'block';
        } else {
            sidebarToggle.style.display = 'none';
        }
    }

    window.addEventListener('resize', checkScreenSize);
    checkScreenSize();
});