<?php
require_once 'db.php';
require_once 'auth_check.php';
require_once 'header.php';

$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();

// Fonction de validation
function validateInput($data)
{
    return htmlspecialchars(trim($data));
}

// Fonction pour vérifier la date
function isValidDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Initialisation des variables d'erreur
$errors = [];
$success = false;
$form_data = [];

// Traitement du formulaire
if (isset($_POST['submit'])) {
    // Validation des champs
    $required_fields = [
        'num_protocole',
        'nom_patient',
        'prenom_patient',
        'age_patient',
        'date_operation',
        'operateur',
        'aide',
        'anesthesiste'
    ];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est requis.";
        }
        $form_data[$field] = validateInput($_POST[$field]);
    }

    // Validation spécifique
    if (!empty($_POST['age_patient']) && (!is_numeric($_POST['age_patient']) || $_POST['age_patient'] < 0 || $_POST['age_patient'] > 130)) {
        $errors[] = "L'âge doit être compris entre 0 et 130 ans.";
    }

    if (!empty($_POST['date_operation']) && !isValidDate($_POST['date_operation'])) {
        $errors[] = "La date d'opération n'est pas valide.";
    }

    // Validation des champs textuels optionnels
    $text_fields = ['diagnostic', 'intervention', 'observations'];
    foreach ($text_fields as $field) {
        $form_data[$field] = validateInput($_POST[$field] ?? '');
    }

    // Si pas d'erreurs, insertion en base de données
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO protocoles (
                        num_protocole, nom_patient, prenom_patient, age_patient, date_operation,
                        operateur, aide, anesthesiste, diagnostic, intervention,
                        observations, date_creation, date_maj
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $form_data['num_protocole'],
                $form_data['nom_patient'],
                $form_data['prenom_patient'],
                $form_data['age_patient'],
                $form_data['date_operation'],
                $form_data['operateur'],
                $form_data['aide'],
                $form_data['anesthesiste'],
                $form_data['diagnostic'],
                $form_data['intervention'],
                $form_data['observations']
            ]);

            $success = true;
            $_SESSION['success_message'] = "Le protocole a été ajouté avec succès.";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Protocole Opératoire</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(45deg, #0062cc, #0096ff);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #0096ff;
            box-shadow: 0 0 0 0.2rem rgba(0, 150, 255, 0.25);
        }

        .btn-submit {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .error-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .required-field::after {
            content: ' *';
            color: #dc3545;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0 text-center">
                    <i class="fas fa-file-medical me-2"></i>
                    Nouveau Protocole Opératoire
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Erreurs détectées :</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="row g-3 needs-validation" novalidate>

                    <div class="col-md-4">
                        <label for="num_protocole" class="form-label required-field">Numéro du protocole</label>
                        <input type="number" id="num_protocole" name="num_protocole"
                            class="form-control <?php echo isset($errors['num_protocole']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['num_protocole'] ?? ''; ?>" required>
                    </div>
                    <!-- Informations Patient -->
                    <div class="col-12 mb-4">
                        <h4 class="border-bottom pb-2"><i class="fas fa-user me-2"></i>Informations Patient</h4>
                    </div>

                    <div class="col-md-4">
                        <label for="nom_patient" class="form-label required-field">Nom</label>
                        <input type="text" id="nom_patient" name="nom_patient"
                            class="form-control <?php echo isset($errors['nom_patient']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['nom_patient'] ?? ''; ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="prenom_patient" class="form-label required-field">Prénom</label>
                        <input type="text" id="prenom_patient" name="prenom_patient"
                            class="form-control <?php echo isset($errors['prenom_patient']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['prenom_patient'] ?? ''; ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="age_patient" class="form-label required-field">Âge</label>
                        <input type="number" id="age_patient" name="age_patient"
                            class="form-control <?php echo isset($errors['age_patient']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['age_patient'] ?? ''; ?>" min="0" max="130" required>
                    </div>

                    <!-- Informations Opération -->
                    <div class="col-12 mt-4 mb-4">
                        <h4 class="border-bottom pb-2"><i class="fas fa-hospital me-2"></i>Informations Opération</h4>
                    </div>

                    <div class="col-md-6">
                        <label for="date_operation" class="form-label required-field">Date de l'opération</label>
                        <input type="date" id="date_operation" name="date_operation"
                            class="form-control <?php echo isset($errors['date_operation']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['date_operation'] ?? ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="operateur" class="form-label required-field">Opérateur</label>
                        <input type="text" id="operateur" name="operateur"
                            class="form-control <?php echo isset($errors['operateur']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['operateur'] ?? ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="aide" class="form-label required-field">Aide opératoire</label>
                        <input type="text" id="aide" name="aide"
                            class="form-control <?php echo isset($errors['aide']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['aide'] ?? ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="anesthesiste" class="form-label required-field">Anesthésiste</label>
                        <input type="text" id="anesthesiste" name="anesthesiste"
                            class="form-control <?php echo isset($errors['anesthesiste']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $form_data['anesthesiste'] ?? ''; ?>" required>
                    </div>

                    <!-- Détails Médicaux -->
                    <div class="col-12 mt-4 mb-4">
                        <h4 class="border-bottom pb-2"><i class="fas fa-notes-medical me-2"></i>Détails Médicaux</h4>
                    </div>

                    <div class="col-12">
                        <label for="diagnostic" class="form-label">Diagnostic</label>
                        <textarea id="diagnostic" name="diagnostic" class="form-control"
                            rows="3"><?php echo $form_data['diagnostic'] ?? ''; ?></textarea>
                    </div>

                    <div class="col-12">
                        <label for="intervention" class="form-label">Description de l'intervention</label>
                        <textarea id="intervention" name="intervention" class="form-control"
                            rows="4"><?php echo $form_data['intervention'] ?? ''; ?></textarea>
                    </div>

                    <div class="col-12">
                        <label for="observations" class="form-label">Observations particulières</label>
                        <textarea id="observations" name="observations" class="form-control"
                            rows="3"><?php echo $form_data['observations'] ?? ''; ?></textarea>
                    </div>

                    <!-- Boutons -->
                    <div class="col-12 text-center mt-4">
                        <a href="index.php" class="btn btn-secondary me-2">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" name="submit" class="btn btn-success btn-submit">
                            <i class="fas fa-save me-2"></i>Enregistrer le protocole
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation côté client
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Auto-fermeture des alertes
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
</body>

</html>

<?php include 'footer.php'; ?>