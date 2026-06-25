<?php require_once "../auth.php"; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StockFlow – Alertes</title>
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
    <a href="mouvements.php" class="nav-link"><span class="nav-icon">⇅</span> Mouvements</a>
    <a href="alertes.php"    class="nav-link active"><span class="nav-icon">⚠</span> Alertes</a>
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
    <h1 class="page-title">Alertes de stock</h1>
    <span id="resume" style="color:rgba(255,255,255,.65);font-size:.9rem"></span>
  </header>

  <div class="page-content">
  <!-- Ruptures totales -->
  <div class="section-block" style="margin-bottom:24px">
    <div class="section-header">
      <h2 class="section-title" style="color:var(--out)">🔴 Ruptures totales (stock = 0)</h2>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Produit</th><th>Référence</th><th>Stock</th><th>Seuil</th><th>Action rapide</th></tr>
        </thead>
        <tbody id="rupture-body">
          <tr><td colspan="5" class="empty">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Alerte seuil -->
  <div class="section-block">
    <div class="section-header">
      <h2 class="section-title" style="color:var(--warn)">🟡 Sous le seuil d'alerte</h2>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Produit</th><th>Référence</th><th>Stock actuel</th><th>Seuil</th><th>Manque</th><th>Action rapide</th></tr>
        </thead>
        <tbody id="alerte-body">
          <tr><td colspan="6" class="empty">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  </div>
</main>

<!-- Modal réapprovisionnement rapide -->
<div class="modal-overlay" id="modal-reap" onclick="if(event.target===this) closeModal('modal-reap')">
  <div class="modal">
    <div class="modal-title">Réapprovisionner</div>
    <input type="hidden" id="reap-id" />
    <p id="reap-produit" style="margin-bottom:16px;color:var(--txt2)"></p>
    <div class="field"><label>Quantité à ajouter *</label><input id="reap-qte" type="number" min="1" value="10" /></div>
    <div class="field"><label>Commentaire</label><input id="reap-comment" type="text" value="Réapprovisionnement urgence" /></div>
    <div class="field"><label>Utilisateur</label><input id="reap-user" type="text" /></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-reap')">Annuler</button>
      <button class="btn btn-in" onclick="reapprovisionner()">↑ Enregistrer l'entrée</button>
    </div>
  </div>
</div>

<script src="../js/app.js"></script>
<script>
async function charger() {
  const rows = await api('alertes');
  const ruptures = rows.filter(r => r.stock_actuel <= 0);
  const alertes  = rows.filter(r => r.stock_actuel > 0);

  document.getElementById('resume').textContent = `${ruptures.length} rupture(s) · ${alertes.length} alerte(s)`;

  // Ruptures
  const rb = document.getElementById('rupture-body');
  rb.innerHTML = ruptures.length
    ? ruptures.map(r => `<tr>
        <td>${r.nom}</td>
        <td><code>${r.reference}</code></td>
        <td class="txt-danger">${r.stock_actuel}</td>
        <td>${r.seuil_alerte}</td>
        <td><button class="btn btn-in btn-sm" onclick="ouvreReap(${r.id},'${r.nom.replace(/'/g,"\\'")}')">↑ Réapprovisionner</button></td>
      </tr>`).join('')
    : '<tr><td colspan="5" class="empty">Aucune rupture totale ✓</td></tr>';

  // Alertes (stock > 0 mais <= seuil)
  const ab = document.getElementById('alerte-body');
  ab.innerHTML = alertes.length
    ? alertes.map(r => `<tr>
        <td>${r.nom}</td>
        <td><code>${r.reference}</code></td>
        <td class="txt-warn">${r.stock_actuel}</td>
        <td>${r.seuil_alerte}</td>
        <td>${r.seuil_alerte - r.stock_actuel}</td>
        <td><button class="btn btn-secondary btn-sm" onclick="ouvreReap(${r.id},'${r.nom.replace(/'/g,"\\'")}')">↑ Réapprovisionner</button></td>
      </tr>`).join('')
    : '<tr><td colspan="6" class="empty">Tous les stocks sont au-dessus du seuil ✓</td></tr>';
}

function ouvreReap(id, nom) {
  document.getElementById('reap-id').value = id;
  document.getElementById('reap-produit').textContent = `Produit : ${nom}`;
  openModal('modal-reap');
}

async function reapprovisionner() {
  const data = {
    produit_id:     document.getElementById('reap-id').value,
    type_mouvement: 'entree',
    quantite:       parseInt(document.getElementById('reap-qte').value),
    commentaire:    document.getElementById('reap-comment').value,
    utilisateur:    document.getElementById('reap-user').value,
  };
  if (!data.quantite || data.quantite < 1) { toast('Quantité invalide', 'danger'); return; }
  const res = await api('ajouter_mouvement', 'POST', data);
  if (res.ok) { toast('Stock mis à jour ✓'); closeModal('modal-reap'); await charger(); }
  else toast(res.erreur || 'Erreur', 'danger');
}

charger();
</script>
</body>
</html>
