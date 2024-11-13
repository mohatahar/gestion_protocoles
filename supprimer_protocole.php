<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Suppression du protocole
    $sql = "DELETE FROM protocoles WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    echo "Protocole supprimé avec succès !";
    header("Location: index.php");
    exit();
}
?>
