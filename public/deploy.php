<?php

/**
 * Sales ERP - Hostinger Git Webhook Deployment Handler
 * 
 * To auto-deploy via GitHub Webhook:
 * 1. Add `DEPLOY_SECRET=YourSuperSecretKey123` to your .env file on Hostinger.
 * 2. In GitHub -> Repo -> Settings -> Webhooks -> Add webhook:
 *    Payload URL: https://yourdomain.com/deploy.php?secret=YourSuperSecretKey123
 *    Content type: application/json
 *    Events: Push event
 */

declare(strict_types=1);

// Prevent caching
header('Content-Type: text/plain; charset=utf-8');
header('X-Accel-Buffering: no');

$rootDir = dirname(__DIR__);
$envFile = $rootDir . '/.env';

// Load DEPLOY_SECRET from .env if present
$secret = 'saleserp_deploy_secret_change_me';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^DEPLOY_SECRET=(.*)$/m', $envContent, $matches)) {
        $secret = trim($matches[1], "\"' \t\n\r\0\x0B");
    }
}

// Check authorization via GET parameter or GitHub Webhook Header (X-Hub-Signature-256)
$providedSecret = $_GET['secret'] ?? $_SERVER['HTTP_X_DEPLOY_SECRET'] ?? '';
$githubSignature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

$authorized = false;

if (!empty($providedSecret) && hash_equals($secret, $providedSecret)) {
    $authorized = true;
} elseif (!empty($githubSignature)) {
    $payload = file_get_contents('php://input');
    $calculatedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    if (hash_equals($calculatedSignature, $githubSignature)) {
        $authorized = true;
    }
}

if (!$authorized) {
    http_response_code(403);
    echo "ERROR: Unauthorized access. Invalid deployment secret.\n";
    exit(1);
}

echo "====================================================\n";
echo " Sales ERP - Hostinger Automated Git Deployment\n";
echo " Timestamp: " . date('Y-m-d H:i:s T') . "\n";
echo "====================================================\n\n";

chdir($rootDir);

$deployScript = $rootDir . '/deploy.sh';

if (!file_exists($deployScript)) {
    http_response_code(500);
    echo "ERROR: deploy.sh script not found in {$rootDir}\n";
    exit(1);
}

// Execute deployment script and stream output
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open("bash " . escapeshellarg($deployScript) . " 2>&1", $descriptors, $pipes, $rootDir);

if (is_resource($process)) {
    fclose($pipes[0]);
    while (!feof($pipes[1])) {
        echo fgets($pipes[1]);
        flush();
    }
    fclose($pipes[1]);
    fclose($pipes[2]);
    $returnCode = proc_close($process);

    echo "\nDeployment finished with exit code: {$returnCode}\n";
    if ($returnCode !== 0) {
        http_response_code(500);
    }
} else {
    http_response_code(500);
    echo "ERROR: Failed to execute deploy.sh process.\n";
}
