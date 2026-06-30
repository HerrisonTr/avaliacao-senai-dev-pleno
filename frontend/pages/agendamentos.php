<?php
$pageConfig = [
    'title' => 'Agendamentos',
    'styles' => [],
    'scripts' => [
        '/assets/js/pages/agendamentos/agendamentos.js',
    ],
];
?>

<?php require __DIR__ . '/../include/header.php'; ?>
<?php require __DIR__ . '/../include/sidebar.php'; ?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Agendamentos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="/pages/dashboard.php">Início</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Agendamentos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <button
                        id="create-appointment-button"
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-cadastro-agendamento">
                        Cadastrar agendamento
                    </button>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Calendário de agendamentos</h3>
                </div>
                <div class="card-body">
                    <div id="appointments-calendar"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Agendamentos cadastrados</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Atendente</th>
                                <th>Serviço</th>
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="appointments-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Carregando agendamentos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modal-cadastro-agendamento" tabindex="-1" aria-labelledby="modal-cadastro-agendamento-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-cadastro-agendamento-label">Cadastrar agendamento</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="appointment-form" novalidate>
                <div class="modal-body">
                    <div id="appointment-occupied-times" class="alert alert-warning d-none mb-3" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="appointment-attendant" class="form-label">Atendente *</label>
                            <select id="appointment-attendant" name="attendant_id" class="form-select" required>
                                <option value="">Selecione um atendente</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-service" class="form-label">Serviço *</label>
                            <select id="appointment-service" name="service_id" class="form-select" required>
                                <option value="">Selecione um serviço</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="appointment-date" class="form-label">Data *</label>
                            <input type="date" id="appointment-date" name="appointment_date" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-start-time" class="form-label">Horário de início *</label>
                            <select id="appointment-start-time" name="start_time" class="form-select" required>
                                <option value="">Selecione um horário</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="appointment-end-time" class="form-label">Horário de fim *</label>
                            <select id="appointment-end-time" name="end_time" class="form-select" required>
                                <option value="">Selecione um horário</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <label for="appointment-customer-name" class="form-label">Nome do cliente *</label>
                            <input type="text" id="appointment-customer-name" name="customer_name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-customer-phone" class="form-label">Telefone *</label>
                            <input type="text" id="appointment-customer-phone" name="customer_phone" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-customer-email" class="form-label">E-mail</label>
                            <input type="email" id="appointment-customer-email" name="customer_email" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="appointment-form-submit" class="btn btn-primary">Salvar agendamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edicao-agendamento" tabindex="-1" aria-labelledby="modal-edicao-agendamento-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-edicao-agendamento-label">Editar agendamento</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="appointment-form-edit" novalidate>
                <input type="hidden" id="appointment-edit-id" name="id">
                <div class="modal-body">
                    <div id="appointment-edit-occupied-times" class="alert alert-warning d-none mb-0" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="appointment-edit-attendant" class="form-label">Atendente *</label>
                            <select id="appointment-edit-attendant" name="attendant_id" class="form-select" required>
                                <option value="">Selecione um atendente</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-edit-service" class="form-label">Serviço *</label>
                            <select id="appointment-edit-service" name="service_id" class="form-select" required>
                                <option value="">Selecione um serviço</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="appointment-edit-date" class="form-label">Data *</label>
                            <input type="date" id="appointment-edit-date" name="appointment_date" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-edit-start-time" class="form-label">Horário de início *</label>
                            <select id="appointment-edit-start-time" name="start_time" class="form-select" required>
                                <option value="">Selecione um horário</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-edit-end-time" class="form-label">Horário de fim *</label>
                            <select id="appointment-edit-end-time" name="end_time" class="form-select" required>
                                <option value="">Selecione um horário</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="appointment-edit-customer-name" class="form-label">Nome do cliente *</label>
                            <input type="text" id="appointment-edit-customer-name" name="customer_name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-edit-customer-phone" class="form-label">Telefone *</label>
                            <input type="text" id="appointment-edit-customer-phone" name="customer_phone" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment-edit-customer-email" class="form-label">E-mail</label>
                            <input type="email" id="appointment-edit-customer-email" name="customer_email" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="appointment-form-edit-submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-visualizacao-agendamento" tabindex="-1" aria-labelledby="modal-visualizacao-agendamento-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-visualizacao-agendamento-label">Visualizar agendamento</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="appointment-view-customer-name" class="form-label">Cliente</label>
                        <input type="text" id="appointment-view-customer-name" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-status" class="form-label">Status</label>
                        <input type="text" id="appointment-view-status" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-attendant" class="form-label">Atendente</label>
                        <input type="text" id="appointment-view-attendant" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-service" class="form-label">Serviço</label>
                        <input type="text" id="appointment-view-service" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-date" class="form-label">Data</label>
                        <input type="text" id="appointment-view-date" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-time" class="form-label">Hora</label>
                        <input type="text" id="appointment-view-time" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-customer-phone" class="form-label">Telefone</label>
                        <input type="text" id="appointment-view-customer-phone" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="appointment-view-customer-email" class="form-label">E-mail</label>
                        <input type="text" id="appointment-view-customer-email" class="form-control" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-status-agendamento" tabindex="-1" aria-labelledby="modal-status-agendamento-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-status-agendamento-label">Atualizar status</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="appointment-status-form" novalidate>
                <input type="hidden" id="appointment-status-id" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="appointment-status-customer-name" class="form-label">Cliente</label>
                            <input type="text" id="appointment-status-customer-name" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <label for="appointment-status-current" class="form-label">Status atual</label>
                            <input type="text" id="appointment-status-current" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <label for="appointment-status-new" class="form-label">Novo status *</label>
                            <select id="appointment-status-new" name="status" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="completed">Concluído</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="appointment-status-submit" class="btn btn-primary">Salvar status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>