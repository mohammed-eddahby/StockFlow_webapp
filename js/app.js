/* app.js – utilitaires partagés */

const API_BASE = location.pathname.includes('/pages/') ? '../api.php' : 'api.php';

async function api(action, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const url = `${API_BASE}?action=${action}`;
  const res = await fetch(url, opts);
  return res.json();
}

function fmtDate(dt) {
  if (!dt) return '–';
  const d = new Date(dt);
  return d.toLocaleDateString('fr-FR') + ' ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function toast(msg, type = 'ok') {
  let t = document.getElementById('global-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'global-toast';
    t.className = 'toast';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.className = `toast toast-${type} show`;
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3000);
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

/* Garde la scène 3D Spline (arrière-plan) active, comme sur la page de connexion */
(function keepSplineAlive() {
  const viewer = document.querySelector('spline-viewer');
  if (!viewer) return;
  viewer.addEventListener('load', () => {
    const canvas = viewer.shadowRoot?.querySelector('#canvas3d');
    if (!canvas) return;
    let moveToggle = false;
    setInterval(() => {
      moveToggle = !moveToggle;
      const fakeX = moveToggle ? 101 : 100;
      canvas.dispatchEvent(new MouseEvent('mousemove', { clientX: fakeX, clientY: 100, bubbles: true }));
    }, 500);
  });
})();
