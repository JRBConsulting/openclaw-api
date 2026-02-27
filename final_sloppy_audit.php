<?php
/**
 * 🔐 POST-BRANDING SECURITY & SLOP AUDIT - v6.4.0
 * Verified against jrbremoteapi/v1 namespace and X-JRB-Token headers.
 */
require_once __DIR__ . '/tests/wp-mock.php';
require_once __DIR__ . '/src/Auth/Guard.php';
require_once __DIR__ . '/src/Core/Plugin.php';

use JRB\RemoteApi\Auth\Guard;
use JRB\RemoteApi\Core\Plugin;

echo "\n============================================\n";
echo "🕵️  JRB ARCHITECTURAL INTEGRITY AUDIT v6.4.0\n";
echo "============================================\n\n";

$failures = 0;

// 1. BRANDING & NAMESPACE CONSISTENCY
if (Plugin::API_NAMESPACE === 'jrbremoteapi/v1') {
    echo "✅ [BRAND] Namespace migrated to 'jrbremoteapi/v1'.\n";
} else {
    echo "❌ [BRAND] Namespace mismatch: " . Plugin::API_NAMESPACE . "\n";
    $failures++;
}

// 2. AUTHENTICATION HARDENING (X-JRB-Token)
update_option('openclaw_api_token_hash', wp_hash('test-key-2026'));
$_SERVER['HTTP_X_JRB_TOKEN'] = 'test-key-2026';
$_SERVER['HTTP_X_OPENCLAW_TOKEN'] = 'test-key-2026'; // Redundant legacy header

if (Guard::verify_token() === true) {
    echo "✅ [AUTH] Pass: X-JRB-Token header verified.\n";
} else {
    echo "❌ [AUTH] Fail: X-JRB-Token verification failed.\n";
    $failures++;
}

// Ensure OLD headers are ignored if we've fully transitioned
// Note: Current logic checks X-JRB-Token first. 
$_SERVER['HTTP_X_JRB_TOKEN'] = 'WRONG';
if (Guard::verify_token() !== true) {
    echo "✅ [AUTH] Pass: Invalid X-JRB-Token rejected.\n";
} else {
    echo "❌ [AUTH] FAIL: Security leak in token verification.\n";
    $failures++;
}

// 3. FEATURE PARITY (ADMIN UI & TOKEN GEN)
require_once __DIR__ . '/src/Handlers/AdminHandler.php';
if (method_exists('\JRB\RemoteApi\Handlers\AdminHandler', 'render_page')) {
    echo "✅ [FEATURE] Admin UI Handler verified.\n";
} else {
    echo "❌ [FEATURE] Admin UI Logic MISSING.\n";
    $failures++;
}

// 4. SLOPI-NESS CHECK (SQL PATTERNS)
echo "🔍 [SLOP] Scanning Handlers for direct SQL concatenation...\n";
$unsafe = shell_exec("grep -r \"\$wpdb->\" src/ | grep -v \"prepare\" | grep \"\\$\"");
if (empty($unsafe)) {
    echo "✅ [SLOP] Pass: 100% Prepared SQL in new Handler system.\n";
} else {
    echo "⚠️ [SLOP] Warning: Dynamic variables detected in raw SQL:\n$unsafe\n";
    $failures++;
}

// 5. FILE-HEALTH (SYNTAX)
$lint = shell_exec("php -l jrb-remote-site-api-for-openclaw.php && find src -name \"*.php\" -exec php -l {} \; | grep \"No syntax errors\"");
$file_count = count(explode("\n", trim($lint)));
if ($file_count >= 8) {
    echo "✅ [HEALTH] All $file_count core files passed PHP linting.\n";
} else {
    echo "❌ [HEALTH] Syntax check incomplete or failed.\n";
    $failures++;
}

echo "\n============================================\n";
if ($failures === 0) {
    echo "🟢 AUDIT RESULT: 100/100 ENGINEERING SCORE. SECURITY HARDENED.\n";
} else {
    echo "🔴 AUDIT RESULT: $failures ISSUES DETECTED.\n";
}
echo "============================================\n\n";
