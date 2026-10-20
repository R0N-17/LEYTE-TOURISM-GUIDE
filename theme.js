/* Theme switcher: Light | Dark.
   One saved choice (localStorage key leyteTheme) applies on every public page AND every admin page, across visits and tabs.
   A visitor who has never chosen starts in the theme their device is using (a one-time starting point, not a mode), and the
   switcher always shows Light or Dark, whichever is on screen. Older versions also saved "system" or "auto"; those saved values
   are converted once to the theme they were showing, so nobody's appearance changes unexpectedly.
   Buttons: data-theme-mode="light|dark" picks a theme; data-theme-toggle flips between Light and Dark. */

const Theme = (() => {
  const KEY = 'leyteTheme', MODES = ['light', 'dark'];
  const DAY_START = 6, DAY_END = 18;           // only used to convert an old saved "auto" choice
  const listeners = [];
  const deviceTheme = () => (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';

  function store(m) { try { localStorage.setItem(KEY, m); } catch (e) {} }

  // The saved choice ('light' | 'dark'), or null when this browser has none yet.
  function readSaved() {
    let m = null;
    try { m = localStorage.getItem(KEY); } catch (e) {}
    if (MODES.includes(m)) return m;
    let moved = null;                                  // an old "system" / "auto" value: keep what it was showing
    if (m === 'system') moved = deviceTheme();
    else if (m === 'auto') { const h = new Date().getHours(); moved = h >= DAY_START && h < DAY_END ? 'light' : 'dark'; }
    if (moved) store(moved);
    return moved;
  }
  let mode = readSaved() || deviceTheme();

  function paint() {
    document.documentElement.setAttribute('data-bs-theme', mode);
    document.querySelectorAll('[data-theme-mode]').forEach(b =>
      b.setAttribute('aria-pressed', String(b.dataset.themeMode === mode)));
    // The round buttons show the theme you are in right now: a sun in Light, a moon in Dark.
    document.querySelectorAll('.theme-fab-toggle i, [data-theme-toggle] i').forEach(i => { i.className = 'bi ' + (mode === 'dark' ? 'bi-moon-stars' : 'bi-sun'); });
    document.querySelectorAll('[data-theme-toggle]').forEach(b => {
      const next = mode === 'dark' ? 'light' : 'dark';
      b.setAttribute('aria-label', `Switch to ${next} mode`);
      b.setAttribute('title', `Switch to ${next} mode (now ${mode})`);
      b.setAttribute('aria-pressed', String(mode === 'dark'));
    });
  }

  // silent = don't notify listeners (used when applying a theme that came from the server)
  function set(m, opts) {
    if (!MODES.includes(m)) return;                    // anything else (e.g. an old account value) is ignored
    mode = m;
    store(m);
    paint();
    if (!(opts && opts.silent)) listeners.forEach(fn => fn(m));
  }

  // Another tab or page changed the theme: follow it.
  window.addEventListener('storage', e => { if (e.key === KEY) { mode = readSaved() || mode; paint(); } });

  // Pages without a theme menu (login, register, ...) get a small floating switcher:
  // one round button showing the current theme; hover it (or tap it on a phone) and Light / Dark drop down.
  // A page can opt out with <body data-no-theme-fab> (the admin console has its own sun/moon button).
  if (!document.querySelector('[data-theme-mode]') && !document.body.hasAttribute('data-no-theme-fab')) {
    const fab = document.createElement('div');
    fab.className = 'theme-fab';
    fab.innerHTML =
      '<button type="button" class="theme-fab-toggle" aria-haspopup="true" aria-label="Color theme" title="Color theme"><i class="bi"></i></button>' +
      '<div class="theme-fab-menu" role="group" aria-label="Color theme">' +
      '<button type="button" class="theme-fab-item" data-theme-mode="light"><i class="bi bi-sun"></i>Light Mode</button>' +
      '<button type="button" class="theme-fab-item" data-theme-mode="dark"><i class="bi bi-moon-stars"></i>Dark Mode</button>' +
      '</div>';
    document.body.appendChild(fab);
  }

  // Tap support for phones (mouse users just hover). Works for the floating switcher and for the one in the dashboard top bar.
  document.addEventListener('click', e => {
    const tog = e.target.closest('.theme-fab-toggle');
    const item = e.target.closest('.theme-fab-item');
    if (item) item.blur();                          // release focus so the menu folds up after you pick a theme
    document.querySelectorAll('.theme-fab.open').forEach(f => {
      if (item || !tog || !f.contains(tog)) f.classList.remove('open');
    });
    if (tog && !item && window.matchMedia('(hover: none)').matches) tog.closest('.theme-fab').classList.toggle('open');
  });

  document.addEventListener('click', e => {
    const b = e.target.closest('[data-theme-mode]');
    if (b) { set(b.dataset.themeMode); return; }
    const t = e.target.closest('[data-theme-toggle]');           // simple sun/moon button: flips between Light and Dark
    if (t) set(mode === 'dark' ? 'light' : 'dark');
  });

  paint();
  // hasSaved: this browser already has an explicit choice. Logging in or out must not overwrite it with the account's older value.
  const hasSaved = () => !!readSaved();
  return { get: () => mode, resolved: () => mode, hasSaved, set, refresh: paint, onChange: fn => listeners.push(fn) };
})();
