<?php
/**
 * OpenClaw API Uninstall
 *
 * Clean up plugin options on uninstall.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('openclaw_api_token');
delete_option('openclaw_api_capabilities');

// Also clean up any old options from previous versions
delete_option('lilith_api_token');
delete_option('lilith_api_capabilities');