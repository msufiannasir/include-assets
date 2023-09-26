jQuery( document ).ready(function() {
  jQuery('body').on('click', 'a.advance-options', function(event) {
    event.preventDefault();
    jQuery(this).siblings('.panel').slideToggle();
  });
  jQuery('body').on('click', 'a.edit-script', function(event) {
    event.preventDefault();
    var url = window.location.href;
    var form_id = jQuery(this).attr('id');
    var id= jQuery(this).siblings('.id-sect').text();
    jQuery.ajax({
      type: "GET",
      url: url+"&edit_file_id="+id,
                
      success: function (response){
        var result = JSON.parse(response);
        var exclude_scripts = "";
        for (let i = 0; i < result.length; i++) {
          if(i==0){
            jQuery("#"+form_id+"").find(".url").val(result[i]['script_url']);
            jQuery("#"+form_id+"").find(".type").val(result[i]['type']);
            jQuery("#"+form_id+"").find(".location").val(result[i]['location']);
            jQuery("#"+form_id+"").find(".sortOrder").val(result[i]['sortOrder']);
            jQuery("#"+form_id+"").find(".id").val(result[i]['id']);
            jQuery("#"+form_id+"").find("#"+result[i]['inclusion']+"").prop('checked',true);
            if(result[i]['inclusion'] === '1'){

            }else{
              jQuery("#"+form_id+"").find("#txtid").slideToggle();
              jQuery("#"+form_id+"").find(".note").slideToggle();
            }
          }
          exclude_scripts +=result[i]['page_slug'];
          
        }
        jQuery("#"+form_id+"").find("#btn-cancel").css('display','block');
        jQuery("#"+form_id+"").find("#txtid").val(exclude_scripts);
        jQuery("#"+form_id+"").find("#btn-s").val("Update");
        jQuery("#"+form_id+"").find('#btn-s').prop('disabled', false);
        jQuery("#"+form_id+"").find('#btn-s').css('cursor','pointer');
        jQuery("#"+form_id+"").find("a.advance-options").trigger("click");
      }
    });
  });
  jQuery('body').on('click', '#btn-cancel', function(event) {
    event.preventDefault();
    var form_id = jQuery(this).attr('class');
    console.log(form_id);
    jQuery("#"+form_id+"").find(".url").val("");
    jQuery("#"+form_id+"").find(".type").val("CSS");
    jQuery("#"+form_id+"").find(".location").val("Please select Location");
    jQuery("#"+form_id+"").find(".sortOrder").val("");
    jQuery("#"+form_id+"").find(".id").val("");
    jQuery("#"+form_id+"").find("#btn-s").val("Add Script");
    jQuery("#"+form_id+"").find("#txtid").val("");
    jQuery("#"+form_id+"").find("#btn-cancel").css('display','none');
    jQuery("#"+form_id+"").find("#1").prop('checked',true);
    jQuery("#"+form_id+"").find("a.advance-options").trigger("click");
    jQuery("#"+form_id+"").find('#btn-s').prop('disabled', true);

  });
  jQuery('body').on('click', 'a.delete-script', function(event) {
    event.preventDefault();
    var id= jQuery(this).siblings('.id-sect').text();
    var url = window.location.href;
    url = url+"&file_id="+id;
    var del = confirm("Are you sure? You want to delete this script?");
    if(del === true){
      window.location.href = url;
    }

  });
  jQuery('.radio-options').on('change', function() {
    var val = jQuery(this).val();
    if(val === '2' || val === '3'){
      jQuery(this).parents("label").siblings("textarea").slideDown();
      jQuery(this).parents("label").siblings(".note").slideDown();
    }else if(val === '1'){
      jQuery(this).parents("label").siblings("textarea").slideToggle();
      jQuery(this).parents("label").siblings(".note").slideToggle();
    }
           
  });
  jQuery('.url').on('focus', function() {
    jQuery(this).css("border-color","green");       
  });
  jQuery('.url').on('blur', function() {
    console.log(jQuery(this).val());
    if(jQuery(this).val()===""){
      jQuery(this).css("border-color","red");
      // jQuery(this).parents('form').find('#btn-s').prop('disabled', true);
      jQuery(this).parents('form').find('#btn-s').css('cursor','not-allowed');
    }
    else{
      // jQuery(this).parents('form').find('#btn-s').prop('disabled', false);
      jQuery(this).parents('form').find('#btn-s').css('cursor','pointer');
    }
  });
  jQuery('.location').on('blur', function() {
    var location = jQuery(this).val();
    if(location === "Please select Location"){
      jQuery(this).css("border-color","red");
      // jQuery(this).parents('form').find('#btn-s').prop('disabled', true);
      jQuery(this).parents('form').find('#btn-s').css('cursor','not-allowed');
    }
    else{
      jQuery(this).css("border-color","green");
      jQuery(this).parents('form').find('#btn-s').prop('disabled', false);
      jQuery(this).parents('form').find('#btn-s').css('cursor','pointer');
    }
  });
});

