const HOST_URL = '';

actionUrl = HOST_URL + "/php/users/ready.php";
$.ajax({
  type: "GET",
  url: actionUrl,
  success: function (data) {
    data.forEach((element) => {
      html = `
          <tr>
            <td>${element.id}</td>
            <td>${element.name}</td>
            <td>${element.email}</td>
            <td>${element.active}</td>
          </tr>
        `;
      $("#ready-users").append(html);
    });
  },
});
