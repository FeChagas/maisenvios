var scripts = document.getElementsByTagName("script");
var index = scripts.length - 1;
var myScript = scripts[index];
// myScript now contains our script object
const HOST_URL = myScript.src.replace(/^[^\?]+\??/, "");

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
        location.href = HOST_URL + "/ready-shops.php";
      }
    },
  });
});
