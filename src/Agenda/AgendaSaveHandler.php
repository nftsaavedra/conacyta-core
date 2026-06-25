<?php

declare(strict_types=1);

namespace ConacytaCore\Agenda;

use WP_Error;
use WP_Post;
use WP_Query;

final class AgendaSaveHandler
{
    private const META_DIA      = 'conacyta_core_agenda_dia';
    private const META_INICIO   = 'conacyta_core_agenda_hora_inicio';
    private const META_FIN      = 'conacyta_core_agenda_hora_fin';
    private const META_DURACION = 'conacyta_core_agenda_duracion_minutos';
    private const META_ORDEN    = 'conacyta_core_agenda_orden';
    private const MAX_SESIONES_POR_DIA = 200;

    public function register(): void
    {
        add_action('save_post_agenda_item', [$this, 'onSave'], 10, 3);
        add_action('admin_notices', [$this, 'renderNotices']);
    }

    /**
     * @param int $postId
     * @param WP_Post $post
     * @param bool $update
     */
    public function onSave(int $postId, WP_Post $post, bool $update): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($postId)) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        $inicio = (string) get_post_meta($postId, self::META_INICIO, true);
        $fin    = (string) get_post_meta($postId, self::META_FIN, true);
        $dia    = (int) get_post_meta($postId, self::META_DIA, true);

        $result = AgendaValidator::validateAndNormalize([
            self::META_DIA    => $dia,
            self::META_INICIO => $inicio,
            self::META_FIN    => $fin,
        ]);

        if (!$result['ok']) {
            $this->setErrorNotice($result['errors']);
            return;
        }

        if (
            AgendaValidator::isValidTime($inicio)
            && AgendaValidator::isValidTime($fin)
        ) {
            $duration = AgendaValidator::duration($inicio, $fin);
            update_post_meta($postId, self::META_DURACION, $duration);
        }

        $auditorioIds = wp_get_object_terms($postId, AuditorioTaxonomy::TAXONOMY, ['fields' => 'ids']);
        $overlap = AgendaValidator::detectOverlap(
            $postId,
            $dia,
            is_array($auditorioIds) ? array_map('intval', $auditorioIds) : [],
            $inicio,
            $fin,
        );

        if ($overlap instanceof WP_Error) {
            $this->setErrorNotice($overlap);
            return;
        }

        $this->autoAssignOrden($postId, $dia);
    }

    private function autoAssignOrden(int $postId, int $dia): void
    {
        $current = (int) get_post_meta($postId, self::META_ORDEN, true);
        if ($current > 0) {
            return;
        }

        $query = new WP_Query([
            'post_type'      => 'agenda_item',
            'post_status'    => ['publish', 'future', 'pending', 'draft'],
            'posts_per_page' => self::MAX_SESIONES_POR_DIA,
            'meta_query'     => [
                [
                    'key'   => self::META_DIA,
                    'value' => $dia,
                    'type'  => 'NUMERIC',
                ],
            ],
            'fields' => 'ids',
        ]);

        $max = 0;
        foreach ($query->posts as $otherId) {
            $o = (int) get_post_meta($otherId, self::META_ORDEN, true);
            if ($o > $max) {
                $max = $o;
            }
        }

        update_post_meta($postId, self::META_ORDEN, $max + 1);
    }

    private function setErrorNotice(WP_Error $errors): void
    {
        $data = $errors->get_error_data();
        $postId = 0;

        if (is_array($data) && isset($data['post_id'])) {
            $postId = (int) $data['post_id'];
        } else {
            $postId = (int) (wp_unslash($_POST['post_ID'] ?? '0'));
        }

        if ($postId <= 0) {
            return;
        }

        $messages = $errors->get_error_messages();
        set_transient(
            'conacyta_agenda_save_error_' . $postId,
            $messages,
            60
        );
    }

    public function renderNotices(): void
    {
        $screen = get_current_screen();
        if (!$screen || 'agenda_item' !== $screen->post_type) {
            return;
        }

        $postId = isset($_GET['post']) ? (int) wp_unslash($_GET['post']) : 0;
        if ($postId <= 0) {
            return;
        }

        $key = 'conacyta_agenda_save_error_' . $postId;
        $messages = get_transient($key);
        if (empty($messages) || !is_array($messages)) {
            return;
        }

        delete_transient($key);

        echo '<div class="notice notice-error is-dismissible">';
        echo '<p><strong>' . esc_html__('Error al guardar la sesión de agenda:', 'conacyta') . '</strong></p>';
        echo '<ul style="list-style:disc;padding-left:1.5em;margin:0">';
        foreach ($messages as $msg) {
            echo '<li>' . esc_html($msg) . '</li>';
        }
        echo '</ul></div>';
    }
}