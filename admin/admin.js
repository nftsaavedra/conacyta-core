// Vanilla JS: Modal confirmación cambio de fechas + sync año
// NO compilado - mantenido fuera de admin/js/ para que wp-scripts no lo borre
document.addEventListener("DOMContentLoaded", function () {
    var fechaInicio = document.querySelector("[name=\"conacyta_evento_fecha_inicio\"]");
    var fechaFin    = document.querySelector("[name=\"conacyta_evento_fecha_fin\"]");
    var inputAnio   = document.querySelector("[name=\"conacyta_evento_anio\"]");
    var form        = document.querySelector("form");

    function syncAnio() {
        if (fechaInicio && inputAnio && fechaInicio.value) {
            var anio = fechaInicio.value.substring(0, 4);
            if (inputAnio.value !== anio) {
                inputAnio.value = anio;
            }
        }
    }

    if (fechaInicio && fechaFin && inputAnio) {
        fechaInicio.addEventListener("change", syncAnio);
        syncAnio();
    }

    if (fechaInicio && fechaFin && form) {
        form.addEventListener("submit", function (e) {
            var savedInicio = fechaInicio.getAttribute("data-original") || "";
            var savedFin    = fechaFin.getAttribute("data-original") || "";
            if (fechaInicio.value === savedInicio && fechaFin.value === savedFin) {
                return;
            }
            e.preventDefault();

            var confirmPhrase = window.conacytaData ? window.conacytaData.confirmPhrase : "CAMBIAR FECHAS CONACYTA";
            var overlay = document.createElement("div");
            overlay.className = "conacyta-confirm-overlay";

            var modal = document.createElement("div");
            modal.className = "conacyta-confirm-modal";

            var h2 = document.createElement("h2");
            h2.textContent = "Confirmar cambio de fechas";
            modal.appendChild(h2);

            var implications = document.createElement("div");
            implications.className = "conacyta-implications";
            implications.textContent = "Cambiar las fechas del evento puede afectar la agenda, la cuenta regresiva y otras secciones del sitio. Esta acción no se puede deshacer automáticamente.";
            modal.appendChild(implications);

            var p = document.createElement("p");
            p.textContent = "Para confirmar, escribe ";
            var strong = document.createElement("strong");
            strong.textContent = confirmPhrase;
            p.appendChild(strong);
            p.appendChild(document.createTextNode(" en el campo de abajo:"));
            modal.appendChild(p);

            var input = document.createElement("input");
            input.type = "text";
            input.className = "widefat";
            input.placeholder = confirmPhrase;
            input.style.cssText = "margin-top:8px;font-size:14px;padding:10px;width:100%";
            modal.appendChild(input);

            var actions = document.createElement("div");
            actions.className = "conacyta-confirm-actions";

            var btnSubmit = document.createElement("button");
            btnSubmit.type = "button";
            btnSubmit.className = "button button-primary conacyta-submit";
            btnSubmit.disabled = true;
            btnSubmit.textContent = "Confirmar";
            actions.appendChild(btnSubmit);

            var btnCancel = document.createElement("button");
            btnCancel.type = "button";
            btnCancel.className = "button button-secondary conacyta-cancel";
            btnCancel.textContent = "Cancelar";
            actions.appendChild(btnCancel);

            modal.appendChild(actions);

            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            input.addEventListener("input", function () {
                btnSubmit.disabled = input.value !== confirmPhrase;
            });

            btnSubmit.addEventListener("click", function () {
                if (input.value === confirmPhrase) {
                    fechaInicio.setAttribute("data-original", fechaInicio.value);
                    fechaFin.setAttribute("data-original", fechaFin.value);
                    overlay.remove();
                    form.requestSubmit();
                }
            });

            btnCancel.addEventListener("click", function () {
                overlay.remove();
            });

            overlay.addEventListener("click", function (ev) {
                if (ev.target === overlay) overlay.remove();
            });

            input.focus();
        });
    }

    // Handlers de media upload para imagenes de Sobre
    var mediaFrame;
    document.querySelectorAll('.conacyta-media-select').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var container = btn.closest('.conacyta-media-field');
            var input = container.querySelector('input[type="hidden"]');
            var preview = container.querySelector('.conacyta-media-preview');
            var removeBtn = container.querySelector('.conacyta-media-remove');

            if (!mediaFrame) {
                mediaFrame = wp.media({ title: 'Seleccionar imagen', library: { type: 'image' }, multiple: false });
            }
            mediaFrame.off('select');
            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                input.value = attachment.id;
                var img = document.createElement('img');
                img.src = attachment.sizes.medium.url;
                img.style.cssText = 'max-width:200px;display:block;margin:8px 0;border-radius:8px';
                img.alt = '';
                preview.innerHTML = '';
                preview.appendChild(img);
                if (removeBtn) removeBtn.style.display = '';
            });
            mediaFrame.open();
        });
    });

    document.querySelectorAll('.conacyta-media-remove').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var container = btn.closest('.conacyta-media-field');
            var input = container.querySelector('input[type="hidden"]');
            var preview = container.querySelector('.conacyta-media-preview');
            input.value = '0';
            preview.innerHTML = '';
            btn.style.display = 'none';
        });
    });
});
