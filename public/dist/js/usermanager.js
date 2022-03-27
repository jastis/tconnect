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

  $(function () {
    $.validator.setDefaults({
      submitHandler: function () {
        $("#btnSignIn").html(
            '<img src= "/img/loading.svg" width="40" height="40"/>'
        );
        $("#btnSignIn").prop("disabled", true);
        // Stop form from submitting normally
        event.preventDefault();
        $.post("/user/weblogin", $("#frmLogin").serialize())
            .done(function (data) {
                // $( "#errorLogin" ).html('');
                // $( "#errorLogin" ).fadeOut(100);
                
                if (data["statusCode"] == 200) {
                    const jwt = data["token"];
                    localStorage.setItem("jwt", jwt);
                    toastMixin.fire({
                        animation: true,
                        title: "Signed in Successfully",
                    });
                    location.replace('/');
    
                    $("#btnSignIn").prop("disabled", false);
                } else if (data["statusCode"] >= 201 && data["statusCode"] <= 299) {
                    toastMixin.fire({
                        title: data["error"]["Description"],
                        icon: "error",
                    });
                    $("#btnSignIn").prop("disabled", false);
                    $("#btnSignIn").html("Login");
                } else {
                    $("#btnSignIn").html("Login");
                    $("#btnSignIn").prop("disabled", false);
                }
            })
            .fail(function (e) {
                const obj = JSON.parse(e.responseText); //obj.error.Description,
                $("#btnSignIn").html("Login");
                $("#btnSignIn").prop("disabled", false);
                toastMixinF.fire({
                    animation: true,
                    title: obj.error.Description
                });
            });
      }
    });
    $('#frmLogin').validate({
      rules: {
        email: {
          required: true,
          email: true,
        },
        password: {
          required: true,
          minlength: 5
        },
        terms: {
          required: true
        },
      },
      messages: {
        email: {
          required: "Please enter a email address",
          email: "Please enter a valid email address"
        },
        password: {
          required: "Please provide a password",
          minlength: "Your password must be at least 5 characters long"
        },
        terms: "Please accept our terms"
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
    });
  });

  
  $(document).on('click','#req_new_pw', function(e){
    e.preventDefault();
    e.stopPropagation();
    var url = '/user/forgot_pw'
     
    event.preventDefault();
    $.post(url, $("#reset_pw_frm").serialize())
        .done(function (data) {
               $("#req_new_pw").prop("disabled", false);
                toastMixin.fire({
                  animation: true,
                  title: data.description//"Password Reset Successful, Check your mail the new password.",
              });
        })
        .fail(function (e) {
            const obj = JSON.parse(e.responseText); //obj.error.Description,
            $("#req_new_pw").html("Login");
            $("#btnreq_new_pwSignIn").prop("disabled", false);
            toastMixinF.fire({
                animation: true,
                title: obj.error.Description
            });
        });
      });