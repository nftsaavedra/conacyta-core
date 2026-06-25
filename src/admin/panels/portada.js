import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { TextControl, ToggleControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel } from './utils.js';

function PortadaPanel() {
    var ref = useEntityProp('postType', 'portada', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };

    return el(ConditionalPanel, { postType: 'portada', name: 'conacyta-portada', title: __('Detalles de Portada', 'conacyta') },
        el(ToggleControl, {
            __next40pxDefaultSize: true,
            label: __('Portada principal', 'conacyta'),
            help: __('Solo una portada puede ser principal a la vez. Al marcar esta, se desmarcan automáticamente las demás.', 'conacyta'),
            checked: meta && meta.conacyta_core_portada_principal === true,
            onChange: function (v) { u('conacyta_core_portada_principal', v); },
        }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('Tagline', 'conacyta'), help: __('Texto descriptivo bajo el título.', 'conacyta'), value: (meta && meta.conacyta_core_portada_tagline) || '', onChange: function (v) { u('conacyta_core_portada_tagline', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('Texto CTA principal', 'conacyta'), value: (meta && meta.conacyta_core_portada_cta_texto) || '', onChange: function (v) { u('conacyta_core_portada_cta_texto', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('URL CTA principal', 'conacyta'), type: 'url', value: (meta && meta.conacyta_core_portada_cta_url) || '', onChange: function (v) { u('conacyta_core_portada_cta_url', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('Texto CTA secundario', 'conacyta'), value: (meta && meta.conacyta_core_portada_cta2_texto) || '', onChange: function (v) { u('conacyta_core_portada_cta2_texto', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('URL CTA secundario', 'conacyta'), type: 'url', value: (meta && meta.conacyta_core_portada_cta2_url) || '', onChange: function (v) { u('conacyta_core_portada_cta2_url', v); } })
    );
}

registerPlugin('conacyta-portada-panel', { render: PortadaPanel });
