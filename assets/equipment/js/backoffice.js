/**
 * Modales, carte Leaflet, sélection groupée, refus réservation.
 */
(function () {
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return [].slice.call((ctx || document).querySelectorAll(sel)); }

  document.addEventListener('DOMContentLoaded', function () {
    var toast = document.getElementById('toast');
    if (toast && toast.dataset.message) {
      toast.textContent = toast.dataset.message;
      toast.className = 'toast ' + (toast.dataset.type || 'success');
      void toast.offsetWidth;
      toast.classList.add('show');
      setTimeout(function () { toast.classList.remove('show'); }, 3800);
    }

    qsa('.bo-modal-backdrop, [data-close="1"]').forEach(function (el) {
      el.addEventListener('click', function () {
        var m = el.closest('.bo-modal');
        if (m) { m.classList.remove('is-open'); m.setAttribute('aria-hidden', 'true'); }
      });
    });

    var map, marker;
    function initMap() {
      var el = document.getElementById('map-picker');
      if (!el || typeof L === 'undefined') return;
      if (map) { map.remove(); map = null; marker = null; }
      var lat = parseFloat(qs('#eq-lat').value) || 36.8065;
      var lng = parseFloat(qs('#eq-lng').value) || 10.1815;
      map = L.map(el).setView([lat, lng], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(map);
      marker = L.marker([lat, lng], { draggable: true }).addTo(map);
      function sync(i) {
        qs('#eq-lat').value = i.lat.toFixed(6);
        qs('#eq-lng').value = i.lng.toFixed(6);
      }
      map.on('click', function (ev) {
        marker.setLatLng(ev.latlng);
        sync(ev.latlng);
      });
      marker.on('dragend', function () { sync(marker.getLatLng()); });
    }

    function openModal(id) {
      var m = qs(id);
      if (!m) return;
      m.classList.add('is-open');
      m.setAttribute('aria-hidden', 'false');
      if (id === '#modal-equipment') setTimeout(initMap, 80);
    }

    var btnCreate = qs('#btn-open-create');
    if (btnCreate) {
      btnCreate.addEventListener('click', function () {
        qs('#modal-title').textContent = 'Nouvel équipement';
        qs('#eq-id').value = '';
        qs('#eq-name').value = '';
        qs('#eq-location').value = '';
        qs('#eq-status').value = 'available';
        qs('#eq-lm').value = '';
        qs('#eq-lat').value = '';
        qs('#eq-lng').value = '';
        qs('#eq-type').selectedIndex = 0;
        qs('#eq-delete-id').value = '';
        var df = qs('#form-equipment-delete');
        if (df) df.style.display = 'none';
        openModal('#modal-equipment');
      });
    }

    qsa('.btn-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = JSON.parse(btn.getAttribute('data-row'));
        qs('#modal-title').textContent = 'Modifier équipement';
        qs('#eq-id').value = row.id;
        qs('#eq-name').value = row.name;
        qs('#eq-status').value = row.status;
        qs('#eq-location').value = row.location || '';
        qs('#eq-type').value = String(row.type_id);
        qs('#eq-lm').value = row.last_maintenance ? String(row.last_maintenance).slice(0, 10) : '';
        qs('#eq-lat').value = row.latitude != null ? row.latitude : '';
        qs('#eq-lng').value = row.longitude != null ? row.longitude : '';
        qs('#eq-delete-id').value = row.id;
        var df = qs('#form-equipment-delete');
        if (df) df.style.display = 'block';
        openModal('#modal-equipment');
      });
    });

    var checkAll = qs('#check-all');
    if (checkAll) {
      checkAll.addEventListener('change', function () {
        qsa('input[name="equipment_ids[]"]').forEach(function (c) { c.checked = checkAll.checked; });
      });
    }

    var btnTypeNew = qs('#btn-type-new');
    if (btnTypeNew) {
      btnTypeNew.addEventListener('click', function () {
        qs('#type-modal-title').textContent = 'Nouveau type';
        qs('#type-id').value = '';
        qs('#type-name').value = '';
        qs('#type-icon').value = '📦';
        qs('#type-cost').value = '0';
        qs('#type-war').value = '12';
        qs('#type-maint').value = '6';
        openModal('#modal-type');
      });
    }

    qsa('.btn-type-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = JSON.parse(btn.getAttribute('data-row'));
        qs('#type-modal-title').textContent = 'Modifier type';
        qs('#type-id').value = row.id;
        qs('#type-name').value = row.category_name;
        qs('#type-icon').value = row.icon;
        qs('#type-cost').value = row.daily_cost;
        qs('#type-war').value = row.warranty_months;
        qs('#type-maint').value = row.default_maintenance_frequency_months;
        openModal('#modal-type');
      });
    });

    qsa('.btn-open-reject').forEach(function (btn) {
      btn.addEventListener('click', function () {
        qs('#reject-id').value = btn.getAttribute('data-id');
        openModal('#modal-reject');
      });
    });
  });
})();
