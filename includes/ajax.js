function uploadProfile() {
      $.ajax({
           type: "POST",
           url: 'uploadprofilephoto.php',
           data:{action:'call_this'},
           success:function(html) {
             alert(html);
           }

      });
 }
