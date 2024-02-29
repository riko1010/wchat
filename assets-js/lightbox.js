/* headjs loads, on ready -> */
head(function () {
  
  /* lightbox for images, docs, iframes, current lightbox does not close when clicking overlay */
  const lightbox = GLightbox({
    touchNavigation: true,
    loop: true,
    autoplayVideos: true,
    selector: ".g",
    closeOnOutsideClick: true,
    preload: true,
  });
  
});