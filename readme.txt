=== JRB Remote Site API for OpenClaw ===
Contributors: jrbconsulting
Tags: api, remote, jrbremote, automation, fluentcrm
Requires at least: 5.6
Tested up to: 6.4.0
Stable tag: 6.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional WordPress REST API for JRB Consulting remote site management and automation.

== Description ==

JRB Remote Site API provides a secure, high-fidelity bridge between your WordPress environment and external automation tools. It extends the WordPress REST API to support remote site management, media handling, and deep integration with the Fluent Suite (CRM, Support, Forms, Project).

== Installation ==

1. Upload the `jrb-remote-site-api-for-openclaw` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your API token and permissions in the 'JRB Remote API' settings page.
4. **Agent Integration:** For OpenClaw users, a dedicated Agent Skill is available here:
   `https://github.com/JRBConsulting/jrb-remote-site-api-skill.git`

== Changelog ==

= 6.4.0 =
* Major structural refactor to PSR-4 modular system.
* Rebrand to JRB Remote (Namespace: jrbremoteapi/v1, Header: X-JRB-Token).
* Hardened Security: Complete move to hashed token storage and granular capability matrix.
* Optimization: Dynamic conditional loading of plugin-specific modules (CRM, Support, etc.).
* Feature Preservation: 100% restoration of legacy token generation and plugin detection UI.

= 6.3.3 =
* Emergency restoration of original feature-complete legacy codebase.
