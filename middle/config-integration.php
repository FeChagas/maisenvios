<div class="grid grid-cols-12 gap-6 mt-5">
  <div class="intro-y col-span-12 lg:col-span-12">
    <div class="intro-y box">
      <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
          <h2 class="font-medium text-base mr-auto">
             Configurações da integração
          </h2>
      </div>
      <div class="p-5">
        <a href="javascript:history.back()">
          <div id="plataform-not-supported-alert" class="alert show box bg-primary text-white flex items-center mb-6 hidden">
              <h5 class="text-lg font-medium leading-none mt-3">Desculpe, as configurações para essa plataforma ainda não são editáveis. Clique aqui para voltar.</h5>
          </div>
        </a>
        <form action="/php/shop_meta/edit.php" id="integration-config-form">
        <div id="VTEX" class="hidden">
            <div class="mt-6">
                <label> <strong> Status de pedido a serem integrados</strong> <a class="text-primary block font-normal" href="https://help.vtex.com/pt/tutorial/tabela-de-status-de-pedidos-oms--frequentlyAskedQuestions_773" target="_blank">(Clique aqui para saber mais)</a></label>
                <div class='form-check mt-2'><input id='order-created' class='form-check-input' type='checkbox' value='order-created' name='vtex_order_status'><label class='form-check-label'for='order-created'>Processando (order-created)</label></div>
                <div class='form-check mt-2'><input id='on-order-completed' class='form-check-input' type='checkbox' value='on-order-completed' name='vtex_order_status'><label class='form-check-label'for='on-order-completed'>Processando (on-order-completed)</label></div>
                <div class='form-check mt-2'><input id='payment-pending' class='form-check-input' type='checkbox' value='payment-pending' name='vtex_order_status'><label class='form-check-label'for='payment-pending'>Pagamento Pendente (payment-pending)</label></div>
                <div class='form-check mt-2'><input id='waiting-for-order-authorization' class='form-check-input' type='checkbox' value='waiting-for-order-authorization' name='vtex_order_status'><label class='form-check-label'for='waiting-for-order-authorization'>Aguardando Autorização do Pedido (waiting-for-order-authorization)</label></div>
                <div class='form-check mt-2'><input id='approve-payment' class='form-check-input' type='checkbox' value='approve-payment' name='vtex_order_status'><label class='form-check-label'for='approve-payment'>Preparando entrega (approve-payment)</label></div>
                <div class='form-check mt-2'><input id='payment-approved' class='form-check-input' type='checkbox' value='payment-approved' name='vtex_order_status'><label class='form-check-label'for='payment-approved'>Pagamento Aprovado (payment-approved)</label></div>
                <div class='form-check mt-2'><input id='request-cancel' class='form-check-input' type='checkbox' value='request-cancel' name='vtex_order_status'><label class='form-check-label'for='request-cancel'>Solicitar cancelamento (request-cancel)</label></div>
                <div class='form-check mt-2'><input id='waiting-for-seller-decision' class='form-check-input' type='checkbox' value='waiting-for-seller-decision' name='vtex_order_status'><label class='form-check-label'for='waiting-for-seller-decision'>Aguardando decisão do Seller (waiting-for-seller-decision)</label></div>
                <div class='form-check mt-2'><input id='authorize-fulfillment' class='form-check-input' type='checkbox' value='authorize-fulfillment' name='vtex_order_status'><label class='form-check-label'for='authorize-fulfillment'>Aguardando autorização para despachar (authorize-fulfillment)</label></div>
                <div class='form-check mt-2'><input id='order-create-error' class='form-check-input' type='checkbox' value='order-create-error' name='vtex_order_status'><label class='form-check-label'for='order-create-error'>Erro na criação do pedido (order-create-error)</label></div>
                <div class='form-check mt-2'><input id='order-creation-error' class='form-check-input' type='checkbox' value='order-creation-error' name='vtex_order_status'><label class='form-check-label'for='order-creation-error'>Erro na criação do pedido (order-creation-error)</label></div>
                <div class='form-check mt-2'><input id='window-to-cancel' class='form-check-input' type='checkbox' value='window-to-cancel' name='vtex_order_status'><label class='form-check-label'for='window-to-cancel'>Carência para Cancelamento (window-to-cancel)</label></div>
                <div class='form-check mt-2'><input id='ready-for-handling' class='form-check-input' type='checkbox' value='ready-for-handling' name='vtex_order_status'><label class='form-check-label'for='ready-for-handling'>Pronto para o Manuseio (ready-for-handling)</label></div>
                <div class='form-check mt-2'><input id='start-handling' class='form-check-input' type='checkbox' value='start-handling' name='vtex_order_status'><label class='form-check-label'for='start-handling'>Iniciar Manuseio (start-handling)</label></div>
                <div class='form-check mt-2'><input id='handling' class='form-check-input' type='checkbox' value='handling' name='vtex_order_status'><label class='form-check-label'for='handling'>Preparando Entrega (handling)</label></div>
                <div class='form-check mt-2'><input id='invoice-after-cancellation-deny' class='form-check-input' type='checkbox' value='invoice-after-cancellation-deny' name='vtex_order_status'><label class='form-check-label'for='invoice-after-cancellation-deny'>Fatura pós-cancelamento negado (invoice-after-cancellation-deny)</label></div>
                <div class='form-check mt-2'><input id='order-accepted' class='form-check-input' type='checkbox' value='order-accepted' name='vtex_order_status'><label class='form-check-label'for='order-accepted'>Verificando Envio (order-accepted)</label></div>
                <div class='form-check mt-2'><input id='invoice' class='form-check-input' type='checkbox' value='invoice' name='vtex_order_status'><label class='form-check-label'for='invoice'>Enviando (invoice)</label></div>
                <div class='form-check mt-2'><input id='invoiced' class='form-check-input' type='checkbox' value='invoiced' name='vtex_order_status'><label class='form-check-label'for='invoiced'>Faturado (invoiced)</label></div>
                <div class='form-check mt-2'><input id='replaced' class='form-check-input' type='checkbox' value='replaced' name='vtex_order_status'><label class='form-check-label'for='replaced'>Substituído (replaced)</label></div>
                <div class='form-check mt-2'><input id='cancellation-requested' class='form-check-input' type='checkbox' value='cancellation-requested' name='vtex_order_status'><label class='form-check-label'for='cancellation-requested'>Cancelamento solicitado (cancellation-requested)</label></div>
                <div class='form-check mt-2'><input id='cancel' class='form-check-input' type='checkbox' value='cancel' name='vtex_order_status'><label class='form-check-label'for='cancel'>Cancelar (cancel)</label></div>
                <div class='form-check mt-2'><input id='canceled' class='form-check-input' type='checkbox' value='canceled' name='vtex_order_status'><label class='form-check-label'for='canceled'>Cancelado (canceled)</label></div>
            </div>
            <div class="mt-6">
                <label><strong>Etapas da integração</strong></label>
                <div class="flex flex-col sm:flex-row mt-2">
                    <div class="form-check mr-2">
                        <input id="vtex_order_feed" class="form-check-input" type="checkbox" value="vtex_order_feed" name="vtex_integration_step">
                        <label class="form-check-label" for="vtex_order_feed">Listar pedidos da VTEX</label>
                    </div>
                    <div class="form-check mr-2 mt-2 sm:mt-0">
                        <input id="sgp_pre_post" class="form-check-input" type="checkbox" value="sgp_pre_post" name="vtex_integration_step">
                        <label class="form-check-label" for="sgp_pre_post">Realizer Pré Postagem</label>
                    </div>
                    <div class="form-check mr-2 mt-2 sm:mt-0">
                        <input id="vtex_tracking_update" class="form-check-input" type="checkbox" value="vtex_tracking_update" name="vtex_integration_step">
                        <label class="form-check-label" for="vtex_tracking_update">Enviar código de rastreio para a VTEX</label>
                    </div>
                    <div class="form-check mr-2 mt-2 sm:mt-0">
                        <input id="vtex_call_endpoint" class="form-check-input" type="checkbox" value="vtex_call_endpoint" name="vtex_integration_step">
                        <label class="form-check-label" for="vtex_call_endpoint">Chama o callback do cliente para pedidos já integrados</label>
                    </div>
                </div>
            </div>

              <div class="mt-6">
                <label for="vtex_endpoint_to_call" class="form-label">Endpoint do cliente</label>
                <input disabled id="vtex_endpoint_to_call" type="text" class="form-control" placeholder="https://sistema.com/endpoint" name="vtex_endpoint_to_call">
            </div>
            <div class="mt-5">
                <button class="btn btn-primary" type="submit">Salvar</button>
            </div>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>