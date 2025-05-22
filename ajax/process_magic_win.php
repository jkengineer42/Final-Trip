<?php
require_once '../PHP/sessions.php'; // Centralized session management

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Erreur : Utilisateur non connecté. Connectez-vous pour réclamer ce prix.']);
    exit;
}

$currentUserEmail = $_SESSION['user_email'];
$voyageIdToWin = 11; // ID for "Expéditions dans l'espace"

// Load voyages data
$voyagesJsonPath = '../data/voyages.json';
if (!file_exists($voyagesJsonPath)) {
    echo json_encode(['success' => false, 'message' => 'Erreur critique: Fichier des voyages introuvable.']);
    exit;
}
$voyagesData = json_decode(file_get_contents($voyagesJsonPath), true);
if ($voyagesData === null || !isset($voyagesData['voyages'])) {
    echo json_encode(['success' => false, 'message' 'Erreur critique: Données des voyages invalides.']);
    exit;
}

$wonTripDetails = null;
$wonTripPrice = 0;
foreach ($voyagesData['voyages'] as $voyage) {
    if (isset($voyage['id']) && $voyage['id'] == $voyageIdToWin) {
        $wonTripDetails = $voyage;
        // Extract and clean price
        $priceStr = $voyage['prix'] ?? '0';
        $priceClean = preg_replace('/[^\d,\.]/', '', $priceStr);
        $wonTripPrice = floatval(str_replace(',', '.', $priceClean));
        break;
    }
}

if ($wonTripDetails === null) {
    echo json_encode(['success' => false, 'message' => 'Erreur: Le voyage spatial (ID 11) est introuvable.']);
    exit;
}

// Simulate a payment record
$transaction_id = 'GAMEWIN_' . strtoupper(bin2hex(random_bytes(10)));
$newPaymentRecord = [
    'transaction_id' => $transaction_id,
    'timestamp' => date('Y-m-d H:i:s'),
    'user_email' => $currentUserEmail,
    'total_paid' => $wonTripPrice, // The full price of the space trip
    'amount_processed' => $wonTripPrice, // Same as total_paid for this simulation
    'status' => 'won_via_game', // Special status
    'vendeur' => 'FINAL_TRIP_GAME',
    'trip_configuration' => [ // Simplified trip configuration for this win
        'tripId' => $wonTripDetails['id'],
        'tripTitle' => $wonTripDetails['titre'],
        'originalPrice' => $wonTripDetails['prix'],
        'calculatedTotalPrice' => $wonTripPrice,
        'duration' => $wonTripDetails['duree'],
        'stages' => "Gagné via le jeu du Nombre Magique - Options par défaut." // Simplified
    ]
];

// Save to paiements.json
$paymentsLogFile = '../data/paiements.json';
$payments = [];
if (file_exists($paymentsLogFile) && is_readable($paymentsLogFile)) {
    $jsonContent = file_get_contents($paymentsLogFile);
    if (!empty($jsonContent)) {
        $payments = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding payments.json in process_magic_win: " . json_last_error_msg());
            $payments = [];
        }
    }
    if (!is_array($payments)) $payments = [];
}

$payments[] = $newPaymentRecord;

if (file_put_contents($paymentsLogFile, json_encode($payments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    // Also, add this "won" trip to the user's personalized_trip session so they can see it in resume/panier
    // This is a bit of a hack to make it visible immediately
    $_SESSION['personalized_trip'] = $newPaymentRecord['trip_configuration'];
    
    // Add to cart with the "won" price (which is the full price, but it's "free" in context)
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    if (!isset($_SESSION['panier_prices'])) $_SESSION['panier_prices'] = [];
    
    $_SESSION['panier'][$voyageIdToWin] = 1; // Qty 1
    $_SESSION['panier_prices'][$voyageIdToWin] = 0.00; // Effectively free for cart total, but record shows full price

    echo json_encode(['success' => true, 'message' => 'Voyage spatial enregistré !']);
} else {
    error_log("Failed to write to payments.json in process_magic_win.");
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du gain.']);
}

?>