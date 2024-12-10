<?php
require_once 'db.php';
require_once 'auth_check.php';
$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();



$page_title = 'Gestion des Utilisateurs';
$show_page_header = true;
$page_header_icon = 'fas fa-users';
$page_header_title = 'Gestion des Comptes Utilisateurs';
$page_header_description = 'Ajoutez, modifiez et supprimez des comptes utilisateurs';
require_once 'header.php';

// Pagination and Search
$items_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_fields = ['username', 'role'];

// Construct search query
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

// Count total users
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $items_per_page);

// Fetch users
$sql = "SELECT id, username, password, role, nom, prenom, derniere_connexion FROM users 
        $where_clause 
        LIMIT $items_per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to generate sort links (similar to index.php)
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

// Function to display sort icon
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
    <title>Gestion des Utilisateurs</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/css2.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-0">Comptes Utilisateurs</h3>
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
                    <a href="ajouter_utilisateur.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nouvel utilisateur
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom d'utilisateur</th>
                                <th>Mot de passe</th>
                                <th>Rôle</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Dernière connexion</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?php echo $index + 1 + $offset; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo str_repeat('*', strlen($user['password'])); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                                    <td>
                                        <?php 
                                        echo $user['derniere_connexion'] ? 
                                            date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : 
                                            'Jamais'; 
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="modifier_utilisateur.php?id=<?php echo $user['id']; ?>"
                                            class="btn btn-warning btn-action" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['username'] !== $_SESSION['username']): ?>
                                            <button type="button" class="btn btn-danger btn-action"
                                                onclick="confirmDelete(<?php echo $user['id']; ?>)" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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

        <!-- Delete Confirmation Modal -->
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
                        Êtes-vous sûr de vouloir supprimer cet utilisateur ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <a href="#" id="confirmDelete" class="btn btn-danger">Supprimer</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/popper/popper.min.js"></script>
    <script src="assets/bootstrap/bootstrap.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            $('#deleteModal').modal('show');
            $('#confirmDelete').attr('href', 'supprimer_utilisateur.php?id=' + id);
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
    </script>
</body>
</html>

<?php include 'footer.php'; ?>