// Inline notification banner.
// Pages can pre-fill the notification by passing a message via the URL fragment, e.g.:
//   /dashboard#msg=Welcome%20back
(function () {
    const el = document.getElementById('notification');
    if (!el) return;

    const hash = window.location.hash || '';
    if (!hash.startsWith('#msg=')) return;

    const raw = decodeURIComponent(hash.slice('#msg='.length));

    // V4: DOM XSS — the message taken from location.hash is dropped into innerHTML
    // without sanitising. Visiting /dashboard#msg=<img src=x onerror=alert(1)>
    // executes the payload in the victim's browser.
    el.innerHTML = raw;
    el.hidden = false;
})();
