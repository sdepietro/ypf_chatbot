# TEMPLATE.md - Guía Técnica del Template Admin Panel

Este documento describe la estructura técnica del template del panel de administración, para facilitar la adaptación de otros themes HTML preexistentes.

## Estructura de Directorios

```
resources/views/admin_panel/
├── layouts/
│   ├── master.blade.php              # Layout principal (HTML base)
│   └── partials/
│       ├── head-css.blade.php        # CSS del <head>
│       ├── topbar.blade.php          # Barra superior (header)
│       ├── sidebar.blade.php         # Menú lateral
│       ├── footer.blade.php          # Pie de página
│       ├── vendor-scripts.blade.php  # Scripts adicionales (no usado actualmente)
│       ├── page-title.blade.php      # Componente de título de página
│       ├── title-meta.blade.php      # Meta tags del título
│       ├── animated_efect.blade.php  # Efectos CSS animados
│       ├── map_style.blade.php       # Estilos para mapas
│       ├── travel_map_core.blade.php # Core JS de mapas
│       └── inputs/                   # Componentes de formulario reutilizables
│           ├── text.blade.php
│           ├── select.blade.php
│           ├── file.blade.php
│           ├── icon.blade.php
│           └── colorpicker.blade.php
├── includes/
│   └── map_travel.blade.php          # Include para mapas de viaje
└── pages/                            # Páginas del panel
    ├── auth/                         # Login, signup, password, lock-screen
    ├── dashboard/                    # index_admin, index_user, index_supervisor
    ├── users/                        # CRUD de usuarios
   
```

## Assets del Template

### Ubicación: `public/assets/`

```

```

## Archivos del Layout

### master.blade.php

**Propósito**: Layout principal que envuelve todas las páginas del admin panel.

**Responsabilidades**:
- Define la estructura HTML base (doctype, html, head, body)
- Incluye meta tags básicos (charset, viewport, author, robots, theme-color)
- Carga el partial `head-css` para los estilos
- Carga jQuery y `config.js` en el head
- Define el contenedor principal del layout (wrapper)
- Incluye los partials de topbar, sidebar y footer en sus posiciones correspondientes
- Define el área de contenido principal donde se renderiza cada página
- Muestra mensajes flash de sesión (success y custom-error) automáticamente
- Carga los scripts base del template (vendor.min.js, app.js)
- Carga scripts adicionales como jsvectormap y dashboard.js
- Itera sobre los plugins de `config/template.php` para cargar JS dinámicamente
- Renderiza el stack `scripts` para JS adicional de cada página

**Secciones Blade disponibles**:
- `@yield('title')` - Título de la página en el tag `<title>`
- `@yield('content')` - Contenido principal de cada página
- `@stack('scripts')` - Scripts adicionales que cada página puede agregar con `@push('scripts')`

---

### head-css.blade.php

**Propósito**: Centraliza la carga de todos los archivos CSS.

**Responsabilidades**:
- Incluye el favicon
- Carga fuentes de Google Fonts (actualmente usa "Play")
- Carga los 3 CSS base del template en orden:
  1. `vendor.min.css` - Bootstrap 5 y dependencias
  2. `icons.min.css` - Set de iconos del template
  3. `style.min.css` - Estilos visuales del template
- Itera sobre los plugins de `config/template.php` y carga dinámicamente los archivos CSS marcados como activos
- Distingue entre assets locales (`asset => true`) y URLs externas (`asset => false`)

---

### topbar.blade.php

**Propósito**: Renderiza la barra superior del panel.

**Responsabilidades**:
- Contiene el botón hamburguesa para toggle del sidebar en mobile
- Muestra controles de usuario (puede incluir theme switcher, notificaciones)
- Renderiza el dropdown del usuario con avatar, opciones de cuenta y logout
- Usa iconos de Iconify para los elementos visuales
- El logout apunta a la ruta `logout`

---

### sidebar.blade.php

**Propósito**: Renderiza el menú de navegación lateral.

**Responsabilidades**:
- Muestra el logo del sitio (versiones dark y light, tamaños sm y lg)
- Usa SimpleBar para scroll personalizado en el menú
- Itera sobre `config('template.template_menu')` para generar los items del menú
- Implementa lógica de permisos: cada item puede tener `can` como string o array de permisos
- Filtra items y subitems según permisos del usuario autenticado
- Soporta menús con submenús colapsables (usa Bootstrap collapse)
- Genera IDs únicos para los collapse basados en el texto del item
- Incluye el partial `animated_efect` al final

**Estructura de datos del menú** (ver sección de Configuración más abajo)

---

### footer.blade.php

**Propósito**: Renderiza el pie de página.

**Responsabilidades**:
- Muestra el año actual (generado con JS)
- Muestra el nombre de la aplicación y créditos
- Contenido simple y estático

---

### vendor-scripts.blade.php

**Propósito**: Carga scripts adicionales via Vite.

**Responsabilidades**:
- Define secciones `script-bottom` y `scripts` (yields)
- Carga `resources/js/app.js` via Vite
- Actualmente no se usa en master.blade.php (los scripts se cargan directamente)

---

### Partials de inputs (inputs/*.blade.php)

**Propósito**: Componentes reutilizables de formulario.

**Archivos**:
- `text.blade.php` - Campo de texto
- `select.blade.php` - Select/dropdown
- `file.blade.php` - Input de archivo
- `icon.blade.php` - Selector de icono
- `colorpicker.blade.php` - Selector de color

**Uso**: Se incluyen con `@include('admin_panel.layouts.partials.inputs.text', [...])` pasando parámetros.

---

## Configuración de Plugins (config/template.php)

### Estructura del archivo

El archivo `config/template.php` contiene:
- `title` - Título de la aplicación
- `template_menu` - Array con la estructura del menú de navegación
- `plugins` - Array de plugins externos a cargar

### Configuración del Menú

Cada item del menú es un array con:
- `text` (string) - Texto visible del item
- `url` (string) - Nombre de ruta Laravel (ej: `admin::users_index`)
- `can` (string|array|null) - Permiso(s) requerido(s). Si es array, pasa si tiene cualquiera. Si es null/vacío, siempre visible
- `icon` (string) - Clase CSS del icono (FontAwesome, RemixIcon, etc.)
- `submenu` (array, opcional) - Array de subitems con la misma estructura

### Configuración de Plugins

Cada plugin es un array con:
- `active` (bool) - Si está activo o no
- `files` (array) - Lista de archivos a cargar, cada uno con:
  - `type` (string) - `css` o `js`
  - `asset` (bool) - `true` para archivos locales en `public/assets/`, `false` para URLs externas
  - `location` (string) - Path del archivo o URL completa

Los plugins se cargan automáticamente en `head-css.blade.php` (CSS) y `master.blade.php` (JS).

---

## Librerías Incluidas

### En vendor.min.js/css
- Bootstrap 5
- SimpleBar (scrollbar personalizado)
- Iconify (iconos dinámicos)

### Plugins Externos Configurados
- FontAwesome 7.0 - Iconos
- RemixIcon 4.6 - Iconos adicionales
- Bootstrap Icons 1.11 - Más iconos
- Toastr - Notificaciones toast
- Flatpickr - Date/time picker
- Tippy.js + Popper - Tooltips
- Moment.js + Timezone - Manejo de fechas
- Bootstrap Colorpicker - Selector de color

---

## Guía para Adaptar un Theme Nuevo

### Paso 1: Identificar Archivos del Theme

Del theme nuevo, localizar:
- CSS principal (estilos del template) → reemplazará `style.min.css`
- CSS de vendor/bootstrap → reemplazará `vendor.min.css`
- CSS de iconos → reemplazará `icons.min.css`
- JS principal (lógica del template) → reemplazará `app.js`
- JS de vendor → reemplazará `vendor.min.js`
- JS de configuración (si existe) → reemplazará `config.js`

### Paso 2: Copiar Assets

1. Copiar los archivos CSS a `public/assets/css/`
2. Copiar los archivos JS a `public/assets/js/`
3. Copiar imágenes a `public/assets/images/`
4. Copiar fuentes a `public/assets/fonts/`
5. Copiar vendors adicionales a `public/assets/vendor/`

### Paso 3: Adaptar master.blade.php

1. Copiar la estructura HTML base del theme (doctype hasta body)
2. Mantener las directivas Blade existentes:
   - `@yield('title')` en el title
   - `@include('admin_panel.layouts.partials.head-css')` en el head
   - Los includes de topbar, sidebar, footer en sus posiciones
   - `@yield('content')` donde va el contenido principal
   - La lógica de flash messages
   - El loop de plugins para JS
   - `@stack('scripts')` al final

### Paso 4: Adaptar head-css.blade.php

1. Actualizar las rutas de los CSS base según los nuevos archivos
2. Mantener el loop de plugins sin cambios
3. Agregar fuentes de Google Fonts que use el theme

### Paso 5: Adaptar topbar.blade.php

1. Copiar el HTML del header/topbar del theme
2. Mantener el botón de toggle del sidebar (adaptar clase/atributo)
3. Mantener el dropdown de usuario con logout apuntando a `route('logout')`
4. Adaptar iconos según la librería que use el theme

### Paso 6: Adaptar sidebar.blade.php

1. Copiar la estructura HTML del sidebar del theme
2. Mantener la lógica PHP de permisos (`$canPass`)
3. Adaptar el loop `@foreach(config('template.template_menu'))` al markup del nuevo theme
4. Identificar las clases CSS equivalentes para:
   - Contenedor del sidebar
   - Items de navegación
   - Links de navegación
   - Indicador de submenú (arrow)
   - Contenedor de submenú colapsable
   - Subitems
5. Mantener el uso de Bootstrap collapse para submenús o adaptar al sistema del theme

### Paso 7: Adaptar footer.blade.php

1. Copiar el HTML del footer del theme
2. Mantener el script de año dinámico
3. Actualizar textos de copyright

### Paso 8: Actualizar config/template.php

1. Revisar plugins que usa el theme y agregarlos/quitarlos
2. Actualizar paths de plugins locales si cambiaron

### Paso 9: Verificar Páginas

1. Revisar que las páginas existentes se vean correctamente
2. Adaptar clases CSS en las páginas si el theme usa clases diferentes para:
   - Cards
   - Tablas
   - Botones
   - Formularios
   - Alertas
   - Breadcrumbs

---

## Notas Importantes

1. **jQuery**: Se carga desde CDN en el head, antes de cualquier otro script
2. **config.js**: Se carga antes de app.js para inicializar configuración del theme
3. **Orden de CSS**: vendor → icons → style (respetar orden para cascade correcto)
4. **Permisos**: El sidebar filtra automáticamente según permisos del usuario
5. **Rutas**: Las rutas del admin usan prefijo `admin::` (definido en routes/web.php)
6. **Flash Messages**: Se muestran automáticamente en master.blade.php (session success/custom-error)
7. **Responsive**: El template debe ser responsive, el toggle de sidebar es crítico para mobile
