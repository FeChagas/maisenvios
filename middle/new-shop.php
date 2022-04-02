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
              <label for="name" class="form-label">Nome</label>
              <input id="name" type="text" class="form-control" placeholder="Nome" name="name">
              <br />
          </div>
          <div class="mt-2">
              <label for="ecommerce" class="form-label">Plataforma para integrar</label>
              <select id="ecommerce" class="form-select mt-2 sm:mr-2" aria-label="Default select example" name="ecommerce" required>
                  <option>Selecione uma plataforma</option>
                  <option value="VTEX">VTEX</option>
                  <option value ="Convertize">Convertize</option>
                  <option value ="lojaintegrada">Loja Integrada</option>
              </select>
              <br />
          </div>

          <div class="mt-2">
              <label for="account-name" class="form-label">Account</label>
              <input id="account-name" type="text" class="form-control" placeholder="Account" name="account">
              <br />
          </div>
          <div class="mt-2">
              <label for="primary-key" class="form-label">Chave Primaria da loja</label>
              <input id="primary-key" type="text" class="form-control" placeholder="Chave Primaria da loja" name="key_primary">
              <br />
          </div>
          <div class="mt-2">
              <label for="primary-token" class="form-label">Chave Secundaria da loja</label>
              <input id="primary-token" type="text" class="form-control" placeholder="Chave Secundaria da loja" name="token_primary">
              <br />
          </div>

          <div class="mt-2">
              <label id="label-of-integrates_to" for="integrates_to" class="form-label">Plataforma para integrar</label>
              <select id="integrates_to" class="form-select mt-2 sm:mr-2" aria-label="Default select example" name="integrates_to" required>
                  <option>Selecione uma plataforma</option>
                  <option value="SGP">SGP</option>
                  <option value ="MaisEnvios">Mais Envios</option>
              </select>
              <br />
          </div>
          <div id="sgp-auth" class="mt-2 hidden">
              <label for="sgp-key" class="form-label">Chave SGP</label>
              <input id="sgp-key" type="text" class="form-control" placeholder="Chave SGP" name="key_mais">
              <br />
          </div>
          <div id="maisenvios-auth" class="mt-2 hidden">
              <label for="maisenvios-username" class="form-label">Login</label>
              <input id="maisenvios-username" type="text" class="form-control" placeholder="Login do usuário integrador" name="username">
              <label for="maisenvios-password" class="form-label">Senha</label>
              <input id="maisenvios-password" type="password" class="form-control" placeholder="A senha do usuário integrador" name="password">
              <br />
          </div>
          <div class="mt-5">
              <button class="btn btn-primary" type="submit">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>