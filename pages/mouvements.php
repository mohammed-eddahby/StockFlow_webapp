<?php require_once "../auth.php"; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StockFlow – Mouvements</title>
  <link rel="stylesheet" href="../css/style.css" />
  <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.94/build/spline-viewer.js"></script>
</head>
<body>

<!-- Arrière-plan 3D (identique à la page de connexion) -->
<div id="canvas-container">
  <spline-viewer url="https://prod.spline.design/5lhVrjClMBbInbTG/scene.splinecode" loading="eager"></spline-viewer>
</div>


<aside class="sidebar">
  <div class="brand"><span class="brand-icon">⬡</span><span class="brand-name">StockFlow</span></div>
  <nav>
    <a href="../dashboard.php" class="nav-link"><span class="nav-icon">▦</span> Tableau de bord</a>
    <a href="produits.php"   class="nav-link"><span class="nav-icon">⬛</span> Produits</a>
    <a href="mouvements.php" class="nav-link active"><span class="nav-icon">⇅</span> Mouvements</a>
    <a href="alertes.php"    class="nav-link"><span class="nav-icon">⚠</span> Alertes</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <span class="sidebar-user-name">👤 <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span>
      <span class="sidebar-user-role"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
    </div>
    <a href="../logout.php" class="sidebar-logout">⎋ Déconnexion</a>
  </div>
</aside>

<main class="main">
  <header class="topbar">
    <h1 class="page-title">Mouvements de stock</h1>
    <button class="btn btn-primary" onclick="openModal('modal-add')">+ Nouveau mouvement</button>
  </header>

  <div class="page-content">
  <div class="section-block">
    <div class="filter-bar">
      <select id="filter-produit" onchange="renderTable()">
        <option value="">Tous les produits</option>
      </select>
      <select id="filter-type" onchange="renderTable()">
        <option value="">Entrées & Sorties</option>
        <option value="entree">Entrées seulement</option>
        <option value="sortie">Sorties seulement</option>
      </select>
      <span class="spacer"></span>
      <span id="total-label" style="font-size:.85rem;color:var(--txt2)"></span>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Produit</th>
            <th>Type</th>
            <th>Quantité</th>
            <th>Commentaire</th>
            <th>Utilisateur</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="mouv-body">
          <tr><td colspan="7" class="empty">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  </div>
</main>

<!-- ============ MODAL AJOUTER MOUVEMENT ============ -->
<div class="modal-overlay" id="modal-add" onclick="if(event.target===this) closeModal('modal-add')">
  <div class="modal">
    <div class="modal-title">Enregistrer un mouvement</div>
    <div class="field">
      <label>Type de mouvement *</label>
      <div style="display:flex;gap:10px;">
        <button id="btn-entree" class="btn btn-in" style="flex:1" onclick="setType('entree')">↑ Entrée (livraison)</button>
        <button id="btn-sortie" class="btn btn-secondary" style="flex:1" onclick="setType('sortie')">↓ Sortie (vente)</button>
      </div>
      <input type="hidden" id="add-type" value="entree" />
    </div>
    <div class="field"><label>Produit *</label><select id="add-produit"></select></div>
    <div class="field"><label>Quantité *</label><input id="add-qte" type="number" min="1" value="1" /></div>
    <div class="field"><label>Commentaire</label><input id="add-comment" type="text" placeholder="Vente comptoir, livraison fournisseur…" /></div>
    <div class="field"><label>Utilisateur</label><input id="add-user" type="text" placeholder="Votre prénom" /></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-add')">Annuler</button>
      <button class="btn btn-primary" onclick="ajouterMouvement()">Enregistrer</button>
    </div>
  </div>
</div>

<script src="../js/app.js"></script>
<script>
const isAdmin = <?php echo $_SESSION['user_role'] === 'admin' ? 'true' : 'false'; ?>;
let mouvements = [];
let produits   = [];

async function charger() {
  [mouvements, produits] = await Promise.all([api('liste_mouvements'), api('liste_stock')]);

  // Peupler les selects
  ['add-produit', 'filter-produit'].forEach(id => {
    const sel = document.getElementById(id);
    const isFilter = id === 'filter-produit';
    if (!isFilter) sel.innerHTML = '';
    produits.forEach(p => {
      const o = document.createElement('option');
      o.value = p.id;
      o.textContent = `${p.nom} (stock: ${p.stock_actuel})`;
      sel.appendChild(o);
    });
  });

  renderTable();
}

function renderTable() {
  const fp = document.getElementById('filter-produit').value;
  const ft = document.getElementById('filter-type').value;
  const tbody = document.getElementById('mouv-body');

  const filtered = mouvements.filter(m =>
    (!fp || m.produit_id == fp) &&
    (!ft || m.type_mouvement === ft)
  );

  document.getElementById('total-label').textContent = `${filtered.length} mouvement(s)`;

  if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty">Aucun mouvement</td></tr>'; return; }

  tbody.innerHTML = filtered.map(m => `
    <tr>
      <td>${fmtDate(m.date_mouvement)}</td>
      <td>${m.produit_nom}</td>
      <td><span class="badge ${m.type_mouvement==='entree'?'badge-in':'badge-out'}">${m.type_mouvement==='entree'?'↑ Entrée':'↓ Sortie'}</span></td>
      <td>${m.quantite}</td>
      <td>${m.commentaire || '–'}</td>
      <td>${m.utilisateur || '–'}</td>
      <td>${isAdmin ? `<button class="btn btn-danger btn-sm" onclick="supprimer(${m.id})">✕</button>` : ''}</td>
    </tr>`).join('');
}

function setType(type) {
  document.getElementById('add-type').value = type;
  document.getElementById('btn-entree').className = `btn btn-${type==='entree'?'in':'secondary'}`;
  document.getElementById('btn-sortie').className = `btn btn-${type==='sortie'?'out':'secondary'}`;
}

async function ajouterMouvement() {
  const data = {
    produit_id:     document.getElementById('add-produit').value,
    type_mouvement: document.getElementById('add-type').value,
    quantite:       parseInt(document.getElementById('add-qte').value),
    commentaire:    document.getElementById('add-comment').value,
    utilisateur:    document.getElementById('add-user').value,
  };
  if (!data.quantite || data.quantite < 1) { toast('Quantité invalide', 'danger'); return; }
  const res = await api('ajouter_mouvement', 'POST', data);
  if (res.ok) { toast('Mouvement enregistré ✓'); closeModal('modal-add'); await charger(); }
  else toast(res.erreur || 'Erreur', 'danger');
}

async function supprimer(id) {
  if (!confirm('Supprimer ce mouvement ?')) return;
  const res = await api('supprimer_mouvement', 'POST', { id });
  if (res.ok) { toast('Mouvement supprimé'); await charger(); }
  else toast(res.erreur || 'Erreur', 'danger');
}

charger();
</script>
</body>
</html>
