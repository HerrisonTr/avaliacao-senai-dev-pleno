<?php
$pageConfig = [
    'title' => 'Usuários',
    'styles' => [],
    'scripts' => [
        '/assets/js/pages/usuarios/usuarios.js',
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
                    <h3 class="mb-0">Usuários</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="/pages/dashboard.php">Início</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Usuários</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div id="users-page-feedback" class="alert d-none" role="alert"></div>

            <div class="row mb-4">
                <div class="col-12">
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-cadastro-usuario">
                        Cadastrar usuário
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Usuários cadastrados</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Carregando usuários...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modal-cadastro-usuario" tabindex="-1" aria-labelledby="modal-cadastro-usuario-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-cadastro-usuario-label">Cadastrar usuário</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="user-form" novalidate>
                <div class="modal-body">
                    <div id="user-form-feedback" class="alert d-none" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome *</label>
                            <input type="text" id="nome" name="name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">E-mail *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="perfil" class="form-label">Perfil *</label>
                            <select id="perfil" name="role_id" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="2">Atendente</option>
                                <option value="1">Administrador</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="senha" class="form-label">Senha *</label>
                            <input type="password" id="senha" name="password" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmar-senha" class="form-label">Confirmar senha *</label>
                            <input type="password" id="confirmar-senha" name="password_confirmation" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="user-form-submit" class="btn btn-primary">Salvar usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edicao-usuario" tabindex="-1" aria-labelledby="modal-edicao-usuario-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-edicao-usuario-label">Editar usuário</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="user-form-edit" novalidate>
                <input type="hidden" id="editar-usuario-id" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar-nome" class="form-label">Nome *</label>
                            <input type="text" id="editar-nome" name="name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="editar-email" class="form-label">E-mail *</label>
                            <input type="email" disabled id="editar-email" name="email" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="editar-perfil" class="form-label">Perfil *</label>
                            <select id="editar-perfil" name="role_id" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="2">Atendente</option>
                                <option value="1">Administrador</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="user-form-edit-submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-senha-usuario" tabindex="-1" aria-labelledby="modal-senha-usuario-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-senha-usuario-label">Alterar senha</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="user-form-password" novalidate>
                <input type="hidden" id="senha-usuario-id" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="senha-usuario-nome" class="form-label">Usuário</label>
                            <input type="text" id="senha-usuario-nome" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="nova-senha" class="form-label">Senha *</label>
                            <input type="password" id="nova-senha" name="password" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmar-nova-senha" class="form-label">Confirmar senha *</label>
                            <input type="password" id="confirmar-nova-senha" name="password_confirmation" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="user-form-password-submit" class="btn btn-primary">Salvar nova senha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../include/footer.php'; ?>
