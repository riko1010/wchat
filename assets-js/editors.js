/* headjs */
/* headjs loads, on ready -> */
head(function () {
  
/*  wysiwyg editor */
tinymce.init({
  selector: '#annotation-update-input',
  menubar: 'edit view insert format',
  plugins: [
      'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
      'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen', 'insertdatetime',
      'media', 'table', 'emoticons', 'template', 'help'
    ],
});  
/* end wysiwyg editor */   
})

