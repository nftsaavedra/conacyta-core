<?php

declare(strict_types=1);

if (!defined("WP_UNINSTALL_PLUGIN")) {
    exit;
}

if (get_option("conacyta_core_cleanup_on_uninstall", false)) {
    $options = [
        "conacyta_core_gemini_model",
        "conacyta_core_chat_rate_limit",
        "conacyta_core_system_prompt",
        "conacyta_core_contacto_email",
        "conacyta_core_contacto_whatsapp",
        "conacyta_core_facebook_url",
        "conacyta_core_instagram_url",
        "conacyta_core_linkedin_url",
        "conacyta_core_cleanup_on_uninstall",
        "conacyta_evento_edicion",
        "conacyta_evento_acronimo",
        "conacyta_evento_anio",
        "conacyta_evento_fecha_inicio",
        "conacyta_evento_fecha_fin",
        "conacyta_evento_url_inscripcion",
        "conacyta_evento_sede",
        "conacyta_evento_ciudad",
        "conacyta_evento_organizador",
        "conacyta_evento_facultad",
        "conacyta_evento_countdown_titulo",
        "conacyta_evento_countdown_fecha_objetivo",
        "conacyta_evento_countdown_fase1_badge",
        "conacyta_evento_countdown_fase1_mensaje",
        "conacyta_evento_countdown_fase2_badge",
        "conacyta_evento_countdown_fase2_mensaje",
        "conacyta_evento_countdown_cta_texto",
        "conacyta_evento_countdown_cta_url",
        "conacyta_evento_sobre_titulo",
        "conacyta_evento_sobre_descripcion",
        "conacyta_evento_sobre_imagen_1",
        "conacyta_evento_sobre_imagen_1_alt",
        "conacyta_evento_sobre_imagen_2",
        "conacyta_evento_sobre_imagen_2_alt",
        "conacyta_evento_sobre_imagen_3",
        "conacyta_evento_sobre_imagen_3_alt",
        "conacyta_evento_seccion_ponentes",
        "conacyta_evento_seccion_actividades",
        "conacyta_evento_seccion_agenda",
        "conacyta_evento_seccion_tarifas",
        "conacyta_evento_seccion_comite",
        "conacyta_evento_seccion_partners",
        "conacyta_evento_seccion_ejes",
        "conacyta_core_chatbot_welcome",
        "conacyta_core_chatbot_placeholder",
        "conacyta_core_chatbot_badge",
        "conacyta_core_chatbot_footer_left",
        "conacyta_core_chatbot_footer_right",
        "conacyta_core_ai_provider",
        "conacyta_core_deepseek_api_key",
        "conacyta_core_deepseek_model",
        "conacyta_core_deepseek_endpoint",
        "conacyta_core_agenda_migrated_v2",
    ];

    foreach ($options as $option) {
        delete_option($option);
    }
}