var scripts = document.getElementsByTagName("script");
var index = scripts.length - 1;
var myScript = scripts[index];
// myScript now contains our script object
const HOST_URL = myScript.src.replace(/^[^\?]+\??/, "");

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
