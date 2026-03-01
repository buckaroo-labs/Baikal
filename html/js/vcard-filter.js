$(document).ready(function(){
  $(".vcardcategory").on("click", function() {
  
    $(this).toggleClass("active");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    if ($(this).hasClass("active")) $("#vcardtable tr." + category).show(); else $("#vcardtable tr." + category).hide();
    
  });
    $(".vcardorg").on("click", function() {
  
    $(this).toggleClass("activeorg");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    if ($(this).hasClass("activeorg")) $("#vcardtable tr").filter(function() {
      $(this).toggle($(this).hasClass(category));
    });
    
  });
});