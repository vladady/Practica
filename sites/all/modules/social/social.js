jQuery(document).ready(function($) {
  $('#post-node-form').tabs();
  $('.request-button').click(function(){
    friend_r = $(this).data('id');
    r_type = $(this).data('type');
    
    $.ajax({
        url: Drupal.settings.basePath + "add",
        type: "POST",
        data: { type: r_type, fid: friend_r },
        success: function(){
            location.reload();
        }
    });
  });
  
});