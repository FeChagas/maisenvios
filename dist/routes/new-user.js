
$("#new-user").submit(function(e) {

  e.preventDefault(); // avoid to execute the actual submit of the form.

  var form = $("#new-user");
  var actionUrl = form.attr('action');
  
  $.ajax({
      type: "POST",
      url: actionUrl,
      data: form.serialize(), // serializes the form's elements.
      success: function(data)
      {
        var json = $.parseJSON(data);
        
        if(json[0].status === 2){
          Swal.fire(
            'Atenção!',
            'Senhas não conferem',
            'error'
          )
        }else if(json[0].status === 1){
          Swal.fire(
            'Atenção!',
            'E-mail já cadastrado na base',
            'error'
          )
        }else if(json[0].status === 0){
          Swal.fire(
            'Sucesso!',
            'Cadastro efetuado com sucesso',
            'success'
          )
        }
      }
  });
  
});