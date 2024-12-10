<?php
require_once 'db.php';
require_once 'auth_check.php';
require_once 'header.php';

$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();

// Récupération de l'ID du protocole
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "ID de protocole invalide.";
    header('Location: index.php');
    exit;
}

// Traitement du formulaire lors de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $errors = [];
        
		if (empty($_POST['num_protocole'])) {
            $errors[] = "Le numéro du protocole est requis.";
        }
        if (empty($_POST['nom_patient'])) {
            $errors[] = "Le nom du patient est requis.";
        }
        if (empty($_POST['prenom_patient'])) {
            $errors[] = "Le prénom du patient est requis.";
        }
        if (empty($_POST['date_operation'])) {
            $errors[] = "La date d'opération est requise.";
        }
        if (empty($_POST['operateur'])) {
            $errors[] = "L'opérateur est requis.";
        }
        
        if (empty($errors)) {
            // Préparation de la requête de mise à jour
            $sql = "UPDATE protocoles SET
					num_protocole = :num_protocole,			
                    nom_patient = :nom_patient,
                    prenom_patient = :prenom_patient,
                    age_patient = :age_patient,
                    date_operation = :date_operation,
                    operateur = :operateur,
                    aide = :aide,
                    anesthesiste = :anesthesiste,
                    diagnostic = :diagnostic,
                    intervention = :intervention,
                    observations = :observations,
                    date_maj = NOW()
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id' => $id,
				'num_protocole' => $_POST['num_protocole'],
                'nom_patient' => $_POST['nom_patient'],
                'prenom_patient' => $_POST['prenom_patient'],
                'age_patient' => $_POST['age_patient'],
                'date_operation' => $_POST['date_operation'],
                'operateur' => $_POST['operateur'],
                'aide' => $_POST['aide'],
                'anesthesiste' => $_POST['anesthesiste'],
                'diagnostic' => $_POST['diagnostic'],
                'intervention' => $_POST['intervention'],
                'observations' => $_POST['observations']
            ]);
            
            $_SESSION['success'] = "Le protocole a été modifié avec succès.";
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la modification du protocole : " . $e->getMessage();
    }
}

// Récupération des données du protocole
try {
    $stmt = $pdo->prepare("SELECT * FROM protocoles WHERE id = ?");
    $stmt->execute([$id]);
    $protocole = $stmt->fetch();
    
    if (!$protocole) {
        $_SESSION['error'] = "Protocole non trouvé.";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération du protocole : " . $e->getMessage();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Protocole - EPH SOBHA</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/css2.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --background-color: #f8fafc;
            --card-background: #ffffff;
            --text-color: #1e293b;
            --border-color: #e2e8f0;
            --input-focus: rgba(79, 70, 229, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            font-size: 2.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .page-header .lead {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            background: var(--card-background);
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--input-focus);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.25rem;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: #4b5563;
            border-color: #4b5563;
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .input-group-text {
            border-radius: 0.75rem;
            background-color: #f8fafc;
            border: 2px solid var(--border-color);
        }

        .form-row {
            margin-bottom: 1.5rem;
        }

        /* Animation pour les changements d'état */
        .form-control, .btn {
            transition: all 0.2s ease;
        }

        /* Style pour les champs obligatoires */
        .required-field::after {
            content: "*";
            color: var(--danger-color);
            margin-left: 4px;
        }

        /* Hover effects */
        .card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
                border-radius: 0 0 1.5rem 1.5rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-edit me-2"></i> Modifier le Protocole</h1>
            <p class="lead">Modification des informations du protocole opératoire</p>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> Erreurs de validation</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
						<div class="col-md-4 mb-3">
                            <label for="num_protocole" class="required-field">Numéro du protocole</label>
                            <input type="number" class="form-control" id="num_protocole" name="num_protocole" 
                                   value="<?php echo htmlspecialchars($protocole['num_protocole']); ?>" required>
                        </div>
                    <h3 class="section-title">
                        <i class="fas fa-user me-2"></i> Informations du Patient
                    </h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nom_patient" class="required-field">Nom du patient</label>
                            <input type="text" class="form-control" id="nom_patient" name="nom_patient" 
                                   value="<?php echo htmlspecialchars($protocole['nom_patient']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prenom_patient" class="required-field">Prénom du patient</label>
                            <input type="text" class="form-control" id="prenom_patient" name="prenom_patient" 
                                   value="<?php echo htmlspecialchars($protocole['prenom_patient']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="age_patient" class="required-field">Âge du patient</label>
                            <input type="number" class="form-control" id="age_patient" name="age_patient" 
                                   value="<?php echo htmlspecialchars($protocole['age_patient']); ?>" required>
                        </div>
                    </div>

                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt me-2"></i> Informations de l'Opération
                    </h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_operation" class="required-field">Date d'opération</label>
                            <input type="date" class="form-control" id="date_operation" name="date_operation" 
                                   value="<?php echo htmlspecialchars($protocole['date_operation']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="operateur" class="required-field">Opérateur</label>
                            <input type="text" class="form-control" id="operateur" name="operateur" 
                                   value="<?php echo htmlspecialchars($protocole['operateur']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="aide">Aide opératoire</label>
                            <input type="text" class="form-control" id="aide" name="aide" 
                                   value="<?php echo htmlspecialchars($protocole['aide']); ?>">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="anesthesiste">Anesthésiste</label>
                            <input type="text" class="form-control" id="anesthesiste" name="anesthesiste" 
                                   value="<?php echo htmlspecialchars($protocole['anesthesiste']); ?>">
                        </div>
                    </div>

                    <h3 class="section-title">
                        <i class="fas fa-file-medical me-2"></i> Détails Médicaux
                    </h3>
                    <div class="mb-4">
                        <label for="diagnostic">Diagnostic</label>
                        <textarea class="form-control" id="diagnostic" name="diagnostic" rows="3"><?php echo htmlspecialchars($protocole['diagnostic']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="intervention">Intervention</label>
                        <textarea class="form-control" id="intervention" name="intervention" rows="5"><?php echo htmlspecialchars($protocole['intervention']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="observations">Observations</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3"><?php echo htmlspecialchars($protocole['observations']); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between flex-wrap mt-4">
                        <a href="index.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/popper/popper.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.min.js"></script>
    <script>
    // Animation pour les messages d'alerte
    $('.alert').fadeIn('slow');
    
    // Validation côté client
    document.querySelector('form').addEventListener('submit', function(e) {
        let requiredFields = document.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
    </script>
</body>
</html>

<?php include 'footer.php'; ?>