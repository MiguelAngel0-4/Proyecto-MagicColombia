/* /cosmix/js/site.enhancements.js
   ============================================================
   Utilidades de UI + Estado de autenticación unificado
   MagicColombia - 2025
   ============================================================ */

(() => {
  const API_BASE = '/cosmix/magic_auth_php';

  // ----------------------------------------------------------
  // Helpers
  // ----------------------------------------------------------
  const $  = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

  const show = el => el && (el.hidden = false, el.style.display = '');
  const hide = el => el && (el.hidden = true,  el.style.display = 'none');

  async function fetchJSON(url, opts = {}) {
    const res = await fetch(url, {
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      ...opts
    });
    let data = null;
    try { data = await res.json(); } catch(_) {}
    return { ok: res.ok, status: res.status, data };
  }

  // ----------------------------------------------------------
  // Auth: ping() y logout()
  // ----------------------------------------------------------
  async function getSession() {
    // Respuesta esperada: { ok:true, user:{ id, nombre, role }, ...}
    const { ok, data } = await fetchJSON(`${API_BASE}/ping.php`, { method: 'GET' });
    if (ok && data && data.ok && data.user) return data.user;
    return null;
  }

  async function logout() {
    // Respuesta esperada: { ok:true }
    await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' });
    // Limpieza visual mínima y refresh suave
    try {
      // Por si hay algún indicador en pantalla
      const menu = $('#userNav .user-menu');
      if (menu) menu.remove();
    } catch (_) {}
    location.reload();
  }

  // ----------------------------------------------------------
  // Render del menú de usuario (avatar + dropdown)
  // ----------------------------------------------------------
  function renderUserNav(user) {
    // Contenedor opcional (lo tienes en el header de varias páginas)
    let host = $('#userNav');
    if (!host) {
      // Si no existe, creamos uno al vuelo al inicio del header
      const header = $('.site-header .nav') || $('.site-header');
      if (!header) return;
      host = document.createElement('div');
      host.id = 'userNav';
      header.prepend(host);
    }

    // Limpia
    host.innerHTML = '';

    if (!user) return;

    const first = (user.nombre || user.email || 'U').trim()[0]?.toUpperCase() || 'U';
    const role  = (user.role || 'user').toLowerCase();

    const wrapper = document.createElement('div');
    wrapper.className = 'user-menu';
    wrapper.style.position = 'relative';

    wrapper.innerHTML = `
      <button id="userBtn" class="btn-avatar" aria-haspopup="true" aria-expanded="false"
              title="${user.nombre || 'Mi cuenta'}">
        <span class="avatar">${first}</span>
      </button>

      <div id="userDropdown" class="dropdown" role="menu" aria-label="Menú de usuario">
        <div class="user-block">
          <div class="avatar sm">${first}</div>
          <div class="meta">
            <strong>${escapeHtml(user.nombre || 'Usuario')}</strong>
            <small class="role">${role}</small>
          </div>
        </div>
        <a class="item" href="${API_BASE}/cuenta.php">Mi cuenta</a>
        <a class="item" href="/cosmix/html/tienda.html#carrito">Carrito</a>
        ${role === 'admin' ? `<a class="item" href="${API_BASE}/admin.php">Panel admin</a>` : ''}
        ${role === 'rider' ? `<a class="item" href="${API_BASE}/rider.php">Panel domiciliario</a>` : ''}
        <hr class="sep"/>
        <button class="item danger" data-logout>Salir</button>
      </div>
    `;

    host.appendChild(wrapper);

    // Estilos mínimos (por si el CSS global no lo tiene aún)
    injectOnce('userMenuStyles', `
      .btn-avatar{background:#fff;border:1px solid rgba(0,0,0,.1);height:40px;padding:0 10px;border-radius:12px;display:inline-flex;align-items:center;gap:8px;cursor:pointer}
      .btn-avatar .avatar{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:#6d28d9;color:#fff;font-weight:600}
      .dropdown{position:absolute;right:0;top:48px;background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,.12);min-width:220px;display:none;z-index:50}
      .dropdown.open{display:block}
      .dropdown .user-block{display:flex;gap:10px;align-items:center;padding:12px}
      .dropdown .avatar.sm{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:#6d28d9;color:#fff;font-weight:600}
      .dropdown .meta small{opacity:.7;text-transform:uppercase;font-size:.72rem}
      .dropdown .item{display:block;width:100%;text-align:left;padding:10px 12px;background:#fff;border:0;color:#111;text-decoration:none;cursor:pointer}
      .dropdown .item:hover{background:#f6f6fb}
      .dropdown .item.danger{color:#b91c1c}
      .dropdown .sep{margin:6px 0;border:0;border-top:1px solid rgba(0,0,0,.06)}
    `);

    // Toggle dropdown
    const btn = $('#userBtn', wrapper);
    const dd  = $('#userDropdown', wrapper);
    const close = (ev) => {
      if (!dd.classList.contains('open')) return;
      if (!wrapper.contains(ev.target)) {
        dd.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
        document.removeEventListener('click', close);
      }
    };
    btn.addEventListener('click', () => {
      dd.classList.toggle('open');
      btn.setAttribute('aria-expanded', String(dd.classList.contains('open')));
      if (dd.classList.contains('open')) {
        setTimeout(() => document.addEventListener('click', close), 0);
      }
    });

    // Logout
    $('[data-logout]', wrapper)?.addEventListener('click', (e) => {
      e.preventDefault();
      logout();
    });
  }

  // ----------------------------------------------------------
  // Conmutador de CTAs en header (data-guest / data-user)
  // ----------------------------------------------------------
  function applyHeaderSwitch(user) {
    const isLogged = !!user;
    $$('[data-guest]').forEach(el => isLogged ? hide(el) : show(el));
    $$('[data-user]').forEach(el => isLogged ? show(el) : hide(el));
  }

  // ----------------------------------------------------------
  // Burger / Menú móvil y preloader
  // ----------------------------------------------------------
  function wireCommonUI() {
    const burger = $('[data-menu-toggle]');
    const menu   = $('#primary-menu');
    if (burger && menu) {
      burger.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        burger.setAttribute('aria-expanded', String(open));
      });
    }

    // Oculta preloader cuando ya pintamos la vista
    const pre = $('#preloader');
    if (pre) setTimeout(() => hide(pre), 150);
  }

  // ----------------------------------------------------------
  // Pequeñas mejoras accesibles
  // ----------------------------------------------------------
  function escapeHtml(str='') {
    return str.replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }

  function injectOnce(id, cssText) {
    if ($('#' + id)) return;
    const style = document.createElement('style');
    style.id = id;
    style.textContent = cssText;
    document.head.appendChild(style);
  }

  // ----------------------------------------------------------
  // Boot
  // ----------------------------------------------------------
  document.addEventListener('DOMContentLoaded', async () => {
    wireCommonUI();

    // 1) Detectamos sesión y ajustamos header
    const user = await getSession();
    applyHeaderSwitch(user);
    if (user) renderUserNav(user);

    // 2) “Salir” también desde cualquier enlace suelto en el header
    $$('[data-logout]').forEach(a => {
      a.addEventListener('click', (e) => { e.preventDefault(); logout(); });
    });
  });
})();
