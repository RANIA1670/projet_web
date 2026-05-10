<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/includes/shell_start.php';
?>
  <p class="eq-page-lead"><a href="<?= htmlspecialchars(cityzen_asset('equipment/detail.php?id=' . $eqId), ENT_QUOTES, 'UTF-8') ?>">← Fiche équipement</a></p>
  <h1 class="eq-page-title"><?= $extendFrom > 0 ? 'Prolonger la réservation' : 'Demander une réservation' ?></h1>
  <p class="eq-page-lead"><?= htmlspecialchars((string) $row['name'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) $row['type_category_name'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php if ($extendFrom <= 0): ?>
    <p class="eq-page-lead"><strong>Tarif fixe :</strong> <?= number_format((float) $pricePerDay, 2, ',', ' ') ?> TND / jour.</p>
  <?php endif; ?>

  <div class="eq-steps">
    <span class="eq-step is-done">1. Équipement</span>
    <span class="eq-step is-done">2. Dates</span>
    <span class="eq-step is-done">3. Motif</span>
    <span class="eq-step">4. Envoi</span>
  </div>

  <?php if ($done): ?>
    <p class="eq-alert eq-alert--ok" role="status">Demande enregistrée. <strong>En attente de validation par un agent.</strong></p>
    <p><a class="eq-btn eq-btn--ghost" href="<?= htmlspecialchars(cityzen_asset('equipment/my-reservations.php'), ENT_QUOTES, 'UTF-8') ?>">Voir mes réservations</a></p>
  <?php else: ?>
    <?php if ($flash !== ''): ?>
      <p class="eq-alert eq-alert--<?= $flashType === 'ok' ? 'ok' : 'err' ?>"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!cityzen_is_logged_in() || cityzen_current_user_id() <= 0): ?>
      <p class="eq-page-lead">
        <a class="eq-btn eq-btn--primary" href="<?= htmlspecialchars(cityzen_login_url(cityzen_asset($_SERVER['REQUEST_URI'] ?? 'equipment/reserve.php?equipment_id=' . $eqId)), ENT_QUOTES, 'UTF-8') ?>">Connexion pour envoyer la demande</a>
        ou <a href="<?= htmlspecialchars(cityzen_asset('register.php'), ENT_QUOTES, 'UTF-8') ?>">créer un compte</a>.
      </p>
    <?php endif; ?>

    <p class="eq-page-lead">Les plages déjà prises ou en attente apparaissent en rouge ci-dessous.</p>
    <ul class="eq-busy-list" id="eq-busy-display" aria-live="polite"></ul>

    <form class="eq-form" method="post" action="" style="margin-top:18px" id="eq-reserve-form">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(cityzen_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="reserve_submit" value="1">
      <input type="hidden" name="equipment_id" value="<?= $eqId ?>">
      <?php if ($extendFrom > 0): ?>
        <input type="hidden" name="extend_from" value="<?= $extendFrom ?>">
        <label>
          Fin de votre réservation actuelle
          <input type="text" value="<?= htmlspecialchars((string) $parent['end_date'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </label>
        <?php
          $minExt = date('Y-m-d\TH:i', strtotime((string) $parent['end_date']) + 60);
        ?>
        <label>
          Nouvelle date de fin
          <input type="datetime-local" name="end_date" required min="<?= htmlspecialchars($minExt, ENT_QUOTES, 'UTF-8') ?>">
        </label>
      <?php else: ?>
        <div class="eq-reserve-grid">
          <div class="eq-reserve-main">
            <div style="margin: 6px 0 12px">
              <button type="button" class="eq-btn eq-btn--ghost" id="eq-assistant-btn">Ask Assistant for available schedule</button>
              <p class="eq-page-lead" style="margin-top:8px">L'assistant propose des créneaux selon les plages libres actuelles.</p>
              <div id="eq-assistant-results" aria-live="polite"></div>
            </div>
            <label>
              Début
              <input type="datetime-local" name="start_date" id="eq-start" required>
            </label>
            <label>
              Fin
              <input type="datetime-local" name="end_date" id="eq-end" required>
            </label>
            <label>
              Motif
              <select name="usage_purpose" required>
                <option value="">— Choisir —</option>
                <option value="event">Événement</option>
                <option value="repair">Réparation</option>
                <option value="inspection">Inspection</option>
              </select>
            </label>
            <label>
              Code remise (optionnel)
              <input type="text" name="discount_code" id="eq-discount-code" maxlength="64" placeholder="Ex: CZ-LUCKY-AB12CD" value="<?= htmlspecialchars($submittedDiscountCode, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <p class="eq-page-lead" id="eq-price-preview"></p>
          </div>
          <?php if (cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
            <aside class="eq-reserve-side">
              <div class="eq-alert eq-spin-box">
                <div class="eq-spin-head">
                  <div class="eq-spin-wheel" id="eq-spin-wheel" aria-hidden="true">
                    <span>🎁</span>
                  </div>
                  <div>
                    <p class="eq-page-lead eq-spin-title"><strong>Lucky spin du jour</strong> (1 tirage / jour).</p>
                    <p class="eq-page-lead eq-spin-sub">Tentez votre chance pour gagner un code remise.</p>
                  </div>
                </div>
                <p class="eq-page-lead" id="eq-spin-state">Vous avez 1 tentative aujourd'hui.</p>
                <button type="button" class="eq-btn eq-btn--ghost" id="eq-spin-btn">Lancer le spin</button>
                <div id="eq-spin-result"></div>
                <div class="eq-confetti" id="eq-confetti" aria-hidden="true"></div>
              </div>
            </aside>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (cityzen_is_logged_in() && cityzen_current_user_id() > 0): ?>
        <button type="submit" class="eq-btn eq-btn--primary"><?= $extendFrom > 0 ? 'Envoyer la demande de prolongation' : 'Envoyer la demande' ?></button>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <script>
  (function () {
    var busy = <?= $busyJson ?>;
    var list = document.getElementById('eq-busy-display');
    if (list && busy.length) {
      busy.forEach(function (b) {
        var li = document.createElement('li');
        li.textContent = b.start.replace('T', ' ').substring(0, 16) + ' → ' + b.end.replace('T', ' ').substring(0, 16);
        list.appendChild(li);
      });
    } else if (list) {
      var li = document.createElement('li');
      li.textContent = 'Aucune plage bloquée pour le moment.';
      li.style.borderLeftColor = 'var(--line)';
      li.style.background = 'var(--surface-soft)';
      li.style.color = 'var(--muted)';
      list.appendChild(li);
    }

    var btn = document.getElementById('eq-assistant-btn');
    var results = document.getElementById('eq-assistant-results');
    var startInput = document.getElementById('eq-start');
    var endInput = document.getElementById('eq-end');
    var codeInput = document.getElementById('eq-discount-code');
    var pricePreview = document.getElementById('eq-price-preview');
    var spinBtn = document.getElementById('eq-spin-btn');
    var spinState = document.getElementById('eq-spin-state');
    var spinResult = document.getElementById('eq-spin-result');
    var spinWheel = document.getElementById('eq-spin-wheel');
    var spinBox = document.querySelector('.eq-spin-box');
    var confetti = document.getElementById('eq-confetti');
    var csrf = '<?= htmlspecialchars(cityzen_csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
    var luckySpinApi = '<?= htmlspecialchars($luckySpinApiUrl, ENT_QUOTES, 'UTF-8') ?>';
    var pricePerDay = <?= json_encode((float) $pricePerDay) ?>;

    function parseLocalDate(v) {
      if (!v) return null;
      var d = new Date(v);
      return isNaN(d.getTime()) ? null : d;
    }

    function toLocalInputValue(dateObj) {
      var yyyy = dateObj.getFullYear();
      var mm = String(dateObj.getMonth() + 1).padStart(2, '0');
      var dd = String(dateObj.getDate()).padStart(2, '0');
      var hh = String(dateObj.getHours()).padStart(2, '0');
      var mi = String(dateObj.getMinutes()).padStart(2, '0');
      return yyyy + '-' + mm + '-' + dd + 'T' + hh + ':' + mi;
    }

    function toHuman(dateObj) {
      return dateObj.toLocaleString('fr-FR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    function daysBetween(startDate, endDate) {
      var ms = Math.max(1, endDate.getTime() - startDate.getTime());
      return Math.max(1, Math.ceil(ms / 86400000));
    }

    function refreshPreview() {
      if (!pricePreview || !startInput || !endInput) return;
      var s = parseLocalDate(startInput.value);
      var e = parseLocalDate(endInput.value);
      if (!s || !e || e <= s) {
        pricePreview.textContent = 'Le total estimé sera calculé après sélection des dates.';
        pricePreview.classList.remove('is-ready');
        return;
      }
      var d = daysBetween(s, e);
      var subtotal = d * pricePerDay;
      pricePreview.textContent = 'Estimation: ' + d + ' jour(s) × ' + subtotal.toFixed(2) + ' TND (' + pricePerDay.toFixed(2) + ' / jour), remise appliquée après vérification du code.';
      pricePreview.classList.add('is-ready');
      pricePreview.classList.remove('price-bump');
      void pricePreview.offsetWidth;
      pricePreview.classList.add('price-bump');
    }

    function renderSuggestions(slots) {
      if (!results) return;
      results.innerHTML = '';
      if (!slots || !slots.length) {
        results.innerHTML = '<p class="eq-page-lead">Aucune proposition trouvée sur les prochains jours.</p>';
        return;
      }
      var wrapper = document.createElement('div');
      wrapper.style.display = 'grid';
      wrapper.style.gap = '8px';
      slots.forEach(function (slot, idx) {
        var start = new Date(String(slot.start).replace(' ', 'T'));
        var end = new Date(String(slot.end).replace(' ', 'T'));
        if (isNaN(start.getTime()) || isNaN(end.getTime())) return;
        var row = document.createElement('div');
        row.className = 'eq-suggestion-card';
        row.style.animationDelay = (idx * 70) + 'ms';
        var meta = document.createElement('p');
        meta.className = 'eq-page-lead';
        meta.textContent = toHuman(start) + ' -> ' + toHuman(end) + ' (score: ' + slot.score + ')';
        var why = document.createElement('p');
        why.className = 'eq-page-lead';
        why.textContent = String(slot.reason || '');
        var apply = document.createElement('button');
        apply.type = 'button';
        apply.className = 'eq-btn eq-btn--ghost';
        apply.textContent = 'Utiliser ce créneau';
        apply.addEventListener('click', function () {
          if (startInput) startInput.value = toLocalInputValue(start);
          if (endInput) endInput.value = toLocalInputValue(end);
          refreshPreview();
          var notice = document.createElement('p');
          notice.className = 'eq-alert eq-alert--ok';
          notice.textContent = 'Créneau appliqué au formulaire.';
          results.prepend(notice);
        });
        row.appendChild(meta);
        row.appendChild(why);
        row.appendChild(apply);
        wrapper.appendChild(row);
      });
      results.appendChild(wrapper);
    }

    function launchConfetti() {
      if (!confetti) return;
      confetti.innerHTML = '';
      confetti.classList.remove('is-active');
      var colors = ['#60a5fa', '#34d399', '#fbbf24', '#f472b6', '#a78bfa'];
      for (var i = 0; i < 22; i++) {
        var p = document.createElement('span');
        p.className = 'eq-confetti-piece';
        p.style.left = (4 + Math.random() * 92) + '%';
        p.style.background = colors[i % colors.length];
        p.style.animationDelay = (Math.random() * 0.25).toFixed(2) + 's';
        p.style.transform = 'rotate(' + Math.round(Math.random() * 320) + 'deg)';
        confetti.appendChild(p);
      }
      void confetti.offsetWidth;
      confetti.classList.add('is-active');
      setTimeout(function () { confetti.classList.remove('is-active'); }, 1800);
    }

    if (btn && results) {
      btn.addEventListener('click', function () {
        var duration = 120;
        var s = parseLocalDate(startInput ? startInput.value : '');
        var e = parseLocalDate(endInput ? endInput.value : '');
        if (s && e && e > s) {
          duration = Math.max(30, Math.round((e.getTime() - s.getTime()) / 60000));
        }
        btn.disabled = true;
        btn.textContent = 'Assistant en cours...';
        fetch('<?= htmlspecialchars($assistantApiUrl, ENT_QUOTES, 'UTF-8') ?>?equipment_id=<?= (int) $eqId ?>&duration_minutes=' + duration, {
          headers: { 'Accept': 'application/json' }
        })
          .then(function (r) { return r.json(); })
          .then(function (data) { renderSuggestions(data.slots || []); })
          .catch(function () {
            results.innerHTML = '<p class="eq-alert eq-alert--err">Assistant indisponible pour le moment.</p>';
          })
          .finally(function () {
            btn.disabled = false;
            btn.textContent = 'Ask Assistant for available schedule';
          });
      });
    }

    if (startInput) startInput.addEventListener('change', refreshPreview);
    if (endInput) endInput.addEventListener('change', refreshPreview);
    if (codeInput) codeInput.addEventListener('input', refreshPreview);
    refreshPreview();

    function renderSpinState(data) {
      if (!spinState || !spinResult || !spinBtn) return;
      if (!data || !data.ok) {
        spinState.textContent = 'Lucky spin indisponible.';
        spinBtn.disabled = true;
        spinBtn.classList.remove('is-spinning');
        if (spinWheel) spinWheel.classList.remove('is-spinning');
        return;
      }
      if (data.can_spin) {
        spinState.textContent = 'Vous avez 1 tirage disponible aujourd\'hui.';
        spinBtn.disabled = false;
        spinBtn.classList.remove('is-spinning');
        spinBtn.textContent = 'Lancer le spin';
        if (spinWheel) spinWheel.classList.remove('is-spinning');
        if (spinBox) spinBox.classList.remove('is-win', 'is-lose');
        return;
      }
      spinBtn.disabled = true;
      spinBtn.classList.remove('is-spinning');
      if (spinWheel) spinWheel.classList.remove('is-spinning');
      if (data.outcome === 'discount' && data.code) {
        spinState.textContent = 'Tirage déjà utilisé aujourd\'hui.';
        spinResult.innerHTML = '<p class="eq-alert eq-alert--ok">Code gagné: <strong>' + data.code + '</strong> (' + data.discount_percent + '%). Expire: ' + (data.valid_until || '-') + '</p>';
        spinResult.classList.remove('spin-reveal');
        void spinResult.offsetWidth;
        spinResult.classList.add('spin-reveal');
        if (codeInput && !codeInput.value) {
          codeInput.value = data.code;
          codeInput.classList.remove('code-highlight');
          void codeInput.offsetWidth;
          codeInput.classList.add('code-highlight');
        }
        if (spinBox) {
          spinBox.classList.add('is-win');
          spinBox.classList.remove('is-lose');
        }
        launchConfetti();
      } else {
        spinState.textContent = 'Tirage déjà utilisé aujourd\'hui.';
      }
    }

    if (spinBtn) {
      fetch(luckySpinApi, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(renderSpinState)
        .catch(function () { if (spinState) spinState.textContent = 'Lucky spin indisponible.'; });

      spinBtn.addEventListener('click', function () {
        spinBtn.disabled = true;
        spinBtn.textContent = 'Tirage...';
        spinBtn.classList.add('is-spinning');
        if (spinWheel) spinWheel.classList.add('is-spinning');
        var body = new URLSearchParams();
        body.set('csrf', csrf);
        fetch(luckySpinApi, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: body.toString()
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            renderSpinState(data);
          })
          .catch(function () {
            if (spinResult) spinResult.innerHTML = '<p class="eq-alert eq-alert--err">Erreur Lucky spin.</p>';
            spinBtn.disabled = false;
            spinBtn.classList.remove('is-spinning');
          })
          .finally(function () {
            spinBtn.textContent = 'Lancer le spin';
            spinBtn.classList.remove('is-spinning');
            if (spinWheel) spinWheel.classList.remove('is-spinning');
          });
      });
    }
  })();
  </script>

<?php require dirname(__DIR__, 3) . '/includes/shell_end.php'; ?>
