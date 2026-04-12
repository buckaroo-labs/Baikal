$(document).ready(function(){
  console.log("document ready");
  $(".vcardcategory").on("click", function() {
  
    $(this).toggleClass("active");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    if ($(this).hasClass("active")) $("#folderitems tr." + category).show(); else $("#folderitems tr." + category).hide();
    
  });
  $(".vobjorg").on("click", function() {
    console.log("scrolling to top");
    var category=$(this).attr("id");
    //category = category.replace(/ /g, '');
    //category = category.replace(/&/g, '-');
    //if ($(this).hasClass("activeorg")) 
    $("#folderitems tbody tr").filter(function() {
      $(this).toggle($(this).hasClass(category));
    });
    window.scroll({
    top: 0, 
    left: 0, 
    behavior: 'smooth' 
    });
    
  });


  $(".vtodostatus").on("click", function() {
  
    $(this).toggleClass("active");
    var todostatus=$(this).attr("id");
    if ($(this).hasClass("active")) $("#folderitems tbody>tr." + todostatus).show(); else $("#folderitems tbody>tr." + todostatus).hide();
    console.log("status clicked: " + todostatus);
  });

});