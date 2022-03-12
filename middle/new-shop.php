<div class="grid grid-cols-12 gap-6 mt-5">
  <div class="intro-y col-span-12 lg:col-span-12">
    <div class="intro-y box">
      <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
          <h2 class="font-medium text-base mr-auto">
             Nova loja
          </h2>
      </div>
      <div class="p-5">
        <form action="/php/shop/new.php" id="new-shop">
          <div class="mt-2">
              <label for="regular-form-1" class="form-label">Nome</label>
              <input id="regular-form-1" type="text" class="form-control" placeholder="Nome" name="name">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-2" class="form-label">Chave Mais Envio</label>
              <input id="regular-form-2" type="text" class="form-control" placeholder="Chave Mais Envio" name="key_mais">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-3" class="form-label">Chave Primaria da loja</label>
              <input id="regular-form-3" type="text" class="form-control" placeholder="Chave Primaria da loja" name="key_primary">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-4" class="form-label">Chave Secundaria da loja</label>
              <input id="regular-form-4" type="text" class="form-control" placeholder="Chave Secundaria da loja" name="token_primary">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-4" class="form-label">Account</label>
              <input id="regular-form-4" type="text" class="form-control" placeholder="Account" name="account">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-4" class="form-label">Plataforma</label>
              <select class="form-select mt-2 sm:mr-2" aria-label="Default select example" name="ecommerce" required>
                  <option>Selecione a loja</option>
                  <option value="VTEX">VTEX</option>
                  <option value ="Convertize">Convertize</option>
                  <option value ="lojaintegrada">Loja Integrada</option>
              </select>
              <br />
          </div>
          <div class="mt-5">
              <button class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>