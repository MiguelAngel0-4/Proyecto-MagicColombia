console.log('[login.js] cargado');

// Utilidad para mostrar mensajes (usa tu banner si existe)
function showMsg(msg, ok) {
  const el =
    document.querySelector('[data-login-alert]') ||
    document.getElementById('login-alert');
  if (el) {
    el.textContent = msg;
    el.classList.toggle('is-ok', !!ok);
    el.classList.toggle('is-error', !ok);
  } else {
    (ok ? console.log : console.warn)(msg);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  if (!form) return;

  const emailEl = form.querySelector('input[type="email"]');
  const passEl  = form.querySelector('input[type="password"]');
  const submit  = form.querySelector('button[type="submit"], input[type="submit"]');

  async function doLogin(ev) {
    ev.preventDefault();

    const email = (emailEl?.value || '').trim().toLowerCase();
    const password = passEl?.value || '';

    if (!email || !password) {
      showMsg('Ingresa correo y contraseña');
      return;
    }

    submit && (submit.disabled = true);
    showMsg('Validando…');

    try {
      const r = await fetch('/cosmix/magic_auth_php/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        cache: 'no-store',
        body: JSON.stringify({ email, password })
      });

      const data = await r.json().catch(() => null);

      if (!r.ok) {
        const err = data && data.error ? data.error : 'server_error';
        if (err === 'user_not_found') {
          showMsg('Login falló: usuario no encontrado');
        } else if (err === 'invalid_credentials') {
          showMsg('Login falló: contraseña incorrecta');
        } else if (err === 'invalid_input') {
          showMsg('Login falló: datos incompletos');
        } else {
          showMsg('No se pudo iniciar sesión: server_error');
        }
        return;
      }

      if (!data || !data.ok || !data.redirect) {
        showMsg('Login falló: Respuesta inválida');
        return;
      }

      showMsg('Sesión iniciada. Redirigiendo…', true);
      setTimeout(() => {
        location.href = data.redirect;
      }, 400);
    } catch (err) {
      console.error(err);
      showMsg('No se pudo conectar con el servidor');
    } finally {
      submit && (submit.disabled = false);
    }
  }

  form.addEventListener('submit', doLogin);
});
