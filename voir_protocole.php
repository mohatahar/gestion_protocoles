<?php
require_once 'db.php';
require_once 'auth_check.php';
require_once 'header.php';

$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();

// Vérifier si un ID a été passé dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Protocole non spécifié.");
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM protocoles WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        die("Aucun protocole trouvé avec cet ID.");
    }

    $protocole = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protocole</title>

    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-gray: #f8f9fa;
            --text-color: #2c3e50;
        }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .header-section {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-back {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            background-color: var(--primary-color);
            border: none;
        }

        .btn-back:hover {
            transform: translateX(-5px);
            background-color: #34495e;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: white;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1.5rem;
            border: none;
        }

        .card-header h3 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
        }
		
		.protocole-info {
            background-color: var(--light-gray);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .patient-info {
            background-color: var(--light-gray);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .info-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .info-content {
            background-color: white;
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid var(--secondary-color);
        }

        .staff-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .staff-card {
            background-color: var(--light-gray);
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
        }

        .staff-icon {
            margin-right: 1rem;
            color: var(--secondary-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .card-footer {
            background-color: white;
            border-top: 1px solid #eee;
            padding: 1.5rem;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background-color: #f1c40f;
            border: none;
            color: white;
        }

        .btn-danger {
            background-color: var(--accent-color);
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <a href="index.php" class="btn btn-secondary btn-back">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <?php
        $date_op = new DateTime($protocole['date_operation']);
        $now = new DateTime();
        $status = ($date_op > $now) ? 'Programmé' : 'Terminé';
        $status_class = ($status === 'Programmé') ? 'warning' : 'success';
        ?>
        <span class="status-badge badge bg-<?php echo $status_class; ?>">
            <?php echo $status; ?>
        </span>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Protocole Opératoire</h3>
        </div>
        
        <div class="card-body">
			<div class="protocole-info">
					<div>
                        <small>Numéro du protocole</small>
                        <div><strong><?php echo htmlspecialchars($protocole['num_protocole']); ?></strong></div>
                    </div>               
            </div>
            <div class="patient-info">
                <h4 class="mb-3">Informations Patient</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><i class="fas fa-user me-2"></i> <strong><?php echo htmlspecialchars($protocole['nom_patient']) . " " . htmlspecialchars($protocole['prenom_patient']); ?></strong></p>
                        <p><i class="fas fa-birthday-cake me-2"></i> <strong>Age :</strong> <?php echo htmlspecialchars($protocole['age_patient']); ?> ans</p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fas fa-calendar-alt me-2"></i> <strong>Date d'opération :</strong> <?php echo date('d/m/Y', strtotime($protocole['date_operation'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="staff-info">
                <div class="staff-card">
                    <i class="fas fa-user-md staff-icon"></i>
                    <div>
                        <small>Opérateur</small>
                        <div><strong><?php echo htmlspecialchars($protocole['operateur']); ?></strong></div>
                    </div>
                </div>
                <div class="staff-card">
                    <i class="fas fa-hands-helping staff-icon"></i>
                    <div>
                        <small>Aide</small>
                        <div><strong><?php echo htmlspecialchars($protocole['aide']); ?></strong></div>
                    </div>
                </div>
                <div class="staff-card">
                    <i class="fas fa-syringe staff-icon"></i>
                    <div>
                        <small>Anesthésiste</small>
                        <div><strong><?php echo htmlspecialchars($protocole['anesthesiste']); ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="info-label">Diagnostic</div>
                <div class="info-content">
                    <?php echo nl2br(htmlspecialchars($protocole['diagnostic'])); ?>
                </div>
            </div>

            <div class="info-section">
                <div class="info-label">Intervention</div>
                <div class="info-content">
                    <?php echo nl2br(htmlspecialchars($protocole['intervention'])); ?>
                </div>
            </div>

            <div class="info-section">
                <div class="info-label">Observations</div>
                <div class="info-content">
                    <?php echo nl2br(htmlspecialchars($protocole['observations'])); ?>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-end gap-2">
                <a href="modifier_protocole.php?id=<?php echo $protocole['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <button class="btn btn-danger delete-protocol" data-id="<?php echo $protocole['id']; ?>">
                    <i class="fas fa-trash-alt"></i> Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/sweetalert/sweetalert2@11.js"></script>

<script>
    document.querySelector('.delete-protocol').addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'supprimer_protocole.php?id=' + id;
            }
        });
    });
</script>

</body>
</html>

<?php include 'footer.php'; ?>