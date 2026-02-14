<?php

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // ✅ PREVENCIÓN: Verificar condiciones antes de permitir acción
        $product = Product::findOrFail($request->product_id);
        
        if ($product->stock < $request->quantity) {
            // ✅ Prevenir orden imposible
            return back()->withErrors([
                'quantity' => "Solo hay {$product->stock} unidades disponibles"
            ])->withInput();
        }
        
        if (!$product->is_available) {
            return back()->withErrors([
                'product' => 'Este producto no está disponible actualmente'
            ]);
        }
        
        // ✅ PREVENCIÓN: Transacción para evitar inconsistencias
        DB::transaction(function () use ($request, $product) {
            $order = Order::create($request->validated());
            $product->decrement('stock', $request->quantity);
        });
        
        return redirect()->route('orders.show', $order);
    }
}