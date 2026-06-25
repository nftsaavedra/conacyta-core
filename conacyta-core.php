<?php
/**
 * Plugin Name:       Conacyta Core
 * Plugin URI:        https://conacyta.unf.edu.pe
 * Description:       Lógica de negocio (CPTs, REST, settings, IA con Gemini + DeepSeek V4) para el XVII CONACYTA 2026.
 * Version:           1.0.2
 * Requires at least: 7.0
 * Requires PHP:      8.3
 * Author:            Universidad Nacional de Frontera
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       conacyta
 * Domain Path:       /languages
 *
 * @package ConacytaCore
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('CONACYTA_CORE_VERSION')) { define('CONACYTA_CORE_VERSION', '1.0.2'); }
if (!defined('CONACYTA_CORE_PLUGIN_FILE')) { define('CONACYTA_CORE_PLUGIN_FILE', __FILE__); }
if (!defined('CONACYTA_CORE_PLUGIN_DIR')) { define('CONACYTA_CORE_PLUGIN_DIR', plugin_dir_path(__FILE__)); }
if (!defined('CONACYTA_CORE_PLUGIN_URL')) { define('CONACYTA_CORE_PLUGIN_URL', plugin_dir_url(__FILE__)); }

$autoload = CONACYTA_CORE_PLUGIN_DIR . 'vendor/autoload.php';
if (!file_exists($autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>' . __('Conacyta Core: ejecuta <code>composer install</code> en el directorio del plugin.', 'conacyta') . '</p></div>';
    });
    return;
}
require_once $autoload;

register_activation_hook(__FILE__, ['ConacytaCore\Core\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['ConacytaCore\Core\Deactivator', 'deactivate']);



add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain(
        'conacyta',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );

    ConacytaCore\Core\Plugin::getInstance()->register();
});
