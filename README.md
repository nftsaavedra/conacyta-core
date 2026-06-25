# Conacyta Core · ![WP](https://img.shields.io/badge/WordPress-7.0%2B-blue) ![PHP](https://img.shields.io/badge/PHP-8.3%2B-purple) ![License](https://img.shields.io/badge/License-GPLv2-green) ![Release](https://img.shields.io/badge/Release-v1.0.2-blue)

**Plugin core** para el **XVII CONACYTA 2026** — Congreso Nacional de Ciencia y Tecnología de Alimentos.  
8 Custom Post Types, REST API, asistente IA (Gemini + DeepSeek V4), paneles Gutenberg para el editor de bloques.

> **Estado**: Estable. Configuración completa del evento, chatbot dual (Gemini + DeepSeek V4), Abilities API (MCP), 4 endpoints REST, 8 CPTs y 4 taxonomías.

Organizado por la **Universidad Nacional de Frontera** · Sullana, Perú.

---

## 📦 Características

| Categoría | Detalle |
|---|---|
| 🧩 **8 CPTs** | Ponentes, Actividades, Agenda, Tarifas, Comité, Partners, Cronograma, Portada |
| 🏷️ **4 Taxonomías** | Áreas Temáticas, Auditorios, Tipos de Sesión, Beneficios de Tarifa |
| 🔌 **4 REST Endpoints** | Chat IA, Historial de chat, Formulario de Contacto, Agenda pública con filtros |
| 🤖 **Abilities API (MCP)** | 3 abilities WP 7.0+ para integración MCP (sitio, ponentes, agenda) |
| ⚙️ **Página de configuración** | Fechas del evento, sede, cuenta regresiva, chatbot, contacto |
| 🧱 **Block Variations** | Query Loop para cada CPT — listo para Site Editor (FSE) |
| 💬 **Chatbot Enterprise** | Gemini (Connectors API) + DeepSeek V4 (OpenAI tool calls + XML DSML) |
| 🎨 **Paneles Gutenberg** | `PluginDocumentSettingPanel` con meta fields tipados |
| ⚡ **WP 7.0+ & FSE** | Block Theme compatible, sin jQuery, Interactivity API |

---

## 📁 Arquitectura

```
conacyta-core/
├── conacyta-core.php          # Punto de entrada y constants
├── uninstall.php              # Limpieza al desinstalar
├── composer.json              # PSR-4: ConacytaCore\ → src/
├── package.json               # @wordpress/scripts build
├── README.md                  # Este archivo
├── readme.txt                 # WordPress.org plugin directory
│
├── src/                       # Lógica de negocio
│   ├── Core/                  # Plugin lifecycle, Assets
│   ├── Shared/                # Auth, Sanitizer, MetaRegistrar, VariationFactory, EventDateHelper
│   ├── Ponente/               # CPT + Variation
│   ├── Actividad/             # CPT + Variation
│   ├── Agenda/                # CPT + Variation + REST + Query Filter
│   ├── Tarifa/                # CPT + Variation + Taxonomía Beneficio
│   ├── Comite/                # CPT + Variation
│   ├── Partner/               # CPT + Variation
│   ├── Cronograma/            # CPT + Variation
│   ├── Portada/               # CPT + Variation + Query Filter
│   ├── AreaTematica/          # Taxonomía + Query Filter
│   ├── Abilities/             # WP 7.0+ AbilityRegistrar (MCP: site-info, ponentes, agenda)
│   ├── Chatbot/               # AbstractAiClient + GeminiClient + DeepSeekClient + Settings + REST
│   ├── Contacto/              # REST Controller + Settings
│   ├── Settings/              # EventoSettings + SettingsPage
│   └── admin/                 # Paneles Gutenberg (8 features)
│       ├── index.js           # Entry — registra todos los paneles
│       └── panels/            # 8 paneles JS (uno por CPT)
│
├── admin/                     # Assets
│   ├── admin.js               # Modal confirmacion + sync ano (vanilla, no compilado)
│   ├── css/editor.css         # Estilos del editor
│   ├── colors.json            # Paleta de colores para Combobox
│   ├── icons.json             # Iconos Font Awesome para Combobox
│   └── js/                    # Output de npm run build
│       ├── index.js           # Paneles compilados
│       └── index.asset.php    # Dependencias auto-resueltas
│
├── languages/                 # Traducciones (.pot)
└── vendor/                    # Composer autoload (PSR-4)
```

---

## ⚡ Quick Start

```bash
# 1. Instalar dependencias
cd wp-content/plugins/conacyta-core
composer install
npm install

# 2. Compilar assets
npm run build

# 3. (Opcional) Regenerar archivo de traducciones .pot
npm run i18n:pot

# 4. Activar desde wp-admin > Plugins
# 5. Configurar: CONACYTA 2026 > Configuración > Evento
# 6. Cargar datos de demo (opcional)
# Copia el seed a mu-plugins/ — se ejecuta automaticamente una sola vez
cp conacyta-seed.php wp-content/mu-plugins/
```

---

## 🔌 REST API

Todos los endpoints se exponen bajo el namespace `conacyta/v1`. Los endpoints autenticados requieren el header `X-WP-Nonce: {nonce}` y un rate limit de **60 req/min/IP** (configurable en `conacyta_core_chat_rate_limit`).

### Chatbot — envío de mensaje

```js
POST /wp-json/conacyta/v1/chat
Content-Type: application/json
X-WP-Nonce: {nonce}

{
  "message": "¿Cuáles son las tarifas?",
  "session_id": "abc-123"        // opcional — mantiene contexto multi-turno
}

→ {
  "reply": "Tenemos 3 tarifas: Asistente S/150, Investigador S/200...",
  "suggestions": ["¿Qué incluye?", "¿Dónde inscribirme?", "Fechas"],
  "session_id": "abc-123"
}
```

### Chatbot — historial de sesión

```js
GET /wp-json/conacyta/v1/chat/history?session_id=abc-123
X-WP-Nonce: {nonce}

→ {
  "session_id": "abc-123",
  "messages": [
    { "role": "user", "text": "¿Cuáles son las tarifas?" },
    { "role": "bot",  "text": "Tenemos 3 tarifas..." }
  ],
  "total": 2
}
```

Permite re-hidratar la conversación cuando el usuario recarga la página o vuelve más tarde. Storage server-side (transient `conacyta_chat_hist_{session_id}`, TTL 1800s, max 20 mensajes). Nunca retorna 404 — si no hay historial devuelve `messages: []` con 200.

### Agenda pública

```js
GET /wp-json/conacyta/v1/agenda?dia=1

→ {
  "items": [
    { "id": 1, "titulo": "Registro e Inauguración", "dia": 1, "hora_inicio": "08:00", "ponente": { ... } },
    ...
  ],
  "total": 45,
  "total_pages": 1
}
```

Cache 5 min. Filtros: `dia`, `auditorio`, `tipo`, `ponente_id`, `per_page`, `page`.

### Contacto

```js
POST /wp-json/conacyta/v1/contacto
X-WP-Nonce: {nonce}
{
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "mensaje": "Información sobre ponencias"
}

→ { "message": "Mensaje enviado correctamente..." }
```

---

## 🤖 Chatbot — Proveedores IA

| Proveedor | API Key | Function Calling | Modelos |
|---|---|---|---|
| **DeepSeek V4** | API key propia (también vía constante `CONACYTA_DEEPSEEK_API_KEY`) | ✅ OpenAI-compatible + XML DSML nativo | `deepseek-v4-flash` · `deepseek-v4-pro` |
| **Gemini (Google)** | Connectors API WP 7.0 (`Settings > Connectors`) | ❌ Flash-Lite no soporta | `gemini-3.1-flash-lite` · `gemini-3.5-flash` (Preview) · `gemini-3.1-pro` |

### Tools disponibles (DeepSeek)

| Tool | Fuente |
|---|---|
| `get_evento` | wp_options (EventoSettings) |
| `get_tarifas` | CPT `tarifa` |
| `get_ponentes` | CPT `ponente` |
| `get_agenda` | CPT `agenda_item` |
| `get_comite` | CPT `comite_member` |
| `get_cronograma` | CPT `cronograma_fase` |
| `get_actividades` | CPT `actividad` |
| `get_partners` | CPT `partner` |
| `buscar_informacion` | `WP_Query` (posts + páginas, top 5 por relevancia) |
| `get_conocimiento_general` | Conocimiento del modelo + zonas Sullana/Piura |

El modelo decide en **tiempo real** qué tool llamar según la pregunta del usuario. Soporta **dos formatos de tool calls**:
- **OpenAI estándar** (`finish_reason: tool_calls`) — usado por `deepseek-v4-flash`/`v4-pro`.
- **XML DSML nativo** (`<调用 name="..."><参数 name="...">...</参数></调用>`) — usado por variantes chinas. Extraído por regex y ejecutado transparentemente.

Multi-turn: hasta 3 rondas consecutivas de tool calls por mensaje. Tras tool execution, el prompt de Gemini se cachea en transient `conacyta_context_prompt` (TTL 300s) para reducir latencia.

### Sugerencias estructuradas

Cada respuesta del bot **debe** terminar con un bloque `---SUGERENCIAS---` y exactamente 2 preguntas sugeridas. El cliente extrae y expone como array `suggestions[]` en la respuesta REST.

---

## 🎨 Paneles Gutenberg

Cada CPT tiene un panel en el sidebar del editor de bloques (`PluginDocumentSettingPanel`):

| CPT | Panel | Campos |
|---|---|---|
| `ponente` | Datos del Ponente | Título académico, Institución, País, Bandera |
| `actividad` | Detalles de Actividad | Icono (Font Awesome), Color Tailwind |
| `agenda_item` | Detalles de Agenda | Día, Hora inicio/fin, Ponente, Color dot |
| `tarifa` | Detalles de Tarifa | Precio, Moneda, Badge, URL inscripción |
| `comite_member` | Datos del Miembro | Rol (Presidente, Secretario, Tesorero, Vocal, Coordinador) |
| `partner` | Datos del Partner | Tipo, URL |
| `cronograma_fase` | Detalles de Fase | Fecha inicio, Fecha fin, Destacada |
| `portada` | Detalles de Portada | Principal, Tagline, CTA principal/secundario |

---

## 🌐 Frontend Integration

El plugin expone `window.conacytaData` en todas las páginas del frontend:

```js
// window.conacytaData
{
  restUrl: "https://.../wp-json/conacyta/v1",
  nonce: "b09cd91446...",        // Nonce REST válido sin login
  chatEndpoint: ".../chat",
  eventoFechaInicio: "2026-10-12",
  eventoFechaFin: "2026-10-16"
}
```

### Vanilla JS (fetch)

```js
const { chatEndpoint, nonce } = window.conacytaData;

const res = await fetch(chatEndpoint, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': nonce,
  },
  body: JSON.stringify({
    message: '¿Cuáles son las tarifas?',
    session_id: sessionId, // opcional — mantener contexto multi-turno
  }),
});

const { reply, suggestions, session_id } = await res.json();
```

### Interactivity API (recomendado WP 7.0+)

```js
import { store } from '@wordpress/interactivity';

store('conacyta/chatbot', {
  state: {
    get nonce() {
      return window.conacytaData?.nonce || '';
    },
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
```

**No requiere login.** El nonce REST es generado automáticamente por WordPress incluso para visitantes no logueados.

---

## 🤖 Abilities API (WP 7.0+ MCP integration)

El plugin registra 3 abilities via `Abilities\AbilityRegistrar` que exponen datos del evento a clientes MCP (Model Context Protocol) y a otros agentes IA. Se activan automáticamente si `wp_register_ability()` está disponible (WP 6.9+).

| Ability | Descripción | Output |
|---|---|---|
| `conacyta/get-site-info` | Información del evento: nombre, fechas, sede, organizador, URL inscripción | Objeto con `evento`, `edicion`, `acronimo`, `anio`, `inicio`, `fin`, `sede`, `ciudad`, `organizador`, `facultad`, `url_inscripcion` |
| `conacyta/list-ponentes` | Lista de ponentes magistrales | Array con `id`, `nombre`, `titulo`, `institucion`, `pais`, `enlace` |
| `conacyta/get-agenda` | Agenda filtrada por día, auditorio y tipo | Array con `id`, `titulo`, `hora_inicio`, `hora_fin`, `auditorios[]`, `tipos[]`, `ponente` |

Permisos: `__return_true` (públicas, sin auth, para consumo por agentes). Definidas con `input_schema` y `output_schema` JSON-Schema, y metadata `mcp: { public: true, type: "tool" }` para descubrimiento.

## 🌐 Internacionalización

- **Text domain**: `conacyta`
- **Archivo POT**: `languages/conacyta.pot`
- **Generación automática**: `npm run i18n:pot` — escanea `src/**/*.php` y `admin/**/*.js` extrayendo strings con `__()`, `_e()`, `esc_html__()`, etc.

El plugin incluye `.editorconfig` con reglas de encoding consistentes con el tema (`charset = utf-8` sin BOM, `end_of_line = lf`, indentación por tipo de archivo).

## 🔧 Requisitos

| Requisito | Versión |
|---|---|
| **WordPress** | 7.0 o superior |
| **PHP** | 8.3 o superior |
| **Tema** | Block Theme (FSE) — recomendado: **Conacyta Theme** |
| **Node.js** | 24 LTS (solo para desarrollo) |
| **pnpm / npm** | Para build de assets |

---

## 📦 Build & Release

```bash
# Desarrollo
npm run build          # Compilar paneles Gutenberg (webpack)
npm run i18n:pot       # Regenerar languages/conacyta.pot
composer dump-autoload # Regenerar autoload PSR-4

# Distribución
npm run build:zip      # → conacyta-core-1.0.2.zip (~80 KB, listo para produccion)
```

---

## 🤝 Contributing

1. Clona el repositorio en `wp-content/plugins/conacyta-core/`
2. Instala dependencias: `composer install && npm install`
3. Crea un branch: `git checkout -b feature/nueva-funcionalidad`
4. Asegúrate que el build compile: `npm run build`
5. PHP lint: `composer dump-autoload --optimize`
6. Commit y PR

---

## 📄 Licencia

GPLv2 or later. Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

## 📝 Changelog

### v1.0.2 (jun 2026) — Release actual

**Nuevas funcionalidades**

- **Abilities API (MCP)**: 3 abilities registradas vía `AbilityRegistrar` (`conacyta/get-site-info`, `conacyta/list-ponentes`, `conacyta/get-agenda`) para integración con clientes MCP y agentes IA externos (WP 6.9+ / 7.0+).
- **Endpoint historial de chat**: `GET /conacyta/v1/chat/history?session_id=...` permite re-hidratar conversaciones al recargar la página. Storage unificado en transient (TTL 1800s, max 20 mensajes).
- **DeepSeek V4 tool calls duales**: soporte para formato OpenAI estándar (`finish_reason: tool_calls`) y XML DSML nativo (`<调用 name="..."><参数 .../></调用>`) con extracción transparente via regex.
- **Script de internacionalización**: `npm run i18n:pot` genera `languages/conacyta.pot` desde `src/**/*.php` y `admin/**/*.js`.
- **`.editorconfig`**: reglas de encoding (UTF-8 sin BOM, LF, indentación por tipo) sincronizadas con el tema.
- **SVG upload support**: archivos `.svg` permitidos en el media library con sanitización de cabeceras XML para prevenir XSS.
- **Cache de contexto de Gemini**: el system prompt con datos de CPTs inyectados se cachea 5 min (`conacyta_context_prompt`) para reducir latencia.

**Correcciones**

- Defaults en `wp_options` con tildes y caracteres UTF-8 correctos (`Sullana, Perú`, `Facultad de Ingeniería de Industrias Alimentarias y Biotecnología`, `Convocatoria Próxima`, `Envío de Resúmenes`, etc.).
- Etiqueta `conacyta_evento_seccion_agenda` cambiada a `Programa` (default: `Programa Oficial del Congreso`).
- Rate limit por IP con headers `CF-Connecting-IP` / `X-Forwarded-For` para compatibilidad con proxies y CDNs.
- Gemini 3.5 Flash (Preview) agregado a los modelos disponibles.

### v1.0.1 (jun 2026)

- 8 CPTs con paneles Gutenberg y meta fields tipados.
- 4 taxonomías con capacidades por rol.
- 3 REST endpoints públicos (chat, contacto, agenda).
- Página de configuración con 3 pestañas y 5 sub-tabs en Evento.
- Chatbot IA dual: Gemini (Connectors API WP 7.0) y DeepSeek V4 (10 tools).
- 8 Block Variations generadas vía `VariationFactory`.
- Registro unificado de meta vía `MetaRegistrar` (register_post_meta + register_rest_field con type coercion).
- Helpers compartidos: `EventDateHelper`, `AbstractAiClient`, `Auth`.

### v1.0.0 (may 2026)

- Release inicial.

---

## 👤 Créditos

**Desarrollado por** [Leumin Omar Saavedra Peña (@nftsaavedra)](https://github.com/nftsaavedra)  
**Soporte tecnológico** · Unidad de Proyectos de Investigación  
**Universidad Nacional de Frontera** · Sullana, Perú

**XVII CONACYTA 2026** — Innovación, sostenibilidad y seguridad alimentaria para el futuro.
