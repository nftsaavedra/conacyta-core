import { registerPlugin } from '@wordpress/plugins';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { SelectControl, ComboboxControl } from '@wordpress/components';
import { el, updateMetaField, ConditionalPanel, colores, getEventDays } from './utils.js';

function safeTime(val, fallback) {
    return (val && /^\d{2}:\d{2}$/.test(val)) ? val : fallback;
}

function AgendaPanel() {
    var ref = useEntityProp('postType', 'agenda_item', 'meta');
    var meta = ref[0];
    var u = function (key, value) { updateMetaField(meta, key, value); };

    var ponentes = useSelect(
        function (select) {
            var records = select('core').getEntityRecords('postType', 'ponente', { per_page: 100, _fields: 'id,title' });
            return records ? records.map(function (p) { return { label: p.title.rendered, value: String(p.id) }; }) : [];
        },
        []
    );

    var currentColor = (meta && meta.conacyta_core_agenda_color_dot) || '';
    var colorOptions = colores.map(function (c) { return { label: c.label, value: c.value, keywords: c.keywords }; });

    return el(ConditionalPanel, { postType: 'agenda_item', name: 'conacyta-agenda', title: __('Detalles de Agenda', 'conacyta') },
        el(SelectControl, {
            __next40pxDefaultSize: true,
            label: __('Dia del evento', 'conacyta'),
            value: String(meta && meta.conacyta_core_agenda_dia || '1'),
            options: getEventDays(),
            onChange: function (v) { u('conacyta_core_agenda_dia', v); },
        }),
        el('div', { className: 'components-base-control', style: { marginBottom: 24 } },
            el('label', { className: 'components-base-control__label', style: { display: 'block', marginBottom: 8 } }, __('Hora de inicio', 'conacyta')),
            el('input', {
                type: 'time',
                className: 'components-text-control__input',
                value: safeTime(meta && meta.conacyta_core_agenda_hora_inicio, '09:00'),
                onChange: function (e) { u('conacyta_core_agenda_hora_inicio', e.target.value); },
                style: { maxWidth: '10em' },
            })
        ),
        el('div', { className: 'components-base-control', style: { marginBottom: 24 } },
            el('label', { className: 'components-base-control__label', style: { display: 'block', marginBottom: 8 } }, __('Hora de fin', 'conacyta')),
            el('input', {
                type: 'time',
                className: 'components-text-control__input',
                value: safeTime(meta && meta.conacyta_core_agenda_hora_fin, '10:00'),
                onChange: function (e) { u('conacyta_core_agenda_hora_fin', e.target.value); },
                style: { maxWidth: '10em' },
            })
        ),
        el(ComboboxControl, {
            __next40pxDefaultSize: true,
            label: __('Ponente asociado', 'conacyta'),
            help: __('Opcional. Busca por nombre del ponente.', 'conacyta'),
            value: (meta && meta.conacyta_core_agenda_ponente_id) ? String(meta.conacyta_core_agenda_ponente_id) : '',
            options: [{ label: __('--- Sin ponente ---', 'conacyta'), value: '' }].concat(ponentes),
            onChange: function (v) { u('conacyta_core_agenda_ponente_id', v ? Number(v) : 0); },
            onFilterValueChange: function (filterValue, options) {
                if (!filterValue) return options;
                var lower = filterValue.toLowerCase();
                return options.filter(function (o) { return o.label.toLowerCase().indexOf(lower) >= 0; });
            },
        }),
        el(ComboboxControl, {
            __next40pxDefaultSize: true,
            label: __('Color del dot', 'conacyta'),
            help: __('Opcional. Clase Tailwind bg-* del circulo.', 'conacyta'),
            value: currentColor || '',
            options: [{ label: __('--- Sin color ---', 'conacyta'), value: '' }].concat(colorOptions),
            onChange: function (v) { u('conacyta_core_agenda_color_dot', v || ''); },
            onFilterValueChange: function (filterValue, options) {
                if (!filterValue) return options;
                var lower = filterValue.toLowerCase();
                return options.filter(function (o) {
                    return o.label.toLowerCase().indexOf(lower) >= 0 || (o.keywords && o.keywords.toLowerCase().indexOf(lower) >= 0) || o.value.toLowerCase().indexOf(lower) >= 0;
                });
            },
        })
    );
}

registerPlugin('conacyta-agenda-panel', { render: AgendaPanel });
