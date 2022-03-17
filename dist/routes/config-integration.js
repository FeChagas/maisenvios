var scripts = document.getElementsByTagName("script");
var index = scripts.length - 1;
var myScript = scripts[index];
// myScript now contains our script object
const HOST_URL = myScript.src.replace(/^[^\?]+\??/, "");
const VALID_PLATAFORM = ["VTEX"];

$(document).ready(() => {
  var CURRENT_SHOP;
  var urlParams = new URLSearchParams(window.location.search);
  const SHOP_ID = urlParams.get("idShop");
  if (SHOP_ID > 0) {
    actionUrl = HOST_URL + `/php/shop/ready.php?id=${SHOP_ID}`;
    $.ajax({
      type: "GET",
      url: actionUrl,
      success: function (data) {
        data.forEach((element) => {
          if ($.inArray(element.ecommerce, VALID_PLATAFORM) <= -1) {
            $("#plataform-not-supported-alert").show();
          } else {
            CURRENT_SHOP = element;
            $(`#${element.ecommerce}`).show();

            actionUrl =
              HOST_URL + `/php/integration/get.php?shop_id=${SHOP_ID}`;
            $.ajax({
              type: "GET",
              url: actionUrl,
              success: function (data) {
                data.forEach((element) => {
                  element.value.forEach((value) => {
                    $(
                      'input:checkbox[name="' +
                        element.name +
                        '"][value="' +
                        value +
                        '"]'
                    ).prop("checked", true);
                  });
                });
              },
            });

            $("#integration-config-form").submit(function (e) {
              e.preventDefault(); // avoid to execute the actual submit of the form.
              var payload = {};
              switch (CURRENT_SHOP.ecommerce) {
                case "VTEX":
                  payload = {
                    vtex_integration_step: [],
                    vtex_order_status: [],
                  };
                  $('input[name="vtex_integration_step"]:checked').each(
                    (index, object) => {
                      payload["vtex_integration_step"].push(object.value);
                    }
                  );

                  $('input[name="vtex_order_status"]:checked').each(
                    (index, object) => {
                      payload["vtex_order_status"].push(object.value);
                    }
                  );

                  break;

                default:
                  Swal.fire({
                    icon: "error",
                    title: "Algo está errado!",
                    text: "Essa integração não tem suporte a configurações ainda.",
                    confirmButtonText: "Legal",
                  }).then((result) => {
                    window.location.replace(HOST_URL + "/ready-shops.php");
                  });
                  break;
              }

              var actionUrl =
                HOST_URL + `/php/integration/edit.php?shop_id=${SHOP_ID}`;
              $.ajax({
                type: "POST",
                data: payload,
                url: actionUrl,
                success: function (data) {
                  switch (data.success) {
                    case true:
                      Swal.fire({
                        icon: "success",
                        title: "Sucesso!",
                        text: "As configurações foram atualizadas.",
                        confirmButtonText: "Legal",
                      }).then(() => {
                        window.location.replace(HOST_URL + "/ready-shops.php");
                      });
                      break;

                    default:
                      Swal.fire({
                        icon: "error",
                        title: "Algo deu errado!",
                        text: "Verifique as informações enviadas e tente novamente.",
                        confirmButtonText: "Ta bom!",
                      });
                      break;
                  }
                },
              });
            });
          }
        });
      },
    });
  }
});