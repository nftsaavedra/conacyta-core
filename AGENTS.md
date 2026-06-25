# Plugin: Conacyta Core

Plugin principal del sitio del XVII CONACYTA 2026. Expone la lógica de negocio (Custom Post Types, REST endpoints, settings, block variations) que el tema `conacyta/` consume.

## Prefijo y namespace

- **Prefijo de funciones/opciones/transients/metas**: `conacyta_core_`
- **Prefix de clases**: `ConacytaCore\` (namespace PSR-4, no usar snake_case `Conacyta_Core_`)
- **Namespace PSR-4**: `ConacytaCore\` mapea a `src/`
- **Prefijo de URLs REST**: `conacyta/v1`

## Referencias WP 7.0+ (fuente de verdad para APIs)

Para evitar obsolescencia, consultar SIEMPRE la documentacion oficial de nuevas funciones, clases y hooks introducidos en cada version de WordPress:

- **WP 7.0+ API Reference**: [https://developer.wordpress.org/reference/since/7.0.0/](https://developer.wordpress.org/reference/since/7.0.0/)
- **WP 7.0 Field Guide**: [https://make.wordpress.org/core/2026/05/14/wordpress-7-0-field-guide/](https://make.wordpress.org/core/2026/05/14/wordpress-7-0-field-guide/)
- **Abilities API**: `wp_register_ability()` (WP 6.9+), `wp_register_ability_category()` — [docs](https://developer.wordpress.org/reference/functions/wp_register_ability/)
- **Connectors API**: WP 7.0+ — usado para Gemini (Google AI)

> Nota: La URL `https://developer.wordpress.org/reference/since/7.0.0/` lista TODAS las funciones, clases y hooks nuevos o modificados en WP 7.0+. Es la fuente canónica para verificar compatibilidad.

## Composer (autoload PSR-4)

```json
{
  "name": "conacyta/conacyta-core",
  "description": "Plugin core para el XVII CONACYTA 2026",
  "type": "wordpress-plugin",
  "license": "GPL-2.0-or-later",
  "require": {
    "php": ">=8.3"
  },
  "autoload": {
    "psr-4": {
      "ConacytaCore\\": "src/"
    }
  },
  "config": {
    "optimize-autoloader": true
  }
}
```

## Estructura de carpetas (Feature-Driven)

```
conacyta-core/
├── AGENTS.md
├── conacyta-core.php
├── composer.json
├── uninstall.php
├── package.json
├── src/
│   ├── admin/
│   │   ├── index.js          ← Entry único: imports de panels/ (8 CPTs)
│   │   └── panels/           ← Feature-driven por CPT (utils + 8 paneles)
│   ├── Abilities/
│   │   └── AbilityRegistrar.php   ← Registro de habilidades MCP/WP-CLI
│   ├── Core/
│   │   ├── Plugin.php
│   │   ├── Activator.php
│   │   ├── Deactivator.php
│   │   └── Assets.php
│   ├── Shared/
│   │   ├── Auth.php
│   │   ├── Sanitizer.php
│   │   ├── MetaRegistrar.php         ← Registro de meta vía register_rest_field()
│   │   ├── EventDateHelper.php       ← Helper de fechas del evento
│   │   └── VariationFactory.php      ← Factory de block variations
│   ├── Ponente/
│   │   └── PonenteCPT.php
│   ├── Actividad/
│   │   └── ActividadCPT.php
│   ├── Agenda/
│   │   ├── AgendaCPT.php
│   │   ├── AgendaVariation.php       ← Única variación individual (dinámica por día)
│   │   ├── AgendaMigrator.php
│   │   ├── AgendaSaveHandler.php
│   │   ├── AgendaRestController.php
│   │   ├── AgendaQueryFilter.php
│   │   ├── AgendaTipoTaxonomy.php
│   │   ├── AgendaSeeder.php
│   │   ├── AgendaValidator.php
│   │   └── AuditorioTaxonomy.php
│   ├── Tarifa/
│   │   ├── TarifaCPT.php
│   │   └── BeneficioTarifaTaxonomy.php
│   ├── Comite/
│   │   └── ComiteCPT.php
│   ├── Partner/
│   │   └── PartnerCPT.php
│   ├── Portada/
│   │   ├── PortadaCPT.php
│   │   └── PortadaQueryFilter.php
│   ├── Cronograma/
│   │   └── CronogramaCPT.php
│   ├── AreaTematica/
│   │   ├── AreaTematicaTaxonomy.php
│   │   └── AreaTematicaQueryFilter.php
│   ├── Chatbot/
│   │   ├── AbstractAiClient.php      ← Base compartida: apiCall, retry, history
│   │   ├── GeminiClient.php          ← Gemini (Connectors API WP 7.0)
│   │   ├── DeepSeekClient.php        ← DeepSeek V4 (function calling)
│   │   ├── ChatbotRestController.php
│   │   └── ChatbotSettings.php
│   ├── Contacto/
│   │   ├── ContactoRestController.php
│   │   └── ContactoSettings.php
│   └── Settings/
│       ├── SettingsPage.php
│       └── EventoSettings.php
├── admin/
│   ├── admin.js               ← Vanilla JS: modal confirmacion + sync año (NO compilado)
│   ├── css/
│   │   └── editor.css
│   ├── js/
│   │   ├── index.js           ← Bundle compilado: paneles Gutenberg
│   │   └── index.asset.php    ← Auto-generado por wp-scripts
│   ├── colors.json
│   └── icons.json
├── scripts/
│   └── zip-dist.mjs           ← Script de build:zip
├── dist/                        ← Output de build:zip
└── languages/
    └── conacyta.pot
```

## Build System

El plugin tiene **un solo entry** compilado con `@wordpress/scripts`:

```bash
npm run build          # compila src/admin/index.js → admin/js/index.js
npm run build:zip      # build + genera dist/conacyta-core-{version}.zip
```

### Entry

| Entry fuente | Output | Propósito | Carga en |
|---|---|---|---|
| `src/admin/index.js` | `admin/js/index.js` | Paneles Gutenberg (8 CPTs) | Pantallas de edición de CPT |

### Script independiente (NO compilado)

| Archivo | Propósito | Carga en |
|---|---|---|
| `admin/admin.js` | Modal confirmación de cambio de fechas + sync año | Settings > Evento |

**Regla crítica**: `admin/admin.js` es un archivo independiente que jamás debe ser eliminado ni compilado. No es parte de ningún entry de webpack.

### Build y verificación

- **SIEMPRE** ejecutar `npm run build` después de modificar cualquier archivo en `src/admin/`.
- **SIEMPRE** ejecutar `composer dump-autoload --optimize` después de modificar archivos PHP.
- **SIEMPRE** verificar que el build compila sin errores antes de commit.
- **NUNCA** hacer commit si `npm run build` muestra errores.

### Commits y sincronización Git

**Reglas de formato del mensaje de commit**:

- **Máximo 50 caracteres** en la línea de asunto.
- Formato: `tipo: descripción` (ej: `fix: seed icon format for actividad`, `refactor: extract MetaRegistrar`).
- Tipos comunes: `fix`, `feat`, `refactor`, `chore`, `docs`, `cleanup`.
- Sin listas, sin explicaciones largas, sin puntos suspensivos.

**Cuándo hacer commit**:

- **SOLO cuando el usuario lo solicita explícitamente** (ej: "haz commit", "sincroniza", "push").
- NUNCA hacer commit por iniciativa propia.

**Pre-commit checks obligatorios**:

1. `npm run build` debe compilar sin errores.
2. `composer dump-autoload --optimize` ejecutado si se modificaron archivos PHP.
3. PHP lint en todos los archivos modificados (`php -l`).
4. Verificar `git status` — no incluir archivos innecesarios.
5. Verificar `git diff` — no incluir API keys, credenciales, ni secrets.
6. Verificar que `admin/admin.js` y `src/admin/index.js` NO aparezcan como `deleted`.
7. El ZIP de distribución (`dist/*.zip`) no debe comitearse (está en `.gitignore`).

**Sincronización con repositorio remoto**:

Cuando el usuario pida commit + push ("sincroniza", "sube los cambios", "haz commit y push"):

1. Ejecutar pre-commit checks.
2. `git add .` en el directorio del plugin.
3. `git commit -m "tipo: descripción"` con mensaje corto.
4. `git push origin main`.
5. Reportar solo: hash del commit, rama, remote, y cantidad de archivos.

**Archivos que jamás deben ser eliminados**:

- `admin/admin.js` — script vanilla para modal de confirmación y sync año.
- `src/admin/index.js` — entry de paneles Gutenberg. Modificar solo para agregar/quitar paneles.
- Si un commit muestra `delete mode` en cualquiera de estos archivos, REVERTIR inmediatamente.

## Responsabilidades del plugin

### 1. Custom Post Types (CPTs)

Registrar en `init` con `show_in_rest => true`. Cada CPT usa `MetaRegistrar::forPostType()` que registra `register_post_meta()` + `register_rest_field()` con type coercion automática.

| CPT | slug | Feature dir | Meta keys |
|---|---|---|---|
| Ponente | `ponente` | `src/Ponente/` | `conacyta_core_ponente_titulo` (s), `_institucion` (s), `_pais` (s), `_bandera_id` (i) |
| Actividad | `actividad` | `src/Actividad/` | `conacyta_core_actividad_icono` (s), `_color_tailwind` (s) |
| Item Agenda | `agenda_item` | `src/Agenda/` | `conacyta_core_agenda_dia` (i), `_hora_inicio` (s), `_hora_fin` (s), `_ponente_id` (i), `_color_dot` (s), `_duracion_minutos` (i), `_orden` (i) |
| Tarifa | `tarifa` | `src/Tarifa/` | `conacyta_core_tarifa_precio` (n), `_moneda` (s), `_destacada` (b), `_etiqueta` (s), `_boton_texto` (s), `_url_inscripcion` (url) |
| Miembro Comité | `comite_member` | `src/Comite/` | `conacyta_core_comite_rol` (s) |
| Partner | `partner` | `src/Partner/` | `conacyta_core_partner_tipo` (s), `_url` (url) |
| Portada | `portada` | `src/Portada/` | `conacyta_core_portada_principal` (b), `_tagline` (s), `_cta_texto` (s), `_cta_url` (s), `_cta2_texto` (s), `_cta2_url` (s) |
| Cronograma | `cronograma_fase` | `src/Cronograma/` | `conacyta_core_fase_fecha_inicio` (s), `_fecha_fin` (s), `_destacada` (b) |

> Tipos: (s)=string, (b)=boolean, (i)=integer, (n)=number, (url)=url

### 2. Taxonomías

- `area_tematica` (jerárquica, asignada a `ponente`). 11 términos seed.
- `conacyta_auditorio` (jerárquica, asignada a `agenda_item`).
- `conacyta_agenda_tipo` (jerárquica, asignada a `agenda_item`).
- `beneficio_tarifa` (no jerárquica, tags, asignada a `tarifa`).

### 3. Block Variations

Las variaciones de `core/query` se generan via `VariationFactory::make()` desde `Plugin::registerBlockVariations()`. Esto reemplaza a las clases individuales `*Variation.php` (eliminadas en v1.1.0).

```php
VariationFactory::make('conacyta/ponentes-grid', 'Grid de Ponentes', '...', 'id', 'ponente'),
```

**Excepción**: `AgendaVariation` mantiene su clase propia porque genera variaciones dinámicas por día según el rango de fechas del evento.

### 4. REST API

| Endpoint | Método | Handler | Auth |
|---|---|---|---|
| `POST /conacyta/v1/chat` | POST | `ChatbotRestController` | `Auth::publicChatAccess()` (nonce + rate limit) |
| `POST /conacyta/v1/contacto` | POST | `ContactoRestController` | `Auth::publicChatAccess()` |
| `GET /conacyta/v1/agenda` | GET | `AgendaRestController` | `__return_true` (datos públicos con caching de 5 min) |

### 5. Settings

La página `Settings > Conacyta` agrupa tres pestañas:

**Chatbot** (`ChatbotSettings.php`):
- Provider: Gemini (Connectors API WP 7.0) o DeepSeek V4 (API key propia)
- Los modelos disponibles se definen en `ChatbotSettings.php` — consultar ese archivo para la lista vigente
- Rate limit, system prompt, textos de UI

**Contacto** (`ContactoSettings.php`):
- `conacyta_core_contacto_email`, `_whatsapp`, `_facebook_url`, `_instagram_url`, `_linkedin_url`

**Evento** (`EventoSettings.php`) — sub-tabs:
- **Identidad**: edición, acrónimo, fechas, URL inscripción, año (auto), cleanup on uninstall
- **Sede**: sede, ciudad, organizador, facultad
- **Countdown**: título, badges, mensajes, CTA
- **Sobre**: título, descripción, imágenes (×3 con media upload)
- **Secciones**: títulos de ponentes, actividades, agenda, tarifas, comité, partners, ejes

Los campos de fecha usan `<input type="date">` nativo HTML5 (no `DateTimePicker`). Ver `wp-content/AGENTS.md` para la regla completa.

### 6. Paneles Gutenberg (Block Editor)

Los meta fields de cada CPT se editan vía paneles `PluginDocumentSettingPanel` en la barra lateral del editor. Implementados en `src/admin/index.js` → `admin/js/index.js`.

### 7. Chatbot — Arquitectura de IA

```
AbstractAiClient          ← apiCall(), isRetryable(), saveToHistory(), getHistory()
├── GeminiClient          ← Gemini API (contexto CPTs inyectado en system prompt)
└── DeepSeekClient        ← DeepSeek V4 (function calling con 10 tools)
```

**DeepSeek V4 tools**: `get_evento`, `get_tarifas`, `get_ponentes`, `get_agenda`, `get_comite`, `get_cronograma`, `get_actividades`, `get_partners`, `buscar_informacion` (WP_Query), `get_conocimiento_general`.

### 8. Registro de Meta

El plugin usa `Shared\MetaRegistrar::forPostType()` como helper unificado que reemplaza el `register_post_meta()` + `register_rest_field()` manual. Ver `wp-content/AGENTS.md` para el patrón de `register_rest_field()` y por qué es necesario en WP 7.0+.

```php
use ConacytaCore\Shared\MetaRegistrar;

MetaRegistrar::forPostType('ponente', [
    'conacyta_core_ponente_titulo'      => 'string',
    'conacyta_core_ponente_bandera_id'  => 'integer',
]);

// Con validación extra:
MetaRegistrar::forPostType('agenda_item', [...], function (int $postId, string $key, mixed $value): mixed {
    if ($key === 'conacyta_core_agenda_dia' && (int) $value < 1) return 1;
    return $value;
});
```

## Hooks del plugin

```php
add_action('plugins_loaded', static function (): void {
    ConacytaCore\Core\Plugin::getInstance()->register();
});
```

`register()` engancha:
- `init` → CPTs, taxonomías, block variations
- `rest_api_init` → REST endpoints
- `admin_menu` + `admin_init` → settings
- `wp_enqueue_scripts` → `conacyta-core-frontend` (localiza `window.conacytaData`)
- `enqueue_block_editor_assets` → paneles Gutenberg (`admin/js/index.js`)
- `admin_enqueue_scripts` → settings scripts (`admin/admin.js`)

## Convenciones de código

- **PHP 8.3+**: `readonly` properties, enums, `match`, named args, tipos union.
- **`declare(strict_types=1);`** en cada archivo PHP de producción.
- **No usar** `extract()`, `$$var`, `eval`.
- **Internacionalización**: `__('texto', 'conacyta')`, `_e()`, `esc_html__()`, etc.

## Llamada desde el Frontend (WP 7.0+)

El plugin expone `window.conacytaData` en el frontend vía el handle `conacyta-core-frontend`:

```js
const { chatEndpoint, nonce } = window.conacytaData;

const res = await fetch(chatEndpoint, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
  body: JSON.stringify({ message: '¿Cuáles son las tarifas?', session_id: sessionId }),
});
const { reply, suggestions } = await res.json();
```

### Proveedores de IA

| Proveedor | API Key | Tool Calls | Modelo |
|---|---|---|---|
| Gemini (Google) | Connectors API WP 7.0 | ❌ Flash-Lite no soporta | Configurable en `ChatbotSettings.php` |
| DeepSeek | API key propia | ✅ OpenAI-compatible | Configurable en `ChatbotSettings.php` |

El provider y modelo se seleccionan en Settings > Conacyta > Chatbot. Los modelos vigentes están definidos en `ChatbotSettings.php`.

## Documentacion para temas

El archivo **[`docs/DATA-CONTRACT.md`](../../docs/DATA-CONTRACT.md)** (raíz del workspace) contiene la referencia COMPLETA de todos los datos que el plugin expone para consumo de temas. Incluye:

- **3 REST endpoints** con request/response shapes, errores, y auth
- **8 CPTs** con meta keys exactas, tipos, y type coercion en REST
- **4 taxonomias** con slugs, rewrite rules, y terminos seed
- **~50 option keys** agrupadas por seccion (Identidad, Sede, Countdown, Sobre, Secciones, Contacto, Chatbot)
- **`window.conacytaData`** shape JS (5 propiedades)
- **9 block variation namespaces** con perPage, orderBy, order, y CPT asociado
- **Relacion CPT-CPT**: `agenda_item` → `ponente` via `conacyta_core_agenda_ponente_id`
- **Transients y cache**: TTLs, keys, y triggers de invalidacion
- **Estructura de menu admin**: sub-menus, capabilities, y slugs

Cualquier tema que quiera consumir datos de este plugin debe alinearse 100% a este contrato. No hardcodear nombres de meta keys, URLs de endpoints, ni opciones de settings — consultar siempre `docs/DATA-CONTRACT.md` como fuente unica de verdad.

## No hacer

- ❌ No hardcodear API keys en JS. Usar Connectors API o `wp_options` server-side.
- ❌ No asumir nombres de modelo de IA — consultar siempre `ChatbotSettings.php` para los modelos soportados.
- ❌ No modificar `src/admin/index.js` para resolver problemas de Settings.
- ❌ Si se modifican CPTs, meta keys, taxonomías, endpoints REST, opciones de settings, o cualquier estructura de datos que el tema consume, se DEBE actualizar `docs/DATA-CONTRACT.md` para reflejar los cambios.
- Para reglas generales de WordPress (jQuery, Classic themes, Tailwind CDN, `query_posts`, etc.), ver `wp-content/AGENTS.md`.
