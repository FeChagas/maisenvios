actionUrl = "/painel/php/shop/ready";
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
              <a class="btn btn-primary" href="/painel/ready-shippings?idShop=${element.id}">
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
