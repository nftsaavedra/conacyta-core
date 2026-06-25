import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { TextControl, Button } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel } from './utils.js';

function PonentePanel() {
    var ref = useEntityProp('postType', 'ponente', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };
    var banderaId = parseInt(meta && meta.conacyta_core_ponente_bandera_id || '0', 10);
    var banderaMedia = useSelect(
        function (select) { return banderaId > 0 ? select('core').getMedia(banderaId) : null; },
        [banderaId]
    );

    return el(ConditionalPanel, { postType: 'ponente', name: 'conacyta-ponente', title: __('Datos del Ponente', 'conacyta') },
        el(TextControl, { __next40pxDefaultSize: true, label: __('Título académico', 'conacyta'), help: 'Dr., Dra., M.Sc., Ph.D.', value: (meta && meta.conacyta_core_ponente_titulo) || '', onChange: function (v) { u('conacyta_core_ponente_titulo', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('Institución', 'conacyta'), value: (meta && meta.conacyta_core_ponente_institucion) || '', onChange: function (v) { u('conacyta_core_ponente_institucion', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('País', 'conacyta'), help: 'Ej: México, Perú, España', value: (meta && meta.conacyta_core_ponente_pais) || '', onChange: function (v) { u('conacyta_core_ponente_pais', v); } }),
        el('div', { className: 'components-base-control', style: { marginBottom: 24 } },
            el('div', { className: 'components-base-control__field' },
                el('label', { className: 'components-base-control__label' }, __('Bandera del país (16:9)', 'conacyta')),
                el(MediaUploadCheck, null,
                    el(MediaUpload, {
                        onSelect: function (media) { u('conacyta_core_ponente_bandera_id', parseInt(media.id, 10)); },
                        value: banderaId,
                        allowedTypes: ['image'],
                        render: function (obj) {
                            var open = obj.open;
                            if (banderaId > 0 && banderaMedia) {
                                return el('div', null,
                                    el('img', { src: banderaMedia.source_url || '', style: { maxWidth: '100%', aspectRatio: '16/9', objectFit: 'cover', borderRadius: 8, marginBottom: 8, display: 'block' }, alt: __('Bandera', 'conacyta') }),
                                    el('div', { style: { display: 'flex', gap: 8 } },
                                        el(Button, { __next40pxDefaultSize: true, onClick: open, variant: 'secondary' }, __('Reemplazar', 'conacyta')),
                                        el(Button, { __next40pxDefaultSize: true, onClick: function () { u('conacyta_core_ponente_bandera_id', 0); }, variant: 'tertiary', __experimentalIsDestructive: true }, __('Eliminar', 'conacyta'))
                                    )
                                );
                            }
                            return el(Button, { __next40pxDefaultSize: true, onClick: open, variant: 'secondary' }, __('Seleccionar bandera', 'conacyta'));
                        }
                    })
                ),
                el('p', { className: 'components-base-control__help', style: { marginTop: 4 } }, __('Imagen panoramica 16:9 desde el gestor de medios.', 'conacyta'))
            )
        )
    );
}

registerPlugin('conacyta-ponente-panel', { render: PonentePanel });