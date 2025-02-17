<?php

use WHMCS\Database\Capsule;

require 'init.php';

// 1. Récupérer le JSON envoyé par SoleasPay
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Assurez-vous que les données sont valides
if (!isset($data['invoice_id']) || !isset($data['status']) || !isset($data['signature'])) {
    http_response_code(400);
    exit('Invalid request data.');
}

// 2. Votre clé secrète
$secret_key = 'VOTRE_CLE_SECRETE'; // Mettez votre clé secrète ici

// 3. Générer la signature attendue
$signature_base = $data['invoice_id'] . $data['status'] . $data['amount'] . $data['timestamp']; // Base de la signature
$expected_signature = hash_hmac('sha256', $signature_base, $secret_key);

// 4. Comparer la signature reçue avec celle attendue
if (!hash_equals($expected_signature, $data['signature'])) {
    // Signature invalide
    http_response_code(403);
    exit('Invalid signature.');
}

// 5. Vérifiez si l'ID de facture existe
$invoice_id = $data['invoice_id'];
$invoice = Capsule::table('tblinvoices')->where('id', $invoice_id)->first();

if (!$invoice) {
    http_response_code(404);
    exit('Invoice not found.');
}

// 6. Mettez à jour le statut de la facture
$status = $data['status'];

if ($status === 'success') {
    Capsule::table('tblinvoices')->where('id', $invoice_id)->update(['status' => 'Paid']);
    // Optionnel : envoyer une notification ou un email
} else {
    Capsule::table('tblinvoices')->where('id', $invoice_id)->update(['status' => 'Unpaid']);
}

// Statut de la réponse
http_response_code(200); // Tout est bon
exit('Success');
