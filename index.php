<?php
require_once 'db.php';
require_once 'auth_check.php';
$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();

$page_title = 'Gestion des Protocoles';
$show_page_header = true;
$page_header_icon = 'fas fa-file-alt';
$page_header_title = 'Gestion des Protocoles Opératoires';
$page_header_description = 'Ajoutez et gérez les protocoles opératoires des patients';
require_once 'header.php';

// Récupérer le nom du mois actuel en français
setlocale(LC_TIME, 'fr_FR.UTF-8');
$mois_actuel = strftime('%B'); // par exemple "Novembre"

// Récupérer le nombre de protocoles créés ce mois-ci
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$current_month_count = $pdo->query("SELECT COUNT(*) 
                                    FROM protocoles 
                                    WHERE date_operation BETWEEN '$current_month_start' AND '$current_month_end'")
    ->fetchColumn();

// Calculer la période du trimestre
$mois_numero = date('n'); // Obtenir le mois sous forme de numéro (1 pour janvier, 2 pour février, etc.)
$trimestre_periode = '';

// Définir la période en fonction du mois
if ($mois_numero >= 1 && $mois_numero <= 3) {
    $trimestre_periode = 'Janvier - Mars';
} elseif ($mois_numero >= 4 && $mois_numero <= 6) {
    $trimestre_periode = 'Avril - Juin';
} elseif ($mois_numero >= 7 && $mois_numero <= 9) {
    $trimestre_periode = 'Juillet - Septembre';
} else {
    $trimestre_periode = 'Octobre - Décembre';
}

// Récupérer le nombre de protocoles créés ce trimestre
$current_month = date('n');
$current_year = date('Y');
$quarter = ceil($current_month / 3);
$current_quarter_start = date('Y-m-d', mktime(0, 0, 0, ($quarter - 1) * 3 + 1, 1, $current_year));
$current_quarter_end = date('Y-m-t', mktime(0, 0, 0, $quarter * 3, 1, $current_year));
$current_quarter_count = $pdo->query("SELECT COUNT(*) 
                                     FROM protocoles
                                     WHERE date_operation BETWEEN '$current_quarter_start' AND '$current_quarter_end'")
    ->fetchColumn();

// Définir les dates de début et de fin de l'année
$current_year_start = "$current_year-01-01";  // 1er janvier de l'année actuelle
$current_year_end = "$current_year-12-31";    // 31 décembre de l'année actuelle

// Requête pour obtenir le nombre de protocoles pour l'année complète
$current_year_count = $pdo->query(
    "SELECT COUNT(*) 
     FROM protocoles
     WHERE date_operation BETWEEN '$current_year_start' AND '$current_year_end' 
     AND YEAR(date_operation) = '$current_year'"
)

    ->fetchColumn();



// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_fields = ['nom_patient', 'prenom_patient', 'operateur'];

// Construction de la requête
$where_conditions = [];
$params = [];
if ($search) {
    foreach ($search_fields as $field) {
        $where_conditions[] = "$field LIKE ?";
        $params[] = "%$search%";
    }
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' OR ', $where_conditions);
}

// Requête pour le total
$count_sql = "SELECT COUNT(*) FROM protocoles $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $items_per_page);

// Requête principale avec tri
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'date_operation';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$allowed_columns = ['num_protocole', 'nom_patient', 'prenom_patient', 'date_operation', 'operateur'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'date_operation';
}
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

$sql = "SELECT * FROM protocoles $where_clause 
        ORDER BY $sort_column $sort_order 
        LIMIT $items_per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$protocoles = $stmt->fetchAll();

// Fonction pour générer les liens de tri
function sortLink($column, $currentSort, $currentOrder, $search)
{
    $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    $params = [
        'sort' => $column,
        'order' => $newOrder
    ];
    if ($search) {
        $params['search'] = $search;
    }
    return '?' . http_build_query($params);
}

// Fonction pour afficher l'icône de tri
function sortIcon($column, $currentSort, $currentOrder)
{
    if ($currentSort === $column) {
        return $currentOrder === 'ASC' ? '↑' : '↓';
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Protocoles Opératoires</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/css2.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Total des protocoles</h5>
                    <h2><?php echo $total_records; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Total de Protocoles en <?php echo ucfirst($mois_actuel); ?></h5>
                    <h2><?php echo isset($current_month_count) ? $current_month_count : 0; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Total de Protocoles de ce trimestre (<?php echo $trimestre_periode; ?>)</h5>
                    <h2><?php echo isset($current_quarter_count) ? $current_quarter_count : 0; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5>Total de Protocoles de l'année
                        <script>document.write(new Date().getFullYear());</script>
                    </h5>
                    <h2><?php echo isset($current_year_count) ? $current_year_count : 0; ?></h2>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-0">Protocoles Opératoires</h3>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <div class="search-box">
                            <input type="text" class="form-control" id="searchInput" placeholder="Rechercher...">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <a href="ajouter_protocole.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nouveau protocole
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="<?php echo sortLink('id', $sort_column, $sort_order, $search); ?>"
                                        class="text-decoration-none text-dark">
                                        ID <?php echo sortIcon('id', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo sortLink('num_protocole', $sort_column, $sort_order, $search); ?>"
                                        class="text-decoration-none text-dark">
                                        N° protocole <?php echo sortIcon('num_protocole', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo sortLink('nom_patient', $sort_column, $sort_order, $search); ?>"
                                        class="text-decoration-none text-dark">
                                        Nom <?php echo sortIcon('nom_patient', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo sortLink('prenom_patient', $sort_column, $sort_order, $search); ?>"
                                        class="text-decoration-none text-dark">
                                        Prénom <?php echo sortIcon('prenom_patient', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo sortLink('date_operation', $sort_column, $sort_order, $search); ?>"
                                        class="text-decoration-none text-dark">
                                        Date Opération
                                        <?php echo sortIcon('date_operation', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo sortLink('operateur', $sort_column, $sort_order, $search); ?>"
                                        class="text-decoration-none text-dark">
                                        Opérateur <?php echo sortIcon('operateur', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $counter = 1;
                            foreach ($protocoles as $protocole): ?>
                                <tr>
                                    <td><?php echo $counter; ?></td>
                                    <td><?php echo htmlspecialchars($protocole['num_protocole']); ?></td>
                                    <td><?php echo htmlspecialchars($protocole['nom_patient']); ?></td>
                                    <td><?php echo htmlspecialchars($protocole['prenom_patient']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($protocole['date_operation'])); ?></td>
                                    <td><?php echo htmlspecialchars($protocole['operateur']); ?></td>
                                    <td class="text-center">
                                        <a href="voir_protocole.php?id=<?php echo $protocole['id']; ?>"
                                            class="btn btn-info btn-action" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="modifier_protocole.php?id=<?php echo $protocole['id']; ?>"
                                            class="btn btn-warning btn-action" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="imprimer_protocole.php?id=<?php echo $protocole['id']; ?>"
                                            class="btn btn-print btn action" title="Imprimer" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-action"
                                            onclick="confirmDelete(<?php echo $protocole['id']; ?>)" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                                $counter++;
                            endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navigation des pages">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                    Précédent
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                    Suivant
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer ce protocole ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Supprimer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/popper/popper.min.js"></script>
    <script src="assets/bootstrap/bootstrap.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            $('#deleteModal').modal('show');
            $('#confirmDelete').attr('href', 'supprimer_protocole.php?id=' + id);
        }

        // Recherche dynamique   
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Animation pour les messages de succès/erreur
        $('.alert-float').delay(5000).fadeOut(500);
    </script>
</body>

</html>

<?php include 'footer.php'; ?>