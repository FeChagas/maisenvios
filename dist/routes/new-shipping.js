var scripts = document.getElementsByTagName("script");
var index = scripts.length - 1;
var myScript = scripts[index];
// myScript now contains our script object
const HOST_URL = myScript.src.replace(/^[^\?]+\??/, "");

$("#new-shipping").submit(function (e) {
  e.preventDefault(); // avoid to execute the actual submit of the form.

  var form = $("#new-shipping");
  var actionUrl = HOST_URL + form.attr("action");

  $.ajax({
    type: "POST",
    url: actionUrl,
    data: form.serialize(), // serializes the form's elements.
    success: function (data) {
      var json = data;

      if (json[0].status === 1) {
        Swal.fire("Atenção!", "Loja já cadastrado na base", "error");
      } else if (json[0].status === 0) {
        Swal.fire("Sucesso!", "Cadastro efetuado com sucesso", "success");
      }
    },
  });
});
