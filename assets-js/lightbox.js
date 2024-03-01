/* headjs loads, on ready -> */
$(document).ready(function (e) {
  
  /* lightbox for images, docs, iframes, current lightbox does not close when clicking overlay */
  devlog('lightbox init');
  const lightbox = GLightbox({
    touchNavigation: true,
    loop: true,
    autoplayVideos: true,
    selector: ".g",
    closeOnOutsideClick: true,
    preload: true,
  });
  
});