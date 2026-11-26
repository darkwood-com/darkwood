/* Darkwood video embeds: no third-party request before an explicit load action. */
(() => {
  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-youtube-load]');
    const host = button?.closest('[data-youtube-id]');
    if (!host || !/^[A-Za-z0-9_-]{11}$/.test(host.dataset.youtubeId)) return;
    const frame = document.createElement('iframe');
    frame.src = `https://www.youtube-nocookie.com/embed/${host.dataset.youtubeId}?autoplay=0&playsinline=1`;
    frame.title = host.dataset.youtubeTitle || 'Vidéo Darkwood';
    frame.loading = 'lazy';
    frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    frame.referrerPolicy = 'strict-origin-when-cross-origin';
    frame.allowFullscreen = true;
    frame.style.cssText = 'display:block;width:100%;aspect-ratio:16/9;border:0';
    host.replaceChildren(frame);
  });
})();
