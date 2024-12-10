<?php
require_once 'db.php';
require_once 'auth_check.php';
require_once 'header.php';
$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];

    // Validation
    if (empty($username) || empty($prenom) || empty($nom) || empty($password) || empty($confirm_password)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Ce nom d\'utilisateur existe déjà';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user with first and last name
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role,nom, prenom) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $role, $prenom, $nom]);

                $success = 'Utilisateur ajouté avec succès';
                // Clear form fields
                $username = $prenom = $nom = $role = '';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/css2.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">
                            <i class="fas fa-user-plus me-2"></i>
                            Nouvel Utilisateur
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                        <div class="mb-3">
                                <label for="prenom" class="form-label">Nom</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="prenom" 
                                        name="prenom"
                                        value="<?php echo htmlspecialchars(isset($prenom) ? $prenom : ''); ?>" 
                                        required 
                                        placeholder="Entrez le nom de famille"
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nom" class="form-label">Prénom</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nom" 
                                        name="nom"
                                        value="<?php echo htmlspecialchars(isset($nom) ? $nom : ''); ?>" 
                                        required 
                                        placeholder="Entrez le prénom"
                                    >
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="username" class="form-label">Nom d'utilisateur</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="username" 
                                        name="username"
                                        value="<?php echo htmlspecialchars(isset($username) ? $username : ''); ?>" 
                                        required 
                                        placeholder="Entrez le nom d'utilisateur"
                                    >
                                </div>
                            </div>

                            
                           

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password" 
                                        name="password" 
                                        required 
                                        placeholder="Entrez le mot de passe"
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        required 
                                        placeholder="Confirmez le mot de passe"
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <select 
                                        class="form-control" 
                                        id="role" 
                                        name="role" 
                                        required
                                    >
                                        <option value="">Sélectionnez un rôle</option>
                                        <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Administrateur</option>
                                        <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Ajouter l'utilisateur
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/popper/popper.min.js"></script>
    <script src="assets/bootstrap/bootstrap.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>