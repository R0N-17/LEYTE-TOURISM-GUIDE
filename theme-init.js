/* Runs in <head> before the page paints, so there is never a flash of the wrong theme.
   Same rules as js/theme.js: a saved choice (light or dark) wins; an older saved "system" / "auto" is read as the theme it
   was showing (device setting / 6 AM to 6 PM); with no choice at all the device's light/dark setting is the starting point.
   Keep this in sync with js/theme.js. The key is shared by the public site and the admin console. */
(function () {
  var mode = null;
  try { mode = localStorage.getItem('leyteTheme'); } catch (e) {}
  var osDark = !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
  var h = new Date().getHours();
  var dark = mode === 'dark' ? true : mode === 'light' ? false : mode === 'auto' ? (h < 6 || h >= 18) : osDark;
  document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
})();
