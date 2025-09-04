// /cosmix/js/register.js
(function () {
  const f = document.getElementById('form-register');
  if (!f) return;

  const showMsg = window.__regShowMsg || function(t){ alert(t); };

  f.addEventListener('submit', async function (e) {
    // si el HTML hace POST clásico (por no haber JS), no evitamos nada
    e.preventDefault();

    const nombre   = document.getElementById('registerName').value.trim();
    const email    = document.getElementById('email').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const pais     = document.getElementById('pais').value.trim();
    const pass     = document.getElementById('reg_pass').value;
    const confirm  = document.getElementById('reg_pass_confirm').value;

    if (!nombre || !email || !pass) {
      showMsg('Nombre, correo y contraseña son obligatorios.');
      return;
    }
    if (pass !== confirm) {
      showMsg('Las contraseñas no coinciden.');
      return;
    }

    try {
      const res = await fetch('/cosmix/magic_auth_php/register.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
          name: nombre,
          email: email,
          telefono: telefono,
          pais: pais,
          password: pass,
          confirmPassword: confirm
        })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || data.ok === false) {
        const err = (data && (data.error || data.detail)) || `HTTP ${res.status}`;
        showMsg(`Error al registrar: ${err}`);
        return;
      }

      showMsg('Cuenta creada con éxito. Redirigiendo…', true);
      // opcional: limpiar y redirigir al login
      setTimeout(() => { window.location.href = '/cosmix/html/login.html'; }, 900);
    } catch (err) {
      showMsg(`Error de red: ${err.message || err}`);
    }
  });
})();
