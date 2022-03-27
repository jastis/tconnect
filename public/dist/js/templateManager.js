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
        $("#btn_ccard").html(
            '<img src= "/img/loading.svg" width="40" height="40"/>'
        );
        $("#btn_ccard").prop("disabled", true);
        // Stop form from submitting normally
        event.preventDefault();
        $.post("/customcard/add", $("#frm_ccard").serialize())
            .done(function (data) {
                // $( "#errorLogin" ).html('');
                // $( "#errorLogin" ).fadeOut(100);
               
                if (data["code"] == 200) {
                    toastMixin.fire({
                        animation: true,
                        title: data.message,
                    });
                    location.replace('/');
    
                    $("#btn_ccard").prop("disabled", false);
                } else if (data["code"] >= 201 && data["code"] <= 299) {
                    toastMixin.fire({
                        title: data.message,
                        icon: "error",
                    });
                    $("#btn_ccard").prop("disabled", false);
                    $("#btn_ccard").html("Create New Theme");
                } else {
                    $("#btn_ccard").html("Create New Theme");
                    $("#btn_ccard").prop("disabled", false);
                }
            })
            .fail(function (e) {
             //const obj = JSON.parse(e.responseText); //obj.error.Description,
                $("#btn_ccard").html("Create New Theme");
                $("#btn_ccard").prop("disabled", false);
                toastMixinF.fire({
                    animation: true,
                    title: e.responseText, //obj.error.Description
                });
            });
      }
    });
    $('#frm_ccard').validate({
      rules: {
        template_name: {
          required: true,
        },
        type_id: {
          required: true,
        },
        theme: {
          required: true
        },
      },
      messages: {
        template_name: {
          required: "Please enter Template Name",
        },
        type_id: {
          required: "Please choose a type of subscription",
        },
        theme: "Please paste theme JSON for the template"
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
