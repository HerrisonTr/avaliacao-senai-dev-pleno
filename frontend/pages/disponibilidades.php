<?php
$pageConfig = [
    'title' => 'Disponibilidades',
    'styles' => [],
    'scripts' => [
        '/assets/js/pages/disponibilidades/disponibilidades.js',
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
                    <h3 class="mb-0">Disponibilidades</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="/pages/dashboard.php">Início</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Disponibilidades</li>
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
                        id="create-availability-button"
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-cadastro-agendamento">
                        Cadastrar agendamento
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Disponibilidades cadastradas</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Atendente</th>
                                <th>Dia da semana</th>
                                <th>Hora inicial</th>
                                <th>Hora final</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="availabilities-table-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Carregando disponibilidades...</td>
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
                <h4 class="modal-title" id="modal-cadastro-agendamento-label">Cadastrar disponibilidade</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="availability-form" novalidate>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="availability-attendant" class="form-label">Atendente *</label>
                            <select id="availability-attendant" name="attendant_id" class="form-select" required>
                                <option value="">Selecione um atendente</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="availability-weekday" class="form-label">Dia da semana *</label>
                            <select id="availability-weekday" name="day_of_week" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="0">Domingo</option>
                                <option value="1">Segunda-feira</option>
                                <option value="2">Terça-feira</option>
                                <option value="3">Quarta-feira</option>
                                <option value="4">Quinta-feira</option>
                                <option value="5">Sexta-feira</option>
                                <option value="6">Sábado</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="availability-start-time" class="form-label">Hora inicial *</label>
                            <input type="text" id="availability-start-time" name="start_time" class="form-control" placeholder="Selecione" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="availability-end-time" class="form-label">Hora final *</label>
                            <input type="text" id="availability-end-time" name="end_time" class="form-control" placeholder="Selecione" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check mt-2">
                                <input type="checkbox" id="availability-active" name="active" value="1" class="form-check-input" checked>
                                <label for="availability-active" class="form-check-label">Ativo?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="availability-form-submit" class="btn btn-primary">Salvar disponibilidade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edicao-agendamento" tabindex="-1" aria-labelledby="modal-edicao-agendamento-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-edicao-agendamento-label">Editar disponibilidade</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="availability-form-edit" novalidate>
                <input type="hidden" id="availability-edit-id" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="availability-edit-attendant" class="form-label">Atendente *</label>
                            <select id="availability-edit-attendant" name="attendant_id" class="form-select" required>
                                <option value="">Selecione um atendente</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="availability-edit-weekday" class="form-label">Dia da semana *</label>
                            <select id="availability-edit-weekday" name="day_of_week" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="0">Domingo</option>
                                <option value="1">Segunda-feira</option>
                                <option value="2">Terça-feira</option>
                                <option value="3">Quarta-feira</option>
                                <option value="4">Quinta-feira</option>
                                <option value="5">Sexta-feira</option>
                                <option value="6">Sábado</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="availability-edit-start-time" class="form-label">Hora inicial *</label>
                            <input type="text" id="availability-edit-start-time" name="start_time" class="form-control" placeholder="Selecione" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="availability-edit-end-time" class="form-label">Hora final *</label>
                            <input type="text" id="availability-edit-end-time" name="end_time" class="form-control" placeholder="Selecione" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check mt-2">
                                <input type="checkbox" id="availability-edit-active" name="active" value="1" class="form-check-input">
                                <label for="availability-edit-active" class="form-check-label">Ativo?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="availability-form-edit-submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
