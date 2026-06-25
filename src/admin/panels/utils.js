import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { TextControl, SelectControl, ToggleControl, Button, ComboboxControl } from '@wordpress/components';
import iconos from '../../../admin/icons.json';
import colores from '../../../admin/colors.json';

export var el = wp.element.createElement;

export function updateMetaField(meta, key, value) {
    var changes = {};
    changes[key] = value;
    wp.data.dispatch('core/editor').editPost({ meta: changes });
}

export function ConditionalPanel({ postType, name, title, children }) {
    var currentType = useSelect(
        function (select) { return select('core/editor').getCurrentPostType(); },
        []
    );
    if (currentType !== postType) return null;
    return el(PluginDocumentSettingPanel, { name: name, title: title, initialOpen: true }, children);
}

export function computeEventDays(startDate, endDate) {
    var parts = startDate.split('-');
    var start = new Date(+parts[0], +parts[1] - 1, +parts[2], 12, 0, 0);
    parts = endDate.split('-');
    var end = new Date(+parts[0], +parts[1] - 1, +parts[2], 12, 0, 0);
    if (isNaN(start.getTime())) { start = new Date(2026, 9, 12, 12, 0, 0); }
    if (isNaN(end.getTime())) { end = new Date(2026, 9, 16, 12, 0, 0); }
    if (end < start) { end = new Date(start); end.setDate(end.getDate() + 4); }
    var days = [];
    var current = new Date(start);
    var dia = 1;
    var MAX_DAYS = 30;
    while (current <= end && dia <= MAX_DAYS) {
        var label = 'Dia ' + dia;
        days.push({ label: label, value: String(dia) });
        current.setDate(current.getDate() + 1);
        dia++;
    }
    return days;
}

export function getEventDays() {
    var inicio = (window.conacytaData && window.conacytaData.eventoFechaInicio) || '2026-10-12';
    var fin = (window.conacytaData && window.conacytaData.eventoFechaFin) || '2026-10-16';
    return computeEventDays(inicio, fin);
}

export { iconos, colores };