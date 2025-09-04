// cosmix/js/auth.js
async function postJSON(url, data) {
  const res = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)});
  const json = await res.json().catch(()=>({}));
  if (!res.ok) throw json;
  return json;
}

window.CosmixAuth = {
  async submitRegister(form) {
    const data = Object.fromEntries(new FormData(form));
    data.nombre    = data.name || data.nombre;
    data.confirmar = data.confirmPassword || data.confirmar;
    return postJSON('/magic_auth_php/register.php', data);
  },
  async submitLogin(form) {
    const data = Object.fromEntries(new FormData(form));
    return postJSON('/magic_auth_php/login.php', { email: data.email, password: data.password });
  },
  async submitLogout() {
    return postJSON('/magic_auth_php/logout.php', { token: (document.cookie.match(/mc_session=([^;]+)/)||[])[1] });
  },
  init() {
    const regForm = document.getElementById('registerForm') || document.getElementById('form-register');
    if (regForm) regForm.addEventListener('submit', async e => {
      e.preventDefault();
      const msg = document.getElementById('register_msg') || document.getElementById('registerMessage');
      try { await this.submitRegister(regForm); if (msg){ msg.style.display='block'; msg.textContent='Cuenta creada. ¡Bienvenido!'; msg.style.color='#166534'; }
        setTimeout(()=> location.assign('login.html'), 800);
      } catch (err) { if (msg){ msg.style.display='block'; msg.textContent=(err && err.error) ? err.error : 'Error al registrar'; msg.style.color='#b91c1c'; } }
    });

    const loginForm = document.getElementById('loginForm') || document.getElementById('form-login');
    if (loginForm) loginForm.addEventListener('submit', async e => {
      e.preventDefault();
      const msg = document.getElementById('login_msg') || document.getElementById('loginMessage');
      try { await this.submitLogin(loginForm); if (msg){ msg.style.display='block'; msg.textContent='Inicio de sesión correcto'; msg.style.color='#166534'; }
        setTimeout(()=> location.assign('tienda.html'), 600);
      } catch (err) { if (msg){ msg.style.display='block'; msg.textContent=(err && err.error) ? err.error : 'Error de autenticación'; msg.style.color='#b91c1c'; } }
    });
  }
};
document.addEventListener('DOMContentLoaded', ()=> window.CosmixAuth?.init?.());
