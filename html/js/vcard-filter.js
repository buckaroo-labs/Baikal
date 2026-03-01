$(document).ready(function(){
  $(".vcardcategory").on("click", function() {
  
    $(this).toggleClass("active");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    if ($(this).hasClass("active")) $("#vcardtable tr." + category).show(); else $("#vcardtable tr." + category).hide();
    
  });
});