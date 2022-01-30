  actionUrl = '/painel/php/users/ready';
  $.ajax({
    type: "GET",
    url: actionUrl,
    success: function(data){
      data.forEach(element => {
        html = `
          <tr>
            <td>${element.id}</td>
            <td>${element.name}</td>
            <td>${element.email}</td>
            <td>${element.active}</td>
          </tr>
        `;
        $('#ready-users').append(html);
      });
    }
  })