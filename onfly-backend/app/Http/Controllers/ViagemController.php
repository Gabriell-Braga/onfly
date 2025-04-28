<?php

namespace App\Http\Controllers;

use App\Models\Viagem;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;
use Carbon\Carbon;

class ViagemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Auth::user()->is_admin
                ? Viagem::query()
                : Viagem::where('user_id', Auth::id());

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('data_ida', [$request->data_inicio, $request->data_fim]);
            } elseif ($request->filled('data_inicio')) {
                $query->where('data_ida', '>=', $request->data_inicio);
            } elseif ($request->filled('data_fim')) {
                $query->where('data_ida', '<=', $request->data_fim);
            }

            if ($request->filled('destino')) {
                $query->where('destino', 'like', '%' . $request->destino . '%');
            }

            return response()->json($query->get());
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar as viagens.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
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
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erro ao salvar a viagem no banco de dados.',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro inesperado ao criar a viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $viagem = Auth::user()->is_admin
                ? Viagem::find($id)
                : Viagem::where('user_id', Auth::id())->find($id);

            if (!$viagem) {
                return response()->json([
                    'message' => 'Viagem não encontrada.'
                ], 404);
            }

            return response()->json($viagem);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar a viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $viagem = Viagem::findOrFail($id);

            if (!Auth::user()->is_admin) {
                return response()->json([
                    'message' => 'Apenas administradores podem alterar o status de pedidos de viagem.'
                ], 403);
            }

            $request->validate([
                'status' => 'required|in:aprovado,cancelado',
            ]);

            if (
                $viagem->status === 'aprovado' &&
                $request->status === 'cancelado' &&
                (Carbon::parse($viagem->data_ida)->isToday() || Carbon::parse($viagem->data_ida)->isPast())
            ) {
                return response()->json([
                    'message' => 'Não é possível cancelar uma viagem aprovada cuja data de ida já passou ou é hoje.'
                ], 400);
            }

            $viagem->status = $request->status;
            $viagem->save();

            Notification::create([
                'user_id' => $viagem->user_id,
                'viagem_id' => $viagem->id,
                'title' => 'Atualização no Pedido '.$viagem->id,
                'message' => 'O status da sua viagem para "' . $viagem->destino . '" foi alterado para "' . ucfirst($request->status) . '".',
                'sent_at' => now(),
            ]);

            return response()->json($viagem);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Viagem não encontrada.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro inesperado ao atualizar status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
