# Tecnolo CRM — CRM para inmobiliarias

CRM completo para una agencia inmobiliaria: gestión de inventario, captación y
seguimiento de leads, agenda de visitas, contratos con liquidación de comisiones,
y un portal público que alimenta el embudo automáticamente.

**Stack:** Laravel 13 · Filament 5 · Livewire 4 · Tailwind 4 · SQLite (cambiable a MySQL/Postgres)

---

## Puesta en marcha

```bash
composer install && npm install && npm run build
```

```bash
cp .env.example .env && php artisan key:generate && php artisan storage:link
```

```bash
php artisan migrate --seed && php artisan serve
```

- **CRM:** http://localhost:8000/admin
- **Portal público:** http://localhost:8000

> Ajusta `APP_URL` en `.env` al host y **puerto** reales. Si no coincide, las
> imágenes servidas desde `storage` apuntarán a una URL equivocada.

### Usuarios de demostración

Todos con contraseña `password`:

| Email | Rol | Ve |
|---|---|---|
| `admin@tecnolocrm.test` | Administrador | Todo + usuarios y configuración |
| `gerencia@tecnolocrm.test` | Gerente | Todos los registros y reportes |
| `andres@tecnolocrm.test` | Agente | Solo sus propios registros |
| `valeria@tecnolocrm.test` | Agente | Solo sus propios registros |
| `jorge@tecnolocrm.test` | Agente | Solo sus propios registros |

Cambia estas credenciales antes de cualquier despliegue real.

---

## Módulos

### Comercial
- **Embudo** (`/admin/pipeline`) — tablero kanban con arrastrar y soltar entre
  etapas; al soltar se actualiza la etapa, la probabilidad y el historial.
- **Leads** — embudo de 8 etapas, valor ponderado, detección de leads estancados
  (+15 días), reasignación masiva de agente, bitácora de actividades y tareas.
- **Contactos** — propietarios, compradores, arrendatarios e inversores, con
  preferencias de búsqueda que se cruzan contra el inventario disponible.
- **Visitas** — agenda por agente y registro de resultado con nivel de interés,
  que queda anotado en la ficha del lead.
- **Contratos** — reserva, venta y alquiler. Al activarse: el inmueble cambia de
  estado, el lead se marca como ganado y se generan las comisiones.
- **Comisiones** — reparto 50/50 entre captador y agente que cierra, con flujo
  pendiente → aprobada → pagada y liquidación masiva.

### Inventario
- **Propiedades** — ficha por pestañas (general, características, ubicación,
  fotos, gestión), galería reordenable, amenidades, referencia `PROP-0001`
  correlativa y control de publicación en el portal.
- **Amenidades** — catálogo editable.

### Administración
- **Usuarios** — roles, comisión por defecto, activación.

### Ajustes → Portal público
Módulo solo para administradores que controla el frontend sin tocar código,
en seis pestañas:

| Pestaña | Qué controla |
|---|---|
| Marca | Nombre, eslogan, logo, favicon y **color principal** de toda la web |
| Portada | Titular, subtítulo, imagen de fondo, y si se muestran buscador y contadores |
| Secciones | Encender/apagar destacadas, zonas y bloque de contacto; sus títulos, cuántas destacadas mostrar y la lista de ventajas |
| Contacto | Teléfono, email, dirección, horario, redes sociales y botón flotante de WhatsApp con mensaje predefinido |
| SEO | Meta título y descripción, imagen al compartir e ID de Google Analytics |
| Avanzado | Modo mantenimiento del portal y nota del pie |

El color principal viaja al portal como variables CSS (`--brand`, `--brand-dark`,
`--brand-tint`, `--brand-contrast`) porque Tailwind compila sus clases en build y
no puede generar un color elegido en tiempo de ejecución. `App\Support\Brand`
deriva las variantes y elige texto blanco o negro según el contraste.

En mantenimiento los visitantes reciben un 503 con el mensaje configurado,
mientras que el administrador sigue viendo el portal con un aviso en la cabecera.

### Portal público
Home con buscador y destacadas, listado con filtros (operación, tipo, ciudad,
habitaciones, rango de precio, orden) y ficha de inmueble con galería, mapa y
formulario de contacto. Cada consulta crea contacto + lead, lo asigna al agente
captador del inmueble y registra la actividad.

---

## Reglas de negocio

| Regla | Dónde vive |
|---|---|
| Comisión de venta/reserva = importe × % | `Contract::commissionBase()` |
| Comisión de alquiler = renta **anual** × % | `Contract::commissionBase()` |
| Contrato activo → inmueble vendido/alquilado/reservado | `Contract::syncPropertyStatus()` |
| Contrato activo → lead ganado | `Contract::markLeadWon()` |
| Reparto de comisión captador/cierre | `Contract::generateCommissions()` |
| Todo cambio de etapa queda registrado | `Lead::booted()` |
| Probabilidad sugerida por etapa | `LeadStage::defaultProbability()` |
| Marcar «perdido» exige motivo | `LeadForm` y `Pipeline::moveLead()` |
| Un agente solo ve sus registros | `getEloquentQuery()` de cada Resource |
| Ajustes: booleanos, listas y archivos sobre una tabla clave/valor | `Setting::bool()`, `list()`, `url()` |
| Portal cerrado salvo para el administrador | `EnsurePortalIsEnabled` |

Los estados son enums PHP (`app/Enums`) que implementan las interfaces de
Filament, así que etiqueta, color e icono viven junto al dominio y no se
duplican en la UI.

---

## Tests

```bash
php artisan test
```

Cubren el renderizado de todas las pantallas del panel y del portal, la
captación de leads (incluido el anti-spam y la deduplicación de contactos), el
cálculo de comisiones, la activación de contratos y el aislamiento por rol.

---

## Notas para producción

- Cambiar a MySQL o PostgreSQL en `.env` (el esquema es portable; los enums se
  guardan como texto).
- Configurar el driver de correo para notificar a los agentes de leads nuevos.
- Revisar `config/filesystems.php` si las fotos van a S3 en lugar de local.
- Las fotos de demostración son SVG generados por el seeder; sustitúyelas por
  fotografías reales antes de publicar.
