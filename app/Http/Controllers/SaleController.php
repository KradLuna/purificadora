<?php

namespace App\Http\Controllers;

use App\Http\Requests\HistoricSaleRequest;
use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function data()
    {
        $user = auth()->user();
        $errorMsg = '';
        $canDoASale = $user->canDoASale($errorMsg);
        $sales = $user->getCurrentSales(false);

        return DataTables::eloquent($sales)
            ->addColumn('price_unit', fn($sale) => '$' . number_format($sale->product->price, 2))
            ->addColumn('total', fn($sale) => '$' . number_format($sale->amount * $sale->product->price, 2))
            ->editColumn('created_at', fn($sale) => $sale->created_at->format('H:i'))
            ->addColumn('actions', function ($sale) use ($canDoASale) {
                return view('sales.actions', compact('sale', 'canDoASale'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $errorMsg = '';
        $canDoASale = $user->canDoASale($errorMsg);

        $sales = $user->getCurrentSales(true);

        $totalSales = $user->getCurrentTotalSales();

        $products = Product::getActivedProducts();

        $counter = $user->getCurrentLitersCounter();

        return view('sales.index', compact('products', 'sales', 'totalSales', 'counter', 'canDoASale', 'errorMsg'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SaleRequest $request)
    {
        $user = auth()->user();
        $result = Sale::logicalStore($request->validated());
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422); // código 422: error de validación
        }
        $totalSales = $user->getCurrentTotalSales();
        $counter = $user->getCurrentLitersCounter();
        return response()->json(['success' => true, 'totalSales' => $totalSales, 'counter' => $counter]);
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
        $products = Product::getActivedProducts();
        return view('sales.edit', compact('sale', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(SaleRequest $request, Sale $sale)
    {
        $sale->logicalUpdate($request->validated());
        return redirect()->route('sales.index')
            ->with('success', 'Venta actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Registro eliminado correctamente.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function historicStore(HistoricSaleRequest $request)
    {
        $totalSales = Sale::sumAllDailySales();
        return response()->json(['success' => true, 'totalSales' => $totalSales]);
    }

    public function summary()
    {
        $user = auth()->user();

        // Si no es admin, podrías mostrar otra vista o restringir
        if (!$user->isAdmin) {
            return view('dashboard.employee'); //TODO
        }

        // === Gráfica de Ventas por Día (Semana actual vs histórico) ===
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        // Ventas semana actual (lunes a domingo)
        $ventasSemana = Sale::select(
            DB::raw('DAYOFWEEK(created_at) as dia_num'),
            DB::raw('DAYNAME(created_at) as dia'),
            DB::raw('SUM(total) as total')
        )
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy(DB::raw('DAYOFWEEK(created_at)'), DB::raw('DAYNAME(created_at)'))
            ->get()
            ->keyBy('dia');
        // PENDIENTE CREAR LA TABLA PARA LAS HORAS
        $sub = DB::table('records as e')
            ->select([
                'e.user_id',
                DB::raw('TIMESTAMPDIFF(SECOND, e.created_at, s.created_at) AS diff_seconds')
            ])
            ->leftJoin('records as s', function ($join) {
                $join->on('s.user_id', '=', 'e.user_id')
                    ->where('s.record_type_id', 4)
                    ->whereRaw('s.created_at = (
                SELECT MIN(s2.created_at)
                FROM records s2
                WHERE s2.user_id = e.user_id
                  AND s2.record_type_id = 4
                  AND s2.created_at > e.created_at
            )');
            })
            ->where('e.record_type_id', 1)
            ->whereRaw('YEARWEEK(e.created_at, 1) = YEARWEEK(CURDATE(), 1)')
            ->whereNotNull('s.created_at');

        $payment_per_hour = config('constants.payment.per_hour');

        $weeklyPayments = DB::table(DB::raw("({$sub->toSql()}) as t"))
            ->mergeBindings($sub)
            ->join('users as U', 'U.id', '=', 't.user_id')
            ->select([
                'U.full_name',
                DB::raw('SEC_TO_TIME(SUM(t.diff_seconds)) AS horas_trabajadas'),
                DB::raw("ROUND(SUM(t.diff_seconds) / 3600 * $payment_per_hour, 2) AS monto_pagado"),
            ])
            ->groupBy('t.user_id', 'U.full_name')
            ->get();

        // Ventas Semanales (Ventas históricas semanales) // dd($weeklyPayments);
        $weeklySales = DB::table('sales')
            ->selectRaw('YEAR(created_at) AS anio')
            ->selectRaw('WEEK(created_at, 1) AS semana')
            ->selectRaw('SUM(total) AS total_semana')
            ->whereNull('sales.deleted_at')
            ->groupByRaw('YEAR(created_at), WEEK(created_at, 1)')
            ->orderBy('anio')
            ->orderBy('semana')
            ->get();

        $labels = [];
        $data = [];
        $colores = [];

        foreach ($weeklySales as $row) {
            $labels[] = "S{$row->semana} ({$row->anio})";
            $data[] = $row->total_semana;

            // Color aleatorio
            $colores[] = '#' . substr(md5($row->semana . $row->anio), 0, 6);
        }


        // === Gráfica de Ventas por Producto (Semana actual) ===
        $ventasProductos = Sale::select(
            'product_id',
            DB::raw('SUM(total) as total')
        )
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('product_id')
            ->with('product')
            ->get();

        $productos = $ventasProductos->map(fn($v) => $v->product->name);
        $totalesProductos = $ventasProductos->pluck('total');

        // Colores dinámicos para la dona
        $coloresProductos = collect(range(1, $ventasProductos->count()))->map(function () {
            return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        });

        $ventasSemanaPorEmpleado = Sale::select(
            'employee_id',
            DB::raw('DAYNAME(created_at) as dia'),
            DB::raw('SUM(total) as total_dia')
        )
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->whereNull('deleted_at')
            ->groupBy('employee_id', DB::raw('DAYNAME(created_at)'), DB::raw('DAYOFWEEK(created_at)'))
            ->orderBy(DB::raw('DAYOFWEEK(created_at)'))
            ->get();

        // Ventas por empleado del día actual
        $ventasPorEmpleadoProcesadas = $ventasSemanaPorEmpleado->groupBy('employee_id')->map(function ($ventas) {
            $empleado = $ventas->first()->user->full_name ?? 'Sin nombre';
            $dias = [
                'Monday' => 0,
                'Tuesday' => 0,
                'Wednesday' => 0,
                'Thursday' => 0,
                'Friday' => 0,
                'Saturday' => 0,
                'Sunday' => 0
            ];

            foreach ($ventas as $venta) {
                $dias[$venta->dia] = $venta->total_dia;
            }

            return [
                'empleado' => $empleado,
                'ventas' => $dias
            ];
        });

        // Ventas por empleado de la semana actual
        $ventasSemanaPorEmpleado = Sale::select(
            'employee_id',
            DB::raw('SUM(total) as total_semana')
        )
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('employee_id')
            ->with('user:id,full_name')
            ->get();

        $salesPerHour = Sale::selectRaw('HOUR(created_at) AS hora, SUM(total) AS venta')
            ->whereRaw('HOUR(created_at) BETWEEN 9 AND 20')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hora')
            ->get();

        // total
        $totalVentasSemana = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('total');

        return view('sales.summary', [
            'labels' => $labels,
            'data' => $data,
            'colores' => $colores,
            'productos' => $productos,
            'totalesProductos' => $totalesProductos,
            'coloresProductos' => $coloresProductos,
            'ventasPorEmpleadoProcesadas' => $ventasPorEmpleadoProcesadas,
            'ventasSemanaPorEmpleado' => $ventasSemanaPorEmpleado,
            'totalVentasSemana' => $totalVentasSemana,
            'totalPagoHoras' => $weeklyPayments,
            'ventasPorHora' => $salesPerHour
        ]);
    }
}
