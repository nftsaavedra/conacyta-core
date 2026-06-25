import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { ComboboxControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel, iconos, colores } from './utils.js';

function ActividadPanel() {
    var ref = useEntityProp('postType', 'actividad', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };
    var currentColor = (meta && meta.conacyta_core_actividad_color_tailwind) || '';
    var colorOptions = colores.map(function (c) { return { label: c.label, value: c.value, keywords: c.keywords }; });
    var randomColor = currentColor || colores[0].value;

    return el(ConditionalPanel, { postType: 'actividad', name: 'conacyta-actividad', title: __('Detalles de Actividad', 'conacyta') },
        el(ComboboxControl, {
            __next40pxDefaultSize: true,
            label: __('Icono', 'conacyta'),
            help: __('Escribe para buscar entre los iconos disponibles.', 'conacyta'),
            value: (meta && meta.conacyta_core_actividad_icono) || '',
            options: iconos.map(function (i) { return { label: i.label, value: i.value, keywords: i.keywords }; }),
            onChange: function (v) { u('conacyta_core_actividad_icono', v || ''); },
            onFilterValueChange: function (filterValue, options) {
                if (!filterValue) return options;
                var lower = filterValue.toLowerCase();
                return options.filter(function (o) {
                    return o.label.toLowerCase().indexOf(lower) >= 0 || (o.keywords && o.keywords.toLowerCase().indexOf(lower) >= 0) || o.value.toLowerCase().indexOf(lower) >= 0;
                });
            }
        }),
        el(ComboboxControl, {
            __next40pxDefaultSize: true,
            label: __('Color del circulo', 'conacyta'),
            help: __('Escribe rojo, azul, verde... para filtrar colores Tailwind.', 'conacyta'),
            value: currentColor || randomColor,
            options: colorOptions,
            onChange: function (v) { u('conacyta_core_actividad_color_tailwind', v || ''); },
            onFilterValueChange: function (filterValue, options) {
                if (!filterValue) return options;
                var lower = filterValue.toLowerCase();
                return options.filter(function (o) {
                    return o.label.toLowerCase().indexOf(lower) >= 0 || (o.keywords && o.keywords.toLowerCase().indexOf(lower) >= 0) || o.value.toLowerCase().indexOf(lower) >= 0;
                });
            }
        })
    );
}

registerPlugin('conacyta-actividad-panel', { render: ActividadPanel });