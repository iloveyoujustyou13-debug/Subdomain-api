<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$data = json_decode(file_get_contents('php://input'), true);

$domain = $data['domain'] ?? '';
$username = $data['username'] ?? '';
$api_token = $data['api_token'] ?? '';
$subdomain = $data['subdomain'] ?? '';

if (empty($domain) || empty($username) || empty($api_token) || empty($subdomain)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Hostinger API call
$url_websites = "https://api.hostinger.com/api/hosting/v1/accounts/{$username}/websites";
$ch = curl_init($url_websites);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $api_token]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['success' => false, 'message' => 'Invalid API token or username.']);
    exit;
}

$websites = json_decode($response, true);
$website_id = null;
foreach ($websites['data'] as $site) {
    if ($site['domain'] === $domain) {
        $website_id = $site['id'];
        break;
    }
}

if (!$website_id) {
    echo json_encode(['success' => false, 'message' => 'Domain not found in your account.']);
    exit;
}

$url_create = "https://api.hostinger.com/api/hosting/v1/websites/{$website_id}/subdomains";
$payload = ['subdomain' => $subdomain];

$ch = curl_init($url_create);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 || $http_code === 201) {
    echo json_encode(['success' => true, 'message' => "✅ {$subdomain}.{$domain} created!"]);
} else {
    $error = json_decode($response, true);
    $msg = $error['error'] ?? 'Unknown error';
    echo json_encode(['success' => false, 'message' => "❌ $msg"]);
}
?>
