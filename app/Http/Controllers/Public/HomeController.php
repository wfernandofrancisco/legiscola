<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

/**
 * Formulários legados na landing institucional (apex).
 */
class HomeController extends Controller
{
    public function contact()
    {
        // TODO: integrar disparo ao time comercial quando houver formulário na landing apex
        return back()->with('success', 'Mensagem enviada com sucesso!');
    }
}
