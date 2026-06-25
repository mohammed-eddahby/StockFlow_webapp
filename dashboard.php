<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StockFlow – Tableau de bord</title>
  <link rel="stylesheet" href="css/style.css" />
  <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.94/build/spline-viewer.js"></script>
</head>
<body>

<!-- Arrière-plan 3D (identique à la page de connexion) -->
<div id="canvas-container">
  <spline-viewer url="https://prod.spline.design/5lhVrjClMBbInbTG/scene.splinecode" loading="eager"></spline-viewer>
</div>


<!-- ======= SIDEBAR ======= -->
<aside class="sidebar">
  <div class="brand">
    <span class="brand-icon">⬡</span>
    <span class="brand-name">StockFlow</span>
  </div>
  <nav>
    <a href="dashboard.php" class="nav-link active">
      <span class="nav-icon">▦</span> Tableau de bord
    </a>
    <a href="pages/produits.php" class="nav-link">
      <span class="nav-icon">⬛</span> Produits
    </a>
    <a href="pages/mouvements.php" class="nav-link">
      <span class="nav-icon">⇅</span> Mouvements
    </a>
    <a href="pages/alertes.php" class="nav-link">
      <span class="nav-icon">⚠</span> Alertes
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <span class="sidebar-user-name">👤 <?php echo htmlspecialchars($_SESSION['user_nom']); ?></span>
      <span class="sidebar-user-role"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
    </div>
    <a href="logout.php" class="sidebar-logout">⎋ Déconnexion</a>
  </div>
</aside>

<!-- ======= MAIN ======= -->
<main class="main">
  <header class="topbar">
    <h1 class="page-title">Tableau de bord</h1>
    <span class="date" id="today"></span>
  </header>

  <div class="page-content">
  <!-- KPI cards -->
  <section class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-label">Produits</div>
      <div class="kpi-value" id="nb-produits">–</div>
    </div>
    <div class="kpi-card kpi-danger" id="card-alertes">
      <div class="kpi-label">En alerte</div>
      <div class="kpi-value" id="nb-alertes">–</div>
    </div>
    <div class="kpi-card kpi-in">
      <div class="kpi-label">Entrées (total)</div>
      <div class="kpi-value" id="nb-entrees">–</div>
    </div>
    <div class="kpi-card kpi-out">
      <div class="kpi-label">Sorties (total)</div>
      <div class="kpi-value" id="nb-sorties">–</div>
    </div>
    <div class="kpi-card kpi-val">
      <div class="kpi-label">Valeur du stock</div>
      <div class="kpi-value" id="val-stock">–</div>
    </div>
  </section>

  <!-- Alert table -->
  <section class="section-block">
    <div class="section-header">
      <h2 class="section-title">⚠ Produits en rupture / alerte</h2>
      <a href="pages/alertes.php" class="btn-link">Voir tout →</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Référence</th>
            <th>Stock actuel</th>
            <th>Seuil</th>
            <th>État</th>
          </tr>
        </thead>
        <tbody id="alerte-body">
          <tr><td colspan="5" class="empty">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Recent movements -->
  <section class="section-block">
    <div class="section-header">
      <h2 class="section-title">Derniers mouvements</h2>
      <a href="pages/mouvements.php" class="btn-link">Tout voir →</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Date</th><th>Produit</th><th>Type</th><th>Qté</th><th>Commentaire</th><th>Utilisateur</th></tr>
        </thead>
        <tbody id="mouvements-body">
          <tr><td colspan="6" class="empty">Chargement…</td></tr>
        </tbody>
      </table>
    </div>
  </section>
  </div>
</main>

<script src="js/app.js"></script>
<script>
  document.getElementById('today').textContent = new Date().toLocaleDateString('fr-FR',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

  // KPI
  api('stats').then(d => {
    if (d.erreur) { console.error('stats:', d.erreur); toast('Erreur chargement statistiques', 'danger'); return; }
    document.getElementById('nb-produits').textContent = d.nb_produits;
    document.getElementById('nb-alertes').textContent  = d.nb_alertes;
    document.getElementById('nb-entrees').textContent  = d.nb_entrees;
    document.getElementById('nb-sorties').textContent  = d.nb_sorties;
    document.getElementById('val-stock').textContent   = d.val_stock.toFixed(2) + ' DH';
    if (d.nb_alertes > 0) document.getElementById('card-alertes').classList.add('has-alert');
  }).catch(err => console.error('stats:', err));

  // Alertes
  api('alertes').then(rows => {
    if (rows.erreur) { console.error('alertes:', rows.erreur); return; }
    const tbody = document.getElementById('alerte-body');
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="5" class="empty">Aucun produit en alerte ✓</td></tr>'; return; }
    tbody.innerHTML = rows.map(r => `
      <tr>
        <td>${r.nom}</td>
        <td><code>${r.reference}</code></td>
        <td class="${r.stock_actuel <= 0 ? 'txt-danger' : 'txt-warn'}">${r.stock_actuel}</td>
        <td>${r.seuil_alerte}</td>
        <td><span class="badge ${r.stock_actuel <= 0 ? 'badge-danger' : 'badge-warn'}">${r.stock_actuel <= 0 ? 'Rupture' : 'Alerte'}</span></td>
      </tr>`).join('');
  }).catch(err => console.error('alertes:', err));

  // Derniers mouvements (10)
  api('liste_mouvements').then(rows => {
    if (rows.erreur) { console.error('liste_mouvements:', rows.erreur); return; }
    const tbody = document.getElementById('mouvements-body');
    const last10 = rows.slice(0, 10);
    if (!last10.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty">Aucun mouvement enregistré</td></tr>'; return; }
    tbody.innerHTML = last10.map(m => `
      <tr>
        <td>${fmtDate(m.date_mouvement)}</td>
        <td>${m.produit_nom}</td>
        <td><span class="badge ${m.type_mouvement==='entree'?'badge-in':'badge-out'}">${m.type_mouvement === 'entree' ? '↑ Entrée' : '↓ Sortie'}</span></td>
        <td>${m.quantite}</td>
        <td>${m.commentaire || '–'}</td>
        <td>${m.utilisateur || '–'}</td>
      </tr>`).join('');
  }).catch(err => console.error('liste_mouvements:', err));
</script>
</body>
</html>
