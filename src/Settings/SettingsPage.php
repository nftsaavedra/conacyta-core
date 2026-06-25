<?php

declare(strict_types=1);

namespace ConacytaCore\Settings;

final class SettingsPage
{
    private const MENU_SLUG = 'conacyta-menu';

    public function register(): void
    {
        add_menu_page(
            __('CONACYTA 2026', 'conacyta'),
            __('CONACYTA 2026', 'conacyta'),
            'edit_posts',
            self::MENU_SLUG,
            [$this, 'renderDashboard'],
            'dashicons-admin-site',
            25
        );

        add_submenu_page(
            self::MENU_SLUG,
            __('Configuración', 'conacyta'),
            __('Configuración', 'conacyta'),
            'manage_options',
            'conacyta-settings',
            [$this, 'render']
        );
    }

    public function renderDashboard(): void
    {
        if (current_user_can('manage_options')) {
            wp_safe_redirect(admin_url('admin.php?page=conacyta-settings'));
        } else {
            wp_safe_redirect(admin_url('edit.php?post_type=ponente'));
        }
        wp_die();
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $active_tab = sanitize_key(wp_unslash($_GET['tab'] ?? 'chatbot'));
        $tabs = [
            'chatbot'  => __('Chatbot', 'conacyta'),
            'contacto' => __('Contacto', 'conacyta'),
            'evento'   => __('Evento', 'conacyta'),
        ];

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Configuración de Conacyta', 'conacyta') . '</h1>';

        echo '<nav class="nav-tab-wrapper">';
        foreach ($tabs as $tab_key => $tab_label) {
            $url = add_query_arg(['page' => 'conacyta-settings', 'tab' => $tab_key], admin_url('admin.php'));
            $class = 'nav-tab' . ($active_tab === $tab_key ? ' nav-tab-active' : '');
            printf(
                '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">' . esc_html($tab_label) . '</a>'
            );
        }
        echo '</nav>';

        echo '<div class="tab-content" style="margin-top:1rem;">';
        echo '<form method="post" action="options.php">';

        if ('chatbot' === $active_tab) {
            settings_fields('conacyta_chatbot');
            do_settings_sections('conacyta_chatbot');
        } elseif ('contacto' === $active_tab) {
            settings_fields('conacyta_contacto');
            do_settings_sections('conacyta_contacto');
        } elseif ('evento' === $active_tab) {
            $evento_subtab = sanitize_key(wp_unslash($_GET['evento_tab'] ?? 'identidad'));
            settings_fields('conacyta_evento_' . $evento_subtab);
            $subtabs = [
                'identidad'  => __('Identidad', 'conacyta'),
                'sede'       => __('Sede', 'conacyta'),
                'countdown'  => __('Countdown', 'conacyta'),
                'sobre'      => __('Sobre', 'conacyta'),
                'secciones'  => __('Secciones', 'conacyta'),
            ];
            echo '<nav class="nav-tab-wrapper" style="margin-bottom:1rem">';
            foreach ($subtabs as $st_key => $st_label) {
                $st_url = add_query_arg(['page' => 'conacyta-settings', 'tab' => 'evento', 'evento_tab' => $st_key], admin_url('admin.php'));
                $st_class = 'nav-tab' . ($evento_subtab === $st_key ? ' nav-tab-active' : '');
                printf('<a href="%s" class="%s">%s</a>', esc_url($st_url), esc_attr($st_class), esc_html($st_label));
            }
            echo '</nav>';
            do_settings_sections('conacyta_evento_' . $evento_subtab);
        }

        submit_button();
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}
