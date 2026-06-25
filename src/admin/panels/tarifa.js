import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { TextControl, SelectControl, ToggleControl, ComboboxControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel } from './utils.js';

function TarifaPanel() {
    var ref = useEntityProp('postType', 'tarifa', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };

    return el(ConditionalPanel, { postType: 'tarifa', name: 'conacyta-tarifa', title: __('Detalles de Tarifa', 'conacyta') },
        el(TextControl, { __next40pxDefaultSize: true, label: __('Precio', 'conacyta'), type: 'number', value: (meta && meta.conacyta_core_tarifa_precio) || '0', onChange: function (v) { u('conacyta_core_tarifa_precio', v === '' ? '0' : v); } }),
        el(SelectControl, { __next40pxDefaultSize: true, label: __('Moneda', 'conacyta'), value: (meta && meta.conacyta_core_tarifa_moneda) || 'PEN', options: [{ label: 'Soles (PEN)', value: 'PEN' }, { label: 'Dólares (USD)', value: 'USD' }], onChange: function (v) { u('conacyta_core_tarifa_moneda', v); } }),
        el(ToggleControl, { __next40pxDefaultSize: true, label: __('Tier destacado', 'conacyta'), help: __('Estilo visual especial (tarjeta central azul).', 'conacyta'), checked: (meta && meta.conacyta_core_tarifa_destacada) === '1' || (meta && meta.conacyta_core_tarifa_destacada) === true, onChange: function (v) { u('conacyta_core_tarifa_destacada', v); } }),
        el(ComboboxControl, { __next40pxDefaultSize: true, label: __('Etiqueta / badge', 'conacyta'), help: __('Texto del badge flotante (escribir o seleccionar)', 'conacyta'), value: (meta && meta.conacyta_core_tarifa_etiqueta) || '', onChange: function (v) { u('conacyta_core_tarifa_etiqueta', v || ''); }, allowReset: true, options: [{ label: 'Ponentes', value: 'Ponentes' }, { label: 'Recomendado', value: 'Recomendado' }, { label: 'Cupos Limitados', value: 'Cupos Limitados' }, { label: 'Nuevo', value: 'Nuevo' }, { label: 'Popular', value: 'Popular' }, { label: '/c.u.', value: '/c.u.' }] }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('Texto del botón', 'conacyta'), value: (meta && meta.conacyta_core_tarifa_boton_texto) || '', onChange: function (v) { u('conacyta_core_tarifa_boton_texto', v); } }),
        el(TextControl, { __next40pxDefaultSize: true, label: __('URL de inscripción', 'conacyta'), help: __('Link del formulario de registro', 'conacyta'), type: 'url', value: (meta && meta.conacyta_core_tarifa_url_inscripcion) || '', onChange: function (v) { u('conacyta_core_tarifa_url_inscripcion', v); } })
    );
}

registerPlugin('conacyta-tarifa-panel', { render: TarifaPanel });