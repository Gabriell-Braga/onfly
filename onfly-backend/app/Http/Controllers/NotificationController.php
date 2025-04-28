<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Listar notificações do usuário
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())->orderBy('sent_at', 'desc')->get();
        return response()->json($notifications);
    }

    // Marcar uma notificação como lida
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $notification->update(['read' => true]);
        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    // Deletar uma notificação
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete();
        return response()->json(['message' => 'Notificação deletada']);
    }
}

