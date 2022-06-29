const HOST_URL = '';

var query = location.search.slice(1);
var partes = query.split("&");
var data = {};
partes.forEach(function (parte) {
  var chaveValor = parte.split("=");
  var chave = chaveValor[0];
  var valor = chaveValor[1];
  data[chave] = valor;
});
actionUrl = HOST_URL + "/php/shipping/ready.php";
idShop = data["idShop"];
$.ajax({
  type: "GET",
  url: actionUrl,
  data: {
    idShop: idShop,
  },
  success: function (data) {
    data.forEach((element) => {
      html = `
          <tr>
            <td>${element.id}</td>
            <td>${element.name}</td>
            <td>${element.correios}</td>
            <td>${element.active}</td>
          </tr>
        `;
      $("#ready-shippings").append(html);
    });
  },
});
