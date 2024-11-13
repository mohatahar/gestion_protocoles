<?php
require_once 'db.php';
require_once 'vendor/autoload.php'; // Require TCPDF library

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    die('ID du protocole non fourni');
}

$id = (int)$_GET['id'];

// Récupérer les données du protocole
$stmt = $pdo->prepare("SELECT * FROM protocoles WHERE id = ?");
$stmt->execute([$id]);
$protocole = $stmt->fetch();

if (!$protocole) {
    die('Protocole non trouvé');
}

// Créer une nouvelle instance de TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Définir les informations du document
$pdf->SetCreator('EPH SOBHA');
$pdf->SetAuthor('EPH SOBHA');
$pdf->SetTitle('Protocole Opératoire - ' . $protocole['nom_patient'] . ' ' . $protocole['prenom_patient']);

// Supprimer les en-têtes et pieds de page par défaut
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Ajouter une nouvelle page
$pdf->AddPage();

// Définir la police
$pdf->SetFont('dejavusans', '', 12);

// En-tête du document
$pdf->SetFont('dejavusans', 'BU', 16);
$pdf->Cell(0, 10, 'PROTOCOLE OPERATOIRE DETAILLE', 0, 1, 'C');
$pdf->SetFont('dejavusans', '', 12);
$pdf->Ln(10);

// Positionnement de départ
$y_start = $pdf->GetY(); // Enregistre la position verticale actuelle pour aligner les deux colonnes

// Colonne de gauche : Informations du patient
$pdf->SetFont('dejavusans', 'BU', 12);
$pdf->SetXY(10, $y_start); // Position en haut à gauche de la page
$pdf->Cell(90, 10, 'Informations du Patient:', 0, 1, 'L');

$pdf->SetXY(10, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(30, 10, 'Nom:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(60, 10, $protocole['nom_patient'], 0, 1);

$pdf->SetXY(10, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(30, 10, 'Prénom:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(60, 10, $protocole['prenom_patient'], 0, 1);

$pdf->SetXY(10, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(30, 10, 'Âge:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(60, 10, $protocole['age_patient'], 0, 1);

$pdf->SetXY(10, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(30, 10, 'N° Protocole:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(60, 10, $protocole['num_protocole'], 0, 1);

// Colonne de droite : Contenu du protocole
$pdf->SetFont('dejavusans', 'BU', 12);
$pdf->SetXY(110, $y_start); // Positionne à droite au niveau du même Y de départ
$pdf->Cell(90, 10, 'Informations de l\'Opération:', 0, 1, 'L');

$pdf->SetXY(110, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(40, 10, 'Date d\'opération:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(50, 10, date('d/m/Y', strtotime($protocole['date_operation'])), 0, 1);

$pdf->SetXY(110, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(40, 10, 'Opérateur:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(50, 10, $protocole['operateur'], 0, 1);

$pdf->SetXY(110, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(40, 10, 'Aide:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(50, 10, $protocole['aide'], 0, 1);

$pdf->SetXY(110, $pdf->GetY());
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(40, 10, 'Anesthésiste:', 0, 0);
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(50, 10, $protocole['anesthesiste'], 0, 1);

$pdf->Ln(15);
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->MultiCell(40, 10, 'Diagnostique:', 0, 'L', false, 0);
$pdf->Ln(7.5);
$pdf->SetFont('dejavusans', '', 12);
$pdf->MultiCell(0, 10, $protocole['diagnostic'] ?? '', 0, 'L', false, 1);
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->MultiCell(40, 10, 'Intervention:', 0, 'L', false, 0);
$pdf->Ln(7.5);
$pdf->SetFont('dejavusans', '', 12);
$pdf->MultiCell(0, 10, $protocole['intervention'] ?? '', 0, 'L', false, 1);
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->MultiCell(40, 10, 'Observations:', 0, 'L', false, 0);
$pdf->Ln(7.5);
$pdf->SetFont('dejavusans', '', 12);
$pdf->MultiCell(0, 10, $protocole['observations'] ?? '', 0, 'L', false, 1);
$pdf->writeHTML($protocole['contenu'], true, false, true, false, '');

// Générer le PDF
$pdf->Output('protocole_' . $protocole['id'] . '.pdf', 'I');