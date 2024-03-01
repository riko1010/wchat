/* headjs loads, on ready -> */
head(function () {
  
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
  $('.g').on('click', function(e){
    e.preventDefault();
  });
  
});