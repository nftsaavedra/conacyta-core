import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { TextControl, SelectControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel } from './utils.js';

function PartnerPanel() {
    var ref = useEntityProp('postType', 'partner', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };

    return el(ConditionalPanel, { postType: 'partner', name: 'conacyta-partner', title: __('Datos del Partner', 'conacyta') },
        el(SelectControl, {
            __next40pxDefaultSize: true,
            label: __('Tipo', 'conacyta'),
            value: (meta && meta.conacyta_core_partner_tipo) || '',
            options: [
                { label: __('Universidad', 'conacyta'), value: 'universidad' },
                { label: __('Estrategico', 'conacyta'), value: 'estrategico' },
                { label: __('Platinium', 'conacyta'), value: 'platinium' },
                { label: __('Gold', 'conacyta'), value: 'gold' },
                { label: __('Colaborador', 'conacyta'), value: 'colaborador' },
            ],
            onChange: function (v) { u('conacyta_core_partner_tipo', v); }
        }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('URL sitio web', 'conacyta'), type: 'url', value: (meta && meta.conacyta_core_partner_url) || '', onChange: function (v) { u('conacyta_core_partner_url', v); } })
    );
}

registerPlugin('conacyta-partner-panel', { render: PartnerPanel });
