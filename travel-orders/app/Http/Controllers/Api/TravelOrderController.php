<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelOrder;
use Illuminate\Http\Request;

class TravelOrderController extends Controller
{
public function index(Request $request)
{
    $query = auth()->user()->is_admin
        ? TravelOrder::query()
        : TravelOrder::where('user_id', auth()->id());

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('destination')) {
        $query->where('destination', 'like', '%' . $request->destination . '%');
    }

    if ($request->filled('date_from')) {
        $query->where('departure_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->where('departure_date', '<=', $request->date_to);
    }

    return response()->json($query->orderBy('created_at', 'desc')->get());
}

    public function store(Request $request)
    {
        $request->validate([
            'destination'    => 'required|string|max:255',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date'    => 'required|date|after_or_equal:departure_date',
        ]);

        $order = TravelOrder::create([
            'user_id'        => auth()->id(),
            'requester_name' => auth()->user()->name,
            'destination'    => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date'    => $request->return_date,
            'status'         => 'solicitado',
        ]);

        return response()->json([
            'message' => 'Pedido criado com sucesso!',
            'order'   => $order,
        ], 201);
    }

public function show(string $id)
{
    $query = auth()->user()->is_admin
        ? TravelOrder::query()
        : TravelOrder::where('user_id', auth()->id());

    $order = $query->findOrFail($id);

    return response()->json($order);
}
public function updateStatus(Request $request, string $id)
{
    $request->validate([
        'status' => 'required|in:aprovado,cancelado',
    ]);

    $order = TravelOrder::findOrFail($id);

    if (!auth()->user()->is_admin) {
        return response()->json([
            'message' => 'Apenas administradores podem alterar o status.',
        ], 403);
    }

    if ($order->status !== 'solicitado') {
        return response()->json([
            'message' => 'Apenas pedidos com status "solicitado" podem ser atualizados.',
        ], 422);
    }

    $order->update(['status' => $request->status]);

    $order->user->notify(new \App\Notifications\TravelOrderStatusChanged($order));

    return response()->json([
        'message' => 'Status atualizado com sucesso!',
        'order'   => $order,
    ]);
}

    public function destroy(string $id)
    {
        $order = TravelOrder::where('user_id', auth()->id())
                            ->findOrFail($id);

        if ($order->status !== 'solicitado') {
            return response()->json([
                'message' => 'Apenas pedidos com status "solicitado" podem ser excluídos.',
            ], 422);
        }

        $order->delete();

        return response()->json([
            'message' => 'Pedido excluído com sucesso!',
        ]);
    }

    public function update(Request $request, string $id)
    {
        return response()->json(['message' => 'Use a rota PATCH /status para atualizar o status.'], 405);
    }
}
