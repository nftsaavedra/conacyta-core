=== Conacyta Core ===
Contributors: nftsaavedra
Tags: congress, conference, speakers, agenda, event, academic
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin core para el XVII CONACYTA 2026. Custom Post Types, REST API,
configuración del evento, asistente IA (Gemini + DeepSeek V4), paneles
Gutenberg y Abilities API (MCP, WP 7.0+).

== Description ==

Conacyta Core es el plugin de lógica de negocio para el sitio del
XVII Congreso Nacional de Ciencia y Tecnología de Alimentos 2026,
organizado por la Universidad Nacional de Frontera en Sullana, Perú.

= Caracteristicas =

* **8 Custom Post Types**: Ponentes, Actividades, Agenda, Tarifas,
  Comite, Partners, Cronograma y Portada, todos con paneles Gutenberg
  en el editor de bloques.
* **4 Taxonomías personalizadas**: Áreas Temáticas, Auditorios, Tipos
  de Sesión y Beneficios de Tarifa.
* **4 REST Endpoints**: Chat IA (proxy Gemini + DeepSeek V4), Historial
  de chat (re-hidratación de conversación), Contacto y Agenda pública
  con filtros.
* **Abilities API (MCP, WP 6.9+ / 7.0+)**: 3 abilities registradas para
  integración con clientes Model Context Protocol y agentes IA externos
  (info del evento, listado de ponentes, agenda filtrada).
* **Página de configuración**: Fechas del evento, sede, organizador,
  cuenta regresiva, textos de UI y más.
* **Block Variations**: Variaciones del bloque Query Loop para cada
  CPT, listas para usar en el Site Editor (FSE).
* **Asistente IA**: Chatbot integrado via Gemini y DeepSeek V4 (gestionado
  server-side). Gemini usa Connectors API de WP 7.0. DeepSeek usa
  function calling con 10 tools nativas (OpenAI-compatible + XML DSML).
* **Compatible con WP 7.0+ y Block Themes (FSE)**.

== Requirements ==

* WordPress 7.0 o superior
* PHP 8.3 o superior
* Tema de bloques (FSE) compatible — recomendado: Conacyta Theme

== Installation ==

1. Sube la carpeta `conacyta-core` a `/wp-content/plugins/`.
2. Activa el plugin desde el menu "Plugins".
3. Ve a "CONACYTA 2026 > Configuración" para establecer las fechas
   del evento y demás ajustes.
4. Para datos de demostracion, copia el archivo
   `wp-content/mu-plugins/conacyta-seed.php` (se auto-ejecuta
   una sola vez).

== Usage ==

Cada CPT aparece en el menu "CONACYTA 2026" del admin:

| Menu | CPT | Panel Gutenberg |
|------|-----|-----------------|
| Ponentes | `ponente` | Título, Institución, País, Bandera |
| Actividades | `actividad` | Icono, Color |
| Sesiones | `agenda_item` | Día, Hora, Ponente, Auditorio, Dot |
| Tarifas | `tarifa` | Precio, Moneda, Badge, Beneficios |
| Comite | `comite_member` | Rol |
| Partners | `partner` | Tipo, URL |
| Convocatoria | `cronograma_fase` | Fechas, Destacada |
| Portadas | `portada` | Tagline, CTAs, Principal |

En el Site Editor, inserta un bloque Query Loop y selecciona la
variación correspondiente (ej. "Ponentes Grid") desde el inspector.

== REST API ==

* `GET /wp-json/conacyta/v1/agenda?dia=1` (publico, cache 5 min)
* `POST /wp-json/conacyta/v1/chat` (requiere nonce + rate limit)
* `GET /wp-json/conacyta/v1/chat/history?session_id=...` (requiere nonce)
* `POST /wp-json/conacyta/v1/contacto` (requiere nonce + rate limit)

== Frontend Integration ==

El plugin esta disenado para llamarse desde el frontend sin necesidad
de login. La REST API expone los datos publicamente (con nonce).

Ejemplo vanilla JS (compatible WP 7.0+):

`
const { chatEndpoint, nonce } = window.conacytaData;

const response = await fetch(chatEndpoint, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': nonce,
  },
  body: JSON.stringify({
    message: '¿Cuáles son las tarifas?',
    session_id: sessionId, // opcional, mantener contexto multi-turno
  }),
});

const { reply, suggestions, session_id } = await response.json();
sessionId = session_id; // guardar para siguiente llamada
`

Con Interactivity API (recomendado WP 7.0+):

`
const { state, actions } = wpStore('conacyta/chatbot', {
  state: {
    get nonce() { return window.conacytaData?.nonce || ''; },
  },
  actions: {
    async sendMessage(message) {
      const res = await fetch(window.conacytaData.chatEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': state.nonce,
        },
        body: JSON.stringify({ message, session_id: state.sessionId }),
      });
      return await res.json();
    },
  },
});
`

El chatbot soporta dos proveedores de IA:
- Gemini (Google) — via Connectors API de WP 7.0
- DeepSeek V4 — via API key propia + Tool Calls (function calling)

== Frequently Asked Questions ==

= Necesito el tema Conacyta? =

No es obligatorio, pero se recomienda. El plugin expone los datos via
REST; cualquier tema puede consumirlos. El tema Conacyta incluye los
patrones de bloque y plantillas FSE optimizadas.

= Cómo configuro el chatbot? =

Ve a "CONACYTA 2026 > Configuración > Chatbot". La API key de Gemini
se gestiona vía Connectors API (WP 7.0+, "Ajustes > Connectors"). Para
DeepSeek, define la key en `wp-config.php` via `CONACYTA_DEEPSEEK_API_KEY`
o ingrésala en la pantalla de Settings.

= Que son las Abilities API? =

El plugin registra 3 abilities para WP 7.0+ (MCP) que exponen datos del
evento a clientes Model Context Protocol y agentes IA externos:
`conacyta/get-site-info`, `conacyta/list-ponentes` y `conacyta/get-agenda`.
Son públicas (sin auth) y se descubren automáticamente vía metadata MCP.

= Cómo genero el archivo de traducciones? =

Ejecuta `npm run i18n:pot` desde el directorio del plugin. Escanea
`src/**/*.php` y `admin/**/*.js`, extrayendo todas las cadenas
internacionalizables con text-domain `conacyta` y generando
`languages/conacyta.pot`.

== Changelog ==

= 1.0.2 =
* Abilities API (MCP, WP 6.9+ / 7.0+): 3 abilities registradas
  (conacyta/get-site-info, conacyta/list-ponentes, conacyta/get-agenda)
  para integración con clientes MCP y agentes IA externos.
* Nuevo endpoint REST `GET /conacyta/v1/chat/history?session_id=...`
  para re-hidratar el historial de conversación al recargar la página.
  Storage unificado en transient (TTL 1800s, max 20 mensajes).
* DeepSeek V4: soporte para tool calls en formato OpenAI estándar
  Y XML DSML nativo (con extracción transparente via regex).
  Multi-turn: hasta 3 rondas consecutivas de tool calls por mensaje.
* Script `npm run i18n:pot` para regenerar `languages/conacyta.pot`
  desde `src/**/*.php` y `admin/**/*.js`.
* Archivo `.editorconfig` con reglas de encoding (UTF-8 sin BOM, LF,
  indentación por tipo) sincronizadas con el tema.
* Soporte para subida de archivos SVG al media library con sanitización
  XML para prevenir XSS.
* Cache de contexto de Gemini: system prompt con datos de CPTs cacheado
  5 min en transient `conacyta_context_prompt` (reduce latencia).
* Defaults en `wp_options` corregidos con tildes y UTF-8 correcto
  (Sullana, Perú; Facultad de Ingeniería de Industrias Alimentarias
  y Biotecnología; Convocatoria Próxima; Envío de Resúmenes; etc.).
* Etiqueta `conacyta_evento_seccion_agenda` renombrada a `Programa`
  con default "Programa Oficial del Congreso".
* Gemini 3.5 Flash (Preview) agregado a los modelos disponibles.
* Rate limit por IP con headers `CF-Connecting-IP` y `X-Forwarded-For`
  para compatibilidad con proxies y CDNs.

= 1.0.1 =
* 8 CPTs con paneles Gutenberg y meta fields tipados: Ponentes, Actividades,
  Agenda, Tarifas, Comite, Partners, Cronograma, Portada.
* 4 taxonomías personalizadas con capacidades por rol.
* 3 REST endpoints públicos: chat IA (POST), contacto (POST), agenda (GET).
* Página de configuración con 3 pestañas (Chatbot, Contacto, Evento) y
  5 sub-pestañas en Evento (Identidad, Sede, Countdown, Sobre, Secciones).
* Chatbot IA dual: Gemini (Connectors API WP 7.0) y DeepSeek V4 (10 tools
  de   function calling).
* 8 Block Variations para el bloque Query Loop, generadas vía
  VariationFactory.
* Registro unificado de meta vía MetaRegistrar (register_post_meta +
  register_rest_field con type coercion automatica).
* Helpers compartidos: EventDateHelper (cálculo de días), AbstractAiClient
  (cliente IA base), Auth (rate limits independientes chat/contacto).
* Seed de datos de demostracion (mu-plugins/conacyta-seed.php).
* Compatible con WP 7.0+ y Block Themes (FSE). Sin jQuery.

== Credits ==

Desarrollado por Leumin Omar Saavedra Peña (@nftsaavedra).
Soporte tecnológico: Unidad de Proyectos de Investigación,
Universidad Nacional de Frontera.

XVII CONACYTA 2026 — Sullana, Perú.
