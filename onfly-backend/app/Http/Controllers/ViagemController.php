<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViagemController extends Controller
{
    public function index(Request $request)
    {
        // Se for admin, começa com todas as viagens; senão, apenas as do próprio usuário
        $query = Auth::user()->is_admin
            ? Viagem::query()
            : Viagem::where('user_id', Auth::id());

        // Filtros opcionais
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has(['data_inicio', 'data_fim'])) {
            $query->whereBetween('data_ida', [$request->data_inicio, $request->data_fim]);
        }

        if ($request->has('destino')) {
            $query->where('destino', 'like', '%' . $request->destino . '%');
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_solicitante' => 'required|string',
            'destino' => 'required|string',
            'data_ida' => 'required|date',
            'data_volta' => 'required|date|after_or_equal:data_ida',
        ]);

        $viagem = Viagem::create([
            'user_id' => Auth::id(),
            'nome_solicitante' => $request->nome_solicitante,
            'destino' => $request->destino,
            'data_ida' => $request->data_ida,
            'data_volta' => $request->data_volta,
            'status' => 'solicitado',
        ]);

        return response()->json($viagem, 201);
    }

    public function show($id)
    {
        $viagem = Auth::user()->is_admin
            ? Viagem::find($id)
            : Viagem::where('user_id', Auth::id())->find($id);

        if (!$viagem) {
            return response()->json([
                'error' => 'Viagem não encontrada.'
            ], 404);
        }

        return response()->json($viagem);
    }

    public function updateStatus(Request $request, $id)
    {
        $viagem = Viagem::findOrFail($id);

        // Verifica se o usuário autenticado é o dono da viagem ou um administrador
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Apenas administradores podem alterar o status de pedidos de viagem.'], 403);
        }


        $request->validate([
            'status' => 'required|in:aprovado,cancelado',
        ]);

        // Regra: Não permitir cancelar um pedido já aprovado com data_ida igual ou anterior a hoje
        if (
            $viagem->status === 'aprovado' &&
            $request->status === 'cancelado' &&
            \Carbon\Carbon::parse($viagem->data_ida)->isTodayOrPast()
        ) {
            return response()->json(['error' => 'Não é possível cancelar uma viagem aprovada cuja data de ida já passou ou é hoje.'], 400);
        }

        $viagem->status = $request->status;
        $viagem->save();

        return response()->json($viagem);
    }

}
