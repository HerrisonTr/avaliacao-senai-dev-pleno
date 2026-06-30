<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response(
        'Esta aplicação disponibiliza uma API. Acesse os endpoints pela rota /api.',
    );
});
