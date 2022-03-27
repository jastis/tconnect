var toastMixin = Swal.mixin({
    toast: true,
    icon: "success",
    title: "General Title",
    animation: false,
    position: "center",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });
  var toastMixinF = Swal.mixin({
    toast: true,
    icon: "error",
    title: "General Title",
    animation: false,
    position: "center",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });

  $(document).on('click','#sendmail', function(e){
    e.preventDefault();
    e.stopPropagation();
    var url = $(this).attr('href');
  
        
        $.ajax({
            type: "GET",
            url: url,
            success: function (data) {
                if (data == 1){
                toastMixin.fire({
                    animation: true,
                    title: "Mail Sent",
                  });
                }else{
                    toastMixinF.fire({
                        animation: true,
                        title: "Error Sending Reminder",
                      });
                }
               
    
            },
            error: function (e) {
                toastMixinF.fire({
                    animation: true,
                    title: "Error Sending Reminder",
                  });
               
    
            }
        });
    
    });
    