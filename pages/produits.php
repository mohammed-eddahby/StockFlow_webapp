<?php require_once "../auth.php"; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StockFlow – Produits</title>
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
    <a href="produits.php"    class="nav-link active"><span class="nav-icon">⬛</span> Produits</a>
    <a href="mouvements.php"  class="nav-link"><span class="nav-icon">⇅</span> Mouvements</a>
    <a href="alertes.php"     class="nav-link"><span class="nav-icon">⚠</span> Alertes</a>
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
    <h1 class="page-title">Produits</h1>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
    <button class="btn btn-primary" onclick="openModal('modal-add')">+ Nouveau produit</button>
    <?php endif; ?>
  </header>

  <div class="page-content">
  <div class="section-block">
    <!-- Filter bar -->
    <div class="filter-bar">
      <input type="text" id="search" placeholder="Rechercher un produit…" oninput="renderTable()" />
      <select id="filter-cat" onchange="renderTable()">
        <option value="">Toutes les catégories</option>
      </select>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Référence</th>
            <th>Nom</th>
            <th>Catégorie</th>
            <th>Prix unitaire</th>
            <th>Stock actuel</th>
            <th>Seuil alerte</th>
            <th>État</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="prod-body">
          <tr><td colspan="8" class="empty">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  </div>
</main>

<!-- ============ MODAL AJOUTER ============ -->
<div class="modal-overlay" id="modal-add" onclick="if(event.target===this) closeModal('modal-add')">
  <div class="modal">
    <div class="modal-title">Nouveau produit</div>
    <div class="field"><label>Référence *</label><input id="add-ref" type="text" /></div>
    <div class="field"><label>Nom *</label><input id="add-nom" type="text" /></div>
    <div class="field"><label>Catégorie *</label><select id="add-cat"></select></div>
    <div class="field"><label>Description</label><textarea id="add-desc" rows="2"></textarea></div>
    <div class="field"><label>Prix unitaire (DH)</label><input id="add-prix" type="number" step="0.01" min="0" value="0" /></div>
    <div class="field"><label>Seuil d'alerte</label><input id="add-seuil" type="number" min="0" value="5" /></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-add')">Annuler</button>
      <button class="btn btn-primary" onclick="ajouterProduit()">Enregistrer</button>
    </div>
  </div>
</div>

<!-- ============ MODAL MODIFIER ============ -->
<div class="modal-overlay" id="modal-edit" onclick="if(event.target===this) closeModal('modal-edit')">
  <div class="modal">
    <div class="modal-title">Modifier le produit</div>
    <input type="hidden" id="edit-id" />
    <div class="field"><label>Référence *</label><input id="edit-ref" type="text" /></div>
    <div class="field"><label>Nom *</label><input id="edit-nom" type="text" /></div>
    <div class="field"><label>Catégorie *</label><select id="edit-cat"></select></div>
    <div class="field"><label>Description</label><textarea id="edit-desc" rows="2"></textarea></div>
    <div class="field"><label>Prix unitaire (DH)</label><input id="edit-prix" type="number" step="0.01" min="0" /></div>
    <div class="field"><label>Seuil d'alerte</label><input id="edit-seuil" type="number" min="0" /></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-edit')">Annuler</button>
      <button class="btn btn-primary" onclick="modifierProduit()">Enregistrer</button>
    </div>
  </div>
</div>

<script src="../js/app.js"></script>
<script>
const isAdmin = <?php echo $_SESSION['user_role'] === 'admin' ? 'true' : 'false'; ?>;
let produits = [];
let categories = [];

// Chargement initial
async function charger() {
  [produits, categories] = await Promise.all([api('liste_stock'), api('liste_categories')]);

  // Peupler les selects catégories
  ['add-cat','edit-cat','filter-cat'].forEach(id => {
    const sel = document.getElementById(id);
    const hasAll = id === 'filter-cat';
    if (!hasAll) sel.innerHTML = '';
    categories.forEach(c => {
      const o = document.createElement('option');
      o.value = c.id; o.textContent = c.nom;
      sel.appendChild(o);
    });
  });

  renderTable();
}

function renderTable() {
  const q   = document.getElementById('search').value.toLowerCase();
  const cat = document.getElementById('filter-cat').value;
  const tbody = document.getElementById('prod-body');

  const filtered = produits.filter(p =>
    (!q   || p.nom.toLowerCase().includes(q) || p.reference.toLowerCase().includes(q)) &&
    (!cat || p.categorie_id == cat || p.categorie == categories.find(c=>c.id==cat)?.nom)
  );

  if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty">Aucun produit trouvé</td></tr>'; return; }

  tbody.innerHTML = filtered.map(p => {
    const alerte = p.stock_actuel <= p.seuil_alerte;
    const rupture = p.stock_actuel <= 0;
    return `<tr>
      <td><code>${p.reference}</code></td>
      <td>${p.nom}</td>
      <td>${p.categorie}</td>
      <td>${parseFloat(p.prix_unitaire).toFixed(2)} DH</td>
      <td class="${rupture?'txt-danger':alerte?'txt-warn':''}">${p.stock_actuel}</td>
      <td>${p.seuil_alerte}</td>
      <td><span class="badge ${rupture?'badge-danger':alerte?'badge-warn':'badge-ok'}">${rupture?'Rupture':alerte?'Alerte':'OK'}</span></td>
      <td>
        ${isAdmin ? `<button class="btn btn-secondary btn-sm" onclick='editer(${JSON.stringify(p)})'>✎ Modifier</button>
        <button class="btn btn-danger btn-sm" onclick="supprimer(${p.id}, '${p.nom.replace(/'/g,"\\'")}')">✕</button>` : '<span style="color:var(--txt2)">–</span>'}
      </td>
    </tr>`;
  }).join('');
}

async function ajouterProduit() {
  const data = {
    reference: document.getElementById('add-ref').value.trim(),
    nom: document.getElementById('add-nom').value.trim(),
    categorie_id: document.getElementById('add-cat').value,
    description: document.getElementById('add-desc').value,
    prix_unitaire: document.getElementById('add-prix').value,
    seuil_alerte: document.getElementById('add-seuil').value,
  };
  if (!data.reference || !data.nom) { toast('Référence et nom obligatoires', 'danger'); return; }
  const res = await api('ajouter_produit', 'POST', data);
  if (res.ok) { toast('Produit ajouté ✓'); closeModal('modal-add'); await charger(); }
  else toast(res.erreur || 'Erreur', 'danger');
}

function editer(p) {
  document.getElementById('edit-id').value = p.id;
  document.getElementById('edit-ref').value = p.reference;
  document.getElementById('edit-nom').value = p.nom;
  document.getElementById('edit-desc').value = p.description || '';
  document.getElementById('edit-prix').value = p.prix_unitaire;
  document.getElementById('edit-seuil').value = p.seuil_alerte;
  // Trouver la catégorie
  const catNom = p.categorie;
  const cat = categories.find(c => c.nom === catNom);
  if (cat) document.getElementById('edit-cat').value = cat.id;
  openModal('modal-edit');
}

async function modifierProduit() {
  const data = {
    id: document.getElementById('edit-id').value,
    reference: document.getElementById('edit-ref').value.trim(),
    nom: document.getElementById('edit-nom').value.trim(),
    categorie_id: document.getElementById('edit-cat').value,
    description: document.getElementById('edit-desc').value,
    prix_unitaire: document.getElementById('edit-prix').value,
    seuil_alerte: document.getElementById('edit-seuil').value,
  };
  const res = await api('modifier_produit', 'POST', data);
  if (res.ok) { toast('Produit mis à jour ✓'); closeModal('modal-edit'); await charger(); }
  else toast(res.erreur || 'Erreur', 'danger');
}

async function supprimer(id, nom) {
  if (!confirm(`Supprimer "${nom}" et tous ses mouvements ?`)) return;
  const res = await api('supprimer_produit', 'POST', { id });
  if (res.ok) { toast('Produit supprimé'); await charger(); }
  else toast(res.erreur || 'Erreur', 'danger');
}

charger();
</script>
</body>
</html>
