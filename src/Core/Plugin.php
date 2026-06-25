<?php

declare(strict_types=1);

namespace ConacytaCore\Core;

use ConacytaCore\Abilities\AbilityRegistrar;
use ConacytaCore\Actividad\ActividadCPT;
use ConacytaCore\Agenda\AgendaCPT;
use ConacytaCore\Agenda\AgendaMigrator;
use ConacytaCore\Agenda\AgendaQueryFilter;
use ConacytaCore\Agenda\AgendaRestController;
use ConacytaCore\Agenda\AgendaSaveHandler;
use ConacytaCore\Agenda\AgendaTipoTaxonomy;
use ConacytaCore\Agenda\AgendaVariation;
use ConacytaCore\Agenda\AuditorioTaxonomy;
use ConacytaCore\AreaTematica\AreaTematicaQueryFilter;
use ConacytaCore\AreaTematica\AreaTematicaTaxonomy;
use ConacytaCore\Chatbot\ChatbotRestController;
use ConacytaCore\Chatbot\ChatbotSettings;
use ConacytaCore\Comite\ComiteCPT;
use ConacytaCore\Contacto\ContactoRestController;
use ConacytaCore\Contacto\ContactoSettings;
use ConacytaCore\Partner\PartnerCPT;
use ConacytaCore\Cronograma\CronogramaCPT;
use ConacytaCore\Ponente\PonenteCPT;
use ConacytaCore\Portada\PortadaCPT;
use ConacytaCore\Portada\PortadaQueryFilter;
use ConacytaCore\Settings\EventoSettings;
use ConacytaCore\Settings\SettingsPage;
use ConacytaCore\Shared\VariationFactory;
use ConacytaCore\Tarifa\BeneficioTarifaTaxonomy;
use ConacytaCore\Tarifa\TarifaCPT;

final class Plugin
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    public function register(): void
    {
        if (function_exists('wp_register_ability')) {
            add_action('wp_abilities_api_categories_init', [$this, 'registerAbilitiesCategories']);
            add_action('wp_abilities_api_init', [$this, 'registerAbilities']);
        }
        add_action('init', [$this, 'registerPostTypesAndTaxonomies']);
        add_action('init', [$this, 'registerSaveHandlers']);
        add_action('init', [$this, 'registerBlockVariations']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('admin_menu', [$this, 'registerSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_filter('upload_mimes', [$this, 'allowSvgUpload']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fixSvgMimeDetection'], 10, 4);
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitizeSvgUpload']);
        add_action('save_post', [$this, 'invalidateContextCache']);
    }

    public function registerPostTypesAndTaxonomies(): void
    {
        (new AreaTematicaTaxonomy())->register();
        (new AuditorioTaxonomy())->register();
        (new AgendaTipoTaxonomy())->register();
        (new BeneficioTarifaTaxonomy())->register();
        (new PonenteCPT())->register();
        (new ActividadCPT())->register();
        (new AgendaCPT())->register();
        (new TarifaCPT())->register();
        (new ComiteCPT())->register();
        (new PartnerCPT())->register();
        (new CronogramaCPT())->register();
        (new PortadaCPT())->register();
    }

    public function registerSaveHandlers(): void
    {
        (new AgendaSaveHandler())->register();
        (new AgendaMigrator())->register();
    }

    public function registerBlockVariations(): void
    {
        add_filter('get_block_type_variations', function (array $variations, \WP_Block_Type $block_type): array {
            if ('core/query' !== $block_type->name) {
                return $variations;
            }

            $variations = array_merge($variations,
                VariationFactory::make('conacyta/ponentes-grid', __('Grid de Ponentes', 'conacyta'), __('Muestra los ponentes magistrales en una cuadricula de 3 columnas.', 'conacyta'), 'id', 'ponente'),
                VariationFactory::make('conacyta/actividades-grid', __('Grid de Actividades', 'conacyta'), __('Muestra las actividades paralelas en una cuadricula.', 'conacyta'), 'calendar', 'actividad'),
                (new AgendaVariation())->get(),
                VariationFactory::make('conacyta/tarifas-grid', __('Grid de Tarifas', 'conacyta'), __('Muestra las tarifas e inversion en una cuadricula.', 'conacyta'), 'tickets-alt', 'tarifa'),
                VariationFactory::make('conacyta/comite-grid', __('Grid de Comité', 'conacyta'), __('Muestra los miembros del comité organizador.', 'conacyta'), 'groups', 'comite_member'),
                VariationFactory::make('conacyta/cronograma-grid', __('Grid de Convocatoria', 'conacyta'), __('Muestra las fases de la convocatoria en timeline.', 'conacyta'), 'calendar-alt', 'cronograma_fase'),
                VariationFactory::make('conacyta/partners-grid', __('Grid de Partners', 'conacyta'), __('Muestra los socios estrategicos en una cuadricula.', 'conacyta'), 'star-filled', 'partner', 20),
                VariationFactory::make('conacyta/portada-hero', __('Hero Home', 'conacyta'), __('Muestra la portada principal en formato hero.', 'conacyta'), 'cover-image', 'portada', 1, 'DESC')
            );

            return $variations;
        }, 10, 2);

        (new AgendaQueryFilter())->register();
        (new AreaTematicaQueryFilter())->register();
        (new PortadaQueryFilter())->register();
    }

    public function registerRestRoutes(): void
    {
        (new ChatbotRestController())->register();
        (new ContactoRestController())->register();
        (new AgendaRestController())->register();
    }

    public function registerSettingsPage(): void
    {
        (new SettingsPage())->register();
    }

    public function registerSettings(): void
    {
        (new ChatbotSettings())->register();
        (new ContactoSettings())->register();
        (new EventoSettings())->register();
    }

    public function registerAbilitiesCategories(): void
    {
        (new AbilityRegistrar())->registerCategories();
    }

    public function registerAbilities(): void
    {
        (new AbilityRegistrar())->register();
    }

    public function enqueueFrontendAssets(): void
    {
        (new Assets())->enqueue();
    }

    public function enqueueEditorAssets(): void
    {
        $screen = get_current_screen();

        if (!$screen || 'post' !== $screen->base) {
            return;
        }

        $cpt_slugs = [
            'ponente', 'actividad', 'agenda_item', 'tarifa',
            'comite_member', 'partner', 'cronograma_fase', 'portada',
        ];

        if (!in_array($screen->post_type, $cpt_slugs, true)) {
            return;
        }

        $asset_file = CONACYTA_CORE_PLUGIN_DIR . 'admin/js/index.asset.php';
        $asset      = file_exists($asset_file) ? require $asset_file : [];

        wp_enqueue_script(
            'conacyta-editor',
            CONACYTA_CORE_PLUGIN_URL . 'admin/js/index.js',
            $asset['dependencies'] ?? [],
            $asset['version'] ?? CONACYTA_CORE_VERSION,
            true
        );

        wp_enqueue_style(
            'conacyta-editor',
            CONACYTA_CORE_PLUGIN_URL . 'admin/css/editor.css',
            [],
            CONACYTA_CORE_VERSION
        );

        wp_localize_script('conacyta-editor', 'conacytaData', [
            'eventoFechaInicio' => get_option('conacyta_evento_fecha_inicio', '2026-10-12'),
            'eventoFechaFin'    => get_option('conacyta_evento_fecha_fin', '2026-10-16'),
        ]);
    }

    public function enqueueAdminAssets(string $hook): void
    {
        if ('conacyta-settings' !== sanitize_key(wp_unslash($_GET['page'] ?? ''))) {
            return;
        }

        $active_tab = sanitize_key(wp_unslash($_GET['tab'] ?? 'chatbot'));
        if ('evento' !== $active_tab) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_script(
            'conacyta-admin-settings',
            CONACYTA_CORE_PLUGIN_URL . 'admin/admin.js',
            [],
            CONACYTA_CORE_VERSION,
            true
        );

        wp_localize_script('conacyta-admin-settings', 'conacytaData', [
            'confirmPhrase'     => 'CAMBIAR FECHAS CONACYTA',
            'agendaItemsCount'  => wp_count_posts('agenda_item')->publish,
        ]);

        wp_add_inline_style('wp-admin', '.conacyta-confirm-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:100000;display:flex;align-items:center;justify-content:center}.conacyta-confirm-modal{background:white;border-radius:12px;padding:32px;max-width:520px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.25)}.conacyta-implications{background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:12px 16px;margin:16px 0;font-size:13px}.conacyta-confirm-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:20px}');
    }

    /**
     * Permite la subida de archivos SVG.
     */
    public function allowSvgUpload(array $mimes): array
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    /**
     * Corrige la deteccion de MIME type para archivos SVG
     * que el servidor puede reportar incorrectamente.
     */
    public function fixSvgMimeDetection(array $data, $file, string $filename, $mimes): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ('svg' !== $ext) {
            return $data;
        }
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
        return $data;
    }

    public function sanitizeSvgUpload(array $file): array
    {
        if ($file['type'] !== 'image/svg+xml') {
            return $file;
        }
        $svg = file_get_contents($file['tmp_name']);
        if ($svg === false) {
            return $file;
        }
        $svg = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $svg);
        $svg = preg_replace('/\bon\w+\s*=\s*"[^"]*"/i', '', $svg);
        $svg = preg_replace("/\bon\w+\s*=\s*'[^']*'/i", '', $svg);
        file_put_contents($file['tmp_name'], $svg);
        return $file;
    }

    public function invalidateContextCache(int $postId): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $cpts = ['ponente', 'tarifa', 'comite_member', 'cronograma_fase', 'actividad', 'partner', 'agenda_item'];
        $postType = get_post_type($postId);
        if (in_array($postType, $cpts, true)) {
            delete_transient('conacyta_context_prompt');
        }
    }
}