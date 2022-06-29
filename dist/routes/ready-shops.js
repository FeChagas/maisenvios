const HOST_URL = '';

actionUrl = HOST_URL + "/php/shop/ready.php";
$.ajax({
  type: "GET",
  url: actionUrl,
  success: function (data) {
    data.forEach((element) => {
      var is_active = element.active == "1" ? "checked" : "";
      html = `
          <tr>
            <td>${element.id}</td>
            <td>${element.name}</td>
            <td>${element.account}</td>
            <td>${element.ecommerce}</td>
            <td>
              <label class="switch">
                <input type="checkbox" class="btn-primary is_active" value="${element.id}" ${is_active}>
                <span class="slider round"></span>
              </label>
            </td>
            <td>
            <a class="btn btn-primary" href="${HOST_URL}/new-shop.php?idShop=${element.id}">
              <div class="side-menu__icon"> <i data-feather="target"></i></div>
              Editar
            <a/>
            <a class="btn btn-primary" href="${HOST_URL}/ready-shippings.php?idShop=${element.id}">
              <div class="side-menu__icon"> <i data-feather="target"></i></div>
              Listar transportadoras
            <a/>
            <a class="btn btn-primary" href="${HOST_URL}/config-integration.php?idShop=${element.id}">
              <div class="side-menu__icon"> <i data-feather="target"></i></div>
              Configurar integração
            <a/>
            </td>
          </tr>
        `;
      $("#ready-shops").append(html);
      $("input[type=checkbox]").click((e) => {
        var is_active = e.target.checked;
        var shop_id = e.target.value;
        var actionUrl = `${HOST_URL}/php/shop/edit.php?id=${shop_id}`;
        $.ajax({
          type: "POST",
          url: actionUrl,
          data: { active: is_active === true ? "1" : "0" },
          success: function (data) {
            if (data.success == false) {
              Swal.fire("Atenção!", "Loja já cadastrado na base", "error");
            } else if (data.success == true) {
              Swal.fire({
                title: "Sucesso!",
                text: "Situação atualizada.",
              });
            }
          },
        });
      });
    });
  },
});
