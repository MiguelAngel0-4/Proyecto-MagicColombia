# MagicColombia — Proyecto (Versión Comentada)

Este paquete contiene el proyecto **MagicColombia** con **todos los HTML/CSS/JS/PHP** comentados didácticamente y **correcciones aplicadas** para navegación y UX. Ideal para exposición en clase.

---

## 🧭 Resumen de la estructura

```
cosmix/
├─ index.html
├─ html/
│  ├─ catalogo.html
│  ├─ tienda.html
│  ├─ producto.html
│  ├─ blog.html
│  ├─ pokemon.html
│  ├─ login.html
│  ├─ registro.html
│  └─ footer.html
├─ css/
│  └─ style.magiccolombia.css        ← versión comentada (mismo nombre)
├─ js/
│  ├─ jquery.min.js (vendor)
│  ├─ bootstrap.js  (vendor)
│  ├─ site.enhancements.js           ← versión comentada (mismo nombre)
│  ├─ hero.carousel.js               ← versión comentada (mismo nombre)
│  ├─ main.magiccolombia.js          ← versión comentada (mismo nombre)
│  ├─ ... (auth/login/register/tienda/catalogo/producto/posts/pokemon/... comentados)
└─ magic_auth_php/
   ├─ login.php, register.php, logout.php, ping.php, cuenta.php, ...
   └─ ... (**todos** comentados y con mismos nombres)
```

---

## 🚀 Puesta en marcha (XAMPP)

1. Copia la carpeta `cosmix/` a:  
   **Windows:** `C:\xampp\htdocs\cosmix`  
   **macOS/Linux:** `/Applications/XAMPP/htdocs/cosmix` o `/opt/lampp/htdocs/cosmix`
2. Inicia **Apache** (y **MySQL** si vas a probar login/registro real).
3. Abre en el navegador: `http://localhost/cosmix/index.html`

> **Importante:** si usas subcarpetas distintas a `/cosmix`, ajusta rutas absolutas en los HTML (por ejemplo, el `href` del logo de `/cosmix/index.html` → `/TU-CARPETA/index.html`).

---

## ✅ Correcciones clave ya aplicadas

- **Logo siempre vuelve al inicio:**  
  `href="/cosmix/index.html"` en el `<a class="brand">` de todas las páginas.
- **Preloader se oculta correctamente:**  
  Se carga el script real `src="/cosmix/js/site.enhancements.js"` (antes apuntaba a una ruta incorrecta).  
  Además, hay un *failsafe* que oculta `#preloader` al cargar el DOM.
- **CTAs de sesión coherentes:**  
  Los scripts usan `data-guest`, `data-user` y `data-logout` para mostrar/ocultar botones según `ping.php`.
- **CSS y JS comentados:**  
  Explican intención, entradas/salidas, eventos y utilidades sin alterar el diseño.

> Nota: En Apache, el archivo debe llamarse **`.htaccess`** (con punto). Si tienes uno llamado `htaccess`, renómbralo para que surta efecto.

---

## 🔑 Flujo de autenticación (resumen)

- **`magic_auth_php/ping.php`** devuelve algo como:  
  ```json
  { "ok": true, "usuario": { "id": 1, "nombre": "..." } }
  ```
- Los front-ends consultan `ping.php` y alternan visibilidad de:
  - `[data-guest]` → elementos **solo** cuando NO hay sesión
  - `[data-user]`  → elementos **solo** cuando SÍ hay sesión
  - `[data-logout]` → cierra sesión vía `POST` a `logout.php`

---

## 🔍 Rutas rápidas para demo

- Inicio: `http://localhost/cosmix/index.html`  
- Catálogo: `http://localhost/cosmix/html/catalogo.html`  
- Tienda: `http://localhost/cosmix/html/tienda.html`  
- Blog/Post: `http://localhost/cosmix/html/blog.html`  
- Login: `http://localhost/cosmix/html/login.html`  
- Registro: `http://localhost/cosmix/html/registro.html`

> Los bloques de **lanzamientos** y botones en **index** enlazan a `tienda.html` con **querystring** (`?q=...&cat=...`) para precargar filtros.

---

## 🧪 Checklist de verificación

- [ ] El logo te lleva al **inicio** desde cualquier página.  
- [ ] El **preloader** desaparece al cargar (Network muestra 200 para `/cosmix/js/site.enhancements.js`).  
- [ ] En modo **invitado** ves “Iniciar sesión / Registrarse”; en modo **loggeado** ves “Mi cuenta / Salir”.  
- [ ] El **carrusel** avanza y los **dots**/controles responden.  
- [ ] En tienda, las URLs con `?q=` filtran correctamente.

---

## 🧰 Solución de problemas

- **El preloader no se oculta:**  
  Verifica 404 en `/cosmix/js/site.enhancements.js`. Si hay error, corrige la ruta en el HTML.
- **No navega a Inicio con el logo:**  
  Revisa que el `href` sea `/cosmix/index.html` (o la ruta que uses).
- **PHP no responde:**  
  Confirma Apache+PHP activos y permisos de la carpeta. Revisa `magic_auth_php/test_db.php` si usas base de datos.
- **`.htaccess` no aplica:**  
  Asegúrate que el archivo se llame **`.htaccess`** y que `AllowOverride` esté habilitado en la config de Apache.

---

## ✍️ Sobre los comentarios en código

- **HTML**: cada bloque tiene “qué hace / de dónde trae info / por qué está así”.  
- **CSS**: encabezados por secciones, `@media` anotados, explicación de variables `:root`.  
- **JS**: IIFEs, eventos, `fetch`, selectores, toggles de clase, y funciones de carrusel.  
- **PHP**: `session_start`, `require`, conexión a BD, *prepared statements*, `password_hash/verify`, respuestas JSON.

---

## 📄 Licencia / Uso en clase

Puedes usar este paquete en tu curso/presentación. Si lo compartes, incluye esta nota para contexto de quienes lo reciban.

---

**Autor de notas y comentarios:** soporte asistido para la entrega de clase.
