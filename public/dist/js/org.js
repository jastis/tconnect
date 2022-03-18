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


$(document).on("submit", "#frm_org", function (e) {
    $("#btn_org").html(
      '<img src= "/img/loading.svg" width="40" height="40"/>'
  );
  $("#btn_org").prop("disabled", true);
  // Stop form from submitting normally
  e.preventDefault();
	e.stopPropagation();
	
	let form = $("#frm_org")[0];
	let data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: "multipart/form-data",
		url: "/attendance/add/organization",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000,
		success: function (data) {
			toastMixin.fire({
                title: data["message"],
                icon: "info",
            });
            $("#btn_org").html("Create Organization");
            $("#btn_org").prop("disabled", false);
           location.reload();
		},
		error: function (e) {
			toastMixinF.fire({
				animation: true,
				title: e.responseText,
			});
            $("#btn_event").html("Create Event");
            $("#btn_event").prop("disabled", false);
		},
	});
});

$(document).on("submit", "#frm_event", function (e) {
    $("#btn_event").html(
      '<img src= "/img/loading.svg" width="40" height="40"/>'
  );
  $("#btn_event").prop("disabled", true);
  // Stop form from submitting normally
  e.preventDefault();
	e.stopPropagation();
	
	let form = $("#frm_event")[0];
	let data = new FormData(form);

	$.ajax({
		type: "POST",
		enctype: "multipart/form-data",
		url: "/attendance/add/event",
		data: data,
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000,
		success: function (data) {
			toastMixin.fire({
                title: data["message"],
                icon: "info",
            });
            $("#btn_event").html("Create Event");
            $("#btn_event").prop("disabled", false);
            location.reload();
		},
		error: function (e) {
			toastMixinF.fire({
				animation: true,
				title: e.responseText,
			});
            $("#btn_event").html("Create Event");
            $("#btn_event").prop("disabled", false);
		},
	});
});