(() => {
  // ======== Helpers ========
  const $  = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => [...ctx.querySelectorAll(sel)];
  const on = (sel, ev, fn, ctx=document) => {
    const el = typeof sel === 'string' ? $(sel, ctx) : sel;
    if (el) el.addEventListener(ev, fn, {passive:true});
  };
  const fmt = n => Number(n||0).toLocaleString('es-CO', {style:'currency', currency:'COP', maximumFractionDigits:0});

  // API pública (lista)
  const API = '/cosmix/magic_auth_php/productos_list.php';

  // ======== Estado ========
  const state = {
    q: '',
    sort: 'name-asc',
    onlyInStock: false,
    category: 'all',     // 'all' | 'boardgame' | 'tcg' | 'acc'
    min: null,
    max: null,
    view: 'grid',        // 'grid' | 'list'
    page: 1
  };

  // ==== Puente HTML ↔ JS: IDs/atributos tolerantes ====
// (colócalo después de: const $ = ... y const $$ = ... )
const byId = (id) => document.getElementById(id);

const els = {
  // q (viejo) o search (nuevo)
  search: byId('search') || byId('q'),

  // onlyStock (nuevo) o onlyInStock (viejo)
  onlyStock: byId('onlyStock') || byId('onlyInStock'),

  sort: byId('sort'),

  // densidad
  densCompact:  byId('densCompact'),
  densStandard: byId('densStandard'),
  densRoomy:    byId('densRoomy'),

  refresh: byId('refresh'),

  // contador: countLabel (nuevo) o count (viejo)
  countLabel: byId('countLabel') || byId('count'),

  // grid
  grid: byId('product-grid'),

  // rangos de precio
  minPrice: byId('minPrice'),
  maxPrice: byId('maxPrice'),
};

// chips: acepta data-category (nuevo) o data-cat (viejo)
const catChipSelector = '#filters [data-category], #filters [data-cat]';
els.catChips = $$(catChipSelector);
els.tagChips = $$('#filters [data-tag]');

const getChipCategory = (btn) => btn.dataset.category || btn.dataset.cat || 'all';


  // ======== Normalización ========
  function normalizeItem(row, idx){
    const id   = row.id ?? (idx + 1);
    const name = row.nombre ?? row.name ?? '';
    const sku  = row.sku ?? '';
    const price = Number(row.precio ?? row.price ?? 0);
    const stock = Number(row.stock ?? row.existencias ?? 0);
    const active = (row.is_active === 1) || (row.is_active === true) || (row.activo === 1);
    const img = row.img_url || row.imagen_url || `/cosmix/images/productos/img.${id}.jpg`;
    // categoría tentativa por nombre (ajustable)
    let category = 'tcg';
    const nm = (name + ' ' + (row.categoria||'')).toLowerCase();
    if (nm.includes('accesor')) category = 'acc';
    if (nm.includes('mesa')) category = 'boardgame';

    return { id, sku, name, price, stock, active, img, category };
  }

  // ======== Carga de datos (API + fallback) ========
  async function loadProducts(){
    try {
      const r = await fetch(API, { credentials: 'include' });
      if (r.ok){
        const j = await r.json();
        const raw = (j && (j.items || j.elementos || j.data)) || [];
        if (Array.isArray(raw) && raw.length){
          return raw.map(normalizeItem);
        }
      }
    } catch (e) {
      console.warn('API productos no disponible, usando estáticos.', e);
    }

    // Fallback a estáticos si existen
    const fromStatics = (window.PRODUCTS && (window.PRODUCTS.items || window.PRODUCTS)) || [];
    if (Array.isArray(fromStatics) && fromStatics.length){
      return fromStatics.map(normalizeItem);
    }
    return [];
  }

  // ======== Filtros / orden ========
  function applyFilters(data){
    let out = [...data];

    if (state.q){
      const q = state.q.toLowerCase();
      out = out.filter(p => (p.name||'').toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q));
    }
    if (state.onlyInStock){
      out = out.filter(p => p.stock > 0 && p.active);
    }
    if (state.category !== 'all'){
      out = out.filter(p => p.category === state.category);
    }
    if (state.min != null && state.min !== ''){
      out = out.filter(p => p.price >= Number(state.min));
    }
    if (state.max != null && state.max !== ''){
      out = out.filter(p => p.price <= Number(state.max));
    }

    switch (state.sort){
      case 'name-asc':  out.sort((a,b)=>a.name.localeCompare(b.name)); break;
      case 'name-desc': out.sort((a,b)=>b.name.localeCompare(a.name)); break;
      case 'price-asc': out.sort((a,b)=>a.price-b.price); break;
      case 'price-desc':out.sort((a,b)=>b.price-a.price); break;
      case 'stock-desc':out.sort((a,b)=>b.stock-a.stock); break;
    }
    return out;
  }

  // ======== Render ========
  function renderCount(total){
    const lbl = $('#countLabel');
    if (lbl) lbl.textContent = `Mostrando ${total} producto${total===1?'':'s'}`;
  }

  function cardHTML(p){
    return `
      <article class="product ${state.view}">
        <a class="thumb" href="./producto.html?id=${encodeURIComponent(p.id)}" title="${p.name}">
          <img loading="lazy" src="${p.img}" alt="${p.name}">
        </a>
        <div class="meta">
          <h3 class="name">${p.name}</h3>
          <div class="sku muted">${p.sku}</div>
          <div class="row">
            <span class="price">${fmt(p.price)}</span>
            <span class="stock ${p.stock>0?'ok':'out'}">${p.stock>0? 'En stock' : 'Agotado'}</span>
          </div>
        </div>
      </article>
    `;
  }

  function renderListHTML(items){
    if (!items.length){
      return `<div class="empty muted">No hay productos para mostrar.</div>`;
    }
    return items.map(cardHTML).join('');
  }

  function applyViewClass(){
    const grid = $('#product-grid');
    if (!grid) return;
    grid.classList.remove('view-grid','view-list');
    grid.classList.add(state.view === 'list' ? 'view-list' : 'view-grid');
  }

  // ======== Inicialización ========
  let DATA = [];

  async function init(){
    DATA = await loadProducts();
    draw();

    // Listeners seguros (no rompen si el elemento no existe)
    on('#search', 'input', (e)=>{ state.q = e.target.value.trim(); draw(); });
    on('#sort', 'change', (e)=>{ state.sort = e.target.value; draw(); });
    on('#onlyStock', 'change', (e)=>{ state.onlyInStock = !!e.target.checked; draw(); });
    on('#minPrice', 'input', (e)=>{ state.min = e.target.value; draw(); });
    on('#maxPrice', 'input', (e)=>{ state.max = e.target.value; draw(); });

    // Chips de categoría
    $$('.chip[data-category]').forEach(chip=>{
      chip.addEventListener('click', ()=>{
        $$('.chip[data-category]').forEach(c=>c.classList.remove('active'));
        chip.classList.add('active');
        state.category = chip.dataset.category || 'all';
        draw();
      }, {passive:true});
    });

    function bindUI() {
  if (els.search) els.search.addEventListener('input', (e) => {
    state.q = (e.target.value || '').trim().toLowerCase();
    state.page = 1;
    applyAndRender();
  });

  if (els.sort) els.sort.addEventListener('change', (e) => {
    state.sort = e.target.value;
    state.page = 1;
    applyAndRender();
  });

  if (els.onlyStock) els.onlyStock.addEventListener('change', (e) => {
    state.onlyInStock = !!e.target.checked;
    state.page = 1;
    applyAndRender();
  });

  // Densidad
  const setDensity = (d) => {
    state.density = d;
    ['densCompact','densStandard','densRoomy'].forEach(id => {
      const btn = els[id];
      if (btn) btn.classList.toggle('is-active', id.toLowerCase().includes(d));
    });
    applyAndRender();
  };
  if (els.densCompact)  els.densCompact.addEventListener('click', () => setDensity('compact'));
  if (els.densStandard) els.densStandard.addEventListener('click', () => setDensity('standard'));
  if (els.densRoomy)    els.densRoomy.addEventListener('click', () => setDensity('roomy'));

  // Chips: categoría
  els.catChips.forEach(btn => {
    btn.addEventListener('click', () => {
      state.category = getChipCategory(btn);   // 'all' | 'boardgame' | 'tcg' | 'acc'
      els.catChips.forEach(b => b.classList.toggle('is-active', b === btn));
      state.page = 1;
      applyAndRender();
    });
  });

  // Chips: tag
  els.tagChips.forEach(btn => {
    btn.addEventListener('click', () => {
      state.tag = btn.dataset.tag || '';       // p.e. 'lanzamiento'
      els.tagChips.forEach(b => b.classList.toggle('is-active', b === btn));
      state.page = 1;
      applyAndRender();
    });
  });

  // Precios
  const setPrice = () => {
    const toNum = (v) => (v === '' || v == null) ? null : Number(v);
    state.min = toNum(els.minPrice?.value);
    state.max = toNum(els.maxPrice?.value);
    state.page = 1;
    applyAndRender();
  };
  if (els.minPrice) els.minPrice.addEventListener('change', setPrice);
  if (els.maxPrice) els.maxPrice.addEventListener('change', setPrice);

  // Refrescar
  if (els.refresh) els.refresh.addEventListener('click', () => {
    cargarTienda?.() || location.reload();
  });
}


    // Vista grid/list
    on('#viewGrid', 'click', ()=>{ state.view = 'grid'; applyViewClass(); });
    on('#viewList', 'click', ()=>{ state.view = 'list'; applyViewClass(); });

    // Refrescar (vuelve a consultar API)
    on('#refresh', 'click', async ()=>{
      DATA = await loadProducts();
      draw();
    });

    // Oculta "login/crear" si detectas sesión (si ya tienes una marca en localStorage/cookie, ajústalo aquí)
    try {
      const logged = !!localStorage.getItem('cmx_session');
      if (logged){
        const lg = $('#btnLogin'); if (lg) lg.style.display = 'none';
        const su = $('#btnSignup'); if (su) su.style.display = 'none';
        const pill = $('#cmxPill'); if (pill) pill.style.display = 'inline-flex';
      }
    } catch {}

    // Vista inicial
    applyViewClass();
  }

  function draw(){
    const grid = $('#product-grid');
    if (!grid) return;
    const items = applyFilters(DATA);
    grid.innerHTML = renderListHTML(items);
    renderCount(items.length);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
