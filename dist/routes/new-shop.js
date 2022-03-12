var scripts = document.getElementsByTagName("script");
var index = scripts.length - 1;
var myScript = scripts[index];
// myScript now contains our script object
const HOST_URL = myScript.src.replace(/^[^\?]+\??/, "");

var urlParams = new URLSearchParams(window.location.search);
const SHOP_ID = urlParams.get("idShop");

if (SHOP_ID > 0) {
  actionUrl = HOST_URL + `/php/shop/ready.php?id=${SHOP_ID}`;
  $.ajax({
    type: "GET",
    url: actionUrl,
    success: function (data) {
      data.forEach((element) => {
        $(`input[name=name]`).val(element.name);
        $(`input[name=key_mais]`).val(element.key_mais);
        $(`input[name=key_primary]`).val(element.key_primary);
        $(`input[name=token_primary]`).val(element.token_primary);
        $(`input[name=account]`).val(element.account);
        $(`select[name=ecommerce] option[value=${element.ecommerce}]`).attr(
          "selected",
          "selected"
        );
      });
    },
  });
}

$("#new-shop").submit(function (e) {
  e.preventDefault(); // avoid to execute the actual submit of the form.

  var form = $("#new-shop");
  var actionUrl = HOST_URL + form.attr("action");

  if (SHOP_ID > 0) {
    var actionUrl = `${HOST_URL}/php/shop/edit.php?id=${SHOP_ID}`;
  }
  $.ajax({
    type: "POST",
    url: actionUrl,
    data: form.serialize(), // serializes the form's elements.
    success: function (data) {
      if (data.success == false) {
        Swal.fire("Atenção!", "Loja já cadastrado na base", "error");
      } else if (data.success == true) {
        Swal.fire({
          title: "Sucesso!",
          text: "Cadastro efetuado com sucesso",
          confirmButtonText: "Legal",
        }).then((result) => {
          window.location.replace(HOST_URL + "/ready-shops.php");
        });
      }
    },
  });
});
