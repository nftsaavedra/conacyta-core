import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { TextControl, ToggleControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel } from './utils.js';

function CronogramaPanel() {
    var ref = useEntityProp('postType', 'cronograma_fase', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };

    return el(ConditionalPanel, { postType: 'cronograma_fase', name: 'conacyta-cronograma', title: __('Detalles de Fase', 'conacyta') },
        el(TextControl, { __next40pxDefaultSize: true, label: __('Fecha inicio', 'conacyta'), help: 'Ej: 01 Junio, 2026', value: (meta && meta.conacyta_core_fase_fecha_inicio) || '', onChange: function (v) { u('conacyta_core_fase_fecha_inicio', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('Fecha fin', 'conacyta'), help: 'Ej: 30 Junio, 2026', value: (meta && meta.conacyta_core_fase_fecha_fin) || '', onChange: function (v) { u('conacyta_core_fase_fecha_fin', v); } }),
        el(ToggleControl, { __next40pxDefaultSize: true, label: __('Fase destacada', 'conacyta'), help: __('Aplica color verde al badge.', 'conacyta'), checked: (meta && meta.conacyta_core_fase_destacada) === '1' || (meta && meta.conacyta_core_fase_destacada) === true, onChange: function (v) { u('conacyta_core_fase_destacada', v); } })
    );
}

registerPlugin('conacyta-cronograma-panel', { render: CronogramaPanel });