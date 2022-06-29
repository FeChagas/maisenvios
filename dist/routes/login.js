const HOST_URL = '';

$("#login").submit(function (e) {
  e.preventDefault(); // avoid to execute the actual submit of the form.

  var form = $("#login");
  var actionUrl = form.attr("action");

  $.ajax({
    type: "POST",
    url: actionUrl,
    data: form.serialize(), // serializes the form's elements.
    success: function (data) {
      var json = data;

      if (json[0].status === 1) {
        Swal.fire("Atenção!", "Usuário ou senha incorretos", "error");
      } else if (json[0].status === 0) {
        location.href = "/ready-shops.php";
      }
    },
  });
});
