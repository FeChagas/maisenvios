var scripts = document.getElementsByTagName("script");
var index = scripts.length - 1;
var myScript = scripts[index];
// myScript now contains our script object
const HOST_URL = myScript.src.replace(/^[^\?]+\??/, "");

actionUrl = HOST_URL + "/php/shop/ready.php";
$.ajax({
  type: "GET",
  url: actionUrl,
  success: function (data) {
    data.forEach((element) => {
      html = `
          <tr>
            <td>${element.id}</td>
            <td>${element.name}</td>
            <td>${element.account}</td>
            <td>${element.ecommerce}</td>
            <td>${element.active}</td>
            <td>
              <a class="btn btn-primary" href="${HOST_URL}/ready-shippings.php?idShop=${element.id}">
                <div class="side-menu__icon"> <i data-feather="target"></i></div>
                Listar transportadoras
              <a/>
            </td>
          </tr>
        `;
      $("#ready-shops").append(html);
    });
  },
});
