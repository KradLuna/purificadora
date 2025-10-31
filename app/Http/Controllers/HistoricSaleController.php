<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoricSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class HistoricSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('historic-sales.index');
    }

    public function data()
    {
        $query = Sale::with(['user', 'product'])
            ->select('sales.*')
            ->orderBy('sales.created_at', 'desc');

        return DataTables::of($query)
            ->addColumn('employee_name', function ($sale) {
                return $sale->user ? $sale->user->full_name : '—';
            })
            ->addColumn('product_name', function ($sale) {
                return $sale->product ? $sale->product->name : '—';
            })
            ->editColumn('created_at', function ($sale) {
                // formateo a dd/mm/yyyy
                return $sale->created_at ? $sale->created_at->format('d/m/Y') : '—';
            })
            ->addColumn('action', function ($row) {
                return view('historic-sales.actions', compact('row'))->render();
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::select('id', 'full_name')->get();
        $products = Product::select('id', 'name', 'price')->get();

        return view('historic-sales.create-edit', compact('users', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HistoricSaleRequest $request)
    {
        Sale::create($request->validated());
        // if (!$result['success']) { //no deberia haber algun error
        //     return response()->json([
        //         'success' => false,
        //         'message' => $result['message'],
        //     ], 422); // código 422: error de validación
        // }
        return view('historic-sales.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $users = User::select('id', 'full_name')->get();
        $products = Product::select('id', 'name', 'price')->get();

        return view('historic-sales.create-edit', compact('sale', 'users', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(HistoricSaleRequest $request, Sale $sale)
    {
        $sale->update($request->validated());
        return view('historic-sales.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        Log::info("destroy", ["sale" => $sale]);
        $sale->delete();
        return redirect()->route('historic-sales.index');
    }
}
