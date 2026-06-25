import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel } from './utils.js';

function ComitePanel() {
    var ref = useEntityProp('postType', 'comite_member', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };

    return el(ConditionalPanel, { postType: 'comite_member', name: 'conacyta-comite', title: __('Datos del Miembro', 'conacyta') },
        el(SelectControl, {
            __next40pxDefaultSize: true,
            label: __('Rol', 'conacyta'),
            value: (meta && meta.conacyta_core_comite_rol) || '',
            options: [
                { label: __('Presidente', 'conacyta'), value: 'Presidente' },
                { label: __('Secretario', 'conacyta'), value: 'Secretario' },
                { label: __('Tesorero', 'conacyta'), value: 'Tesorero' },
                { label: __('Vocal', 'conacyta'), value: 'Vocal' },
                { label: __('Coordinador', 'conacyta'), value: 'Coordinador' },
            ],
            onChange: function (v) { u('conacyta_core_comite_rol', v); }
        })
    );
}

registerPlugin('conacyta-comite-panel', { render: ComitePanel });
