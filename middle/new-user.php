<div class="grid grid-cols-12 gap-6 mt-5">
  <div class="intro-y col-span-12 lg:col-span-12">
    <div class="intro-y box">
      <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
          <h2 class="font-medium text-base mr-auto">
             Novo usuário 
          </h2>
      </div>
      <div class="p-5">
        <form action="/php/users/new.php" id="new-user">
          <div class="mt-2">
              <label for="regular-form-1" class="form-label">Nome</label>
              <input id="regular-form-1" type="text" class="form-control" placeholder="Nome" name="name">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-2" class="form-label">E-mail</label>
              <input id="regular-form-2" type="text" class="form-control" placeholder="E-mail" name="email">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-3" class="form-label">Senha</label>
              <input id="regular-form-3" type="text" class="form-control" placeholder="Senha" name="password">
              <br />
          </div>
          <div class="mt-2">
              <label for="regular-form-4" class="form-label">Confime a senha</label>
              <input id="regular-form-4" type="text" class="form-control" placeholder="Confirme a senha" name="confirm_password">
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