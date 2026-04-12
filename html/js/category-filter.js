$(document).ready(function(){
  $(".vcardcategory").on("click", function() {
  
    $(this).toggleClass("active");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    if ($(this).hasClass("active")) $("#folderitems tr." + category).show(); else $("#folderitems tr." + category).hide();
    
  });
    $(".vobjorg").on("click", function() {
  
    //$(this).toggleClass("activeorg");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    //if ($(this).hasClass("activeorg")) 
    $("#folderitems tbody tr").filter(function() {
      $(this).toggle($(this).hasClass(category));
    });
    /*
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
    window.focus();
    window.scrollTo(0, 0);
    console.log("scrolling");
    */
    console.log("scrolling to top");
    $('body').scrollTop(0);
    
  });


  $(".vtodostatus").on("click", function() {
  
    $(this).toggleClass("active");
    var todostatus=$(this).attr("id");
    if ($(this).hasClass("active")) $("#folderitems tbody>tr." + todostatus).show(); else $("#folderitems tbody>tr." + todostatus).hide();
  });

});