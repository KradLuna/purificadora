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
        //todo: cuando se realizo una venta y es eliminada, hay que regresar el stock original
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
        $numberOfWeeksToPastToCount = 9;

        // Si no es admin, podrías mostrar otra vista o restringir
        if (!$user->isAdmin) {
            return view('dashboard.employee'); //TODO
        }

        // === Gráfica de Ventas por Día (Semana actual vs histórico) ===
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $currentEmployeesSalesWeek = Sale::currentEmployeesSalesWeek();

        // Ventas semana historicas (lunes a domingo)
        $ventasPorDiaSemana = Sale::query()
            ->selectRaw('
                DAYOFWEEK(created_at) AS dia_numero,
                DAYNAME(created_at)   AS dia_nombre,
                SUM(total)            AS total_dia
                ')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subWeeks($numberOfWeeksToPastToCount)->startOfWeek())
            ->groupByRaw('DAYOFWEEK(created_at), DAYNAME(created_at)')
            ->orderBy('dia_numero')
            ->get();

        $diasSemana = [
            2 => 'Lunes',
            3 => 'Martes',
            4 => 'Miércoles',
            5 => 'Jueves',
            6 => 'Viernes',
            7 => 'Sabado',
            1 => 'Domingo',
        ];

        $labelsDias = [];
        $dataDias   = [];

        foreach ($diasSemana as $num => $nombre) {
            $row = $ventasPorDiaSemana->firstWhere('dia_numero', $num);

            $labelsDias[] = __($nombre); // opcional traducción
            $dataDias[]   = $row ? (float) $row->total_dia : 0;
        }

        $weeklyPayments = Sale::weeklyPayments();

        // Ventas Semanales (Ventas históricas semanales)
        $weeklySales = Sale::weeklySales();

        $labels = [];
        $data = [];

        foreach ($weeklySales as $row) {
            $labels[] = "S{$row->semana} ({$row->anio})";
            $data[] = $row->total_semana;
        }

        // Colores dinámicos para las graficas
        $coloresProductos = collect(range(1, 25))->map(function () {
            return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        });


        /**
         * ventas historicas por hora
         */
        $salesPerHour = Sale::selectRaw('HOUR(created_at) AS hora, SUM(total) AS venta')
            ->whereRaw('HOUR(created_at) BETWEEN 10 AND 20')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hora')
            ->get();

        // total
        $totalVentasSemana = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('total');
        // ventas semana actual - DONA GRAFICA 2
        $ventasProductos = Sale::query()
            ->select([
                'products.id as product_id',
                'products.name as producto',
                DB::raw('SUM(sales.amount) as cantidad_vendida'),
                DB::raw('SUM(sales.total) as total_vendido'),
            ])
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->whereBetween('sales.created_at', [$startOfWeek, $endOfWeek])
            ->whereNull('sales.deleted_at')
            ->where('products.price', '>', 1)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_vendido')
            ->get();

        //ventas historicas por producto - DONA GRAFICA 4
        $globalSalesPerProduct = Sale::query()
            ->select([
                'products.id as product_id',
                'products.name as producto',
                DB::raw('SUM(sales.amount) as cantidad_vendida'),
                DB::raw('SUM(sales.total) as total_vendido'),
            ])
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->whereNull('sales.deleted_at')
            ->where('products.price', '>', 1)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_vendido')
            ->get();

        /**
         * preparando ventas semanales de garrafones
         */

        // Primero, genera un array con las últimas 9 semanas
        $semanas = collect();
        for ($i = 9; $i >= 0; $i--) {
            $fecha = Carbon::now()->subWeeks($i);
            $semanas->push([
                'anio' => $fecha->year,
                'semana' => $fecha->weekOfYear,
                'label' => "S" . $fecha->weekOfYear . " (" . $fecha->year . ")"
            ]);
        }
        $ventasSemanalesGarrafones = Sale::currentSalesWeek();

        $labelsSemanas = [];
        $totalesSemana = [];

        foreach ($semanas as $semana) {
            $key = $semana['anio'] . '-' . $semana['semana'];
            $labelsSemanas[] = $semana['label'];

            // Si existe venta en esa semana, tomar el valor, sino 0
            $totalesSemana[] = isset($ventasSemanalesGarrafones[$key])
                ? (float) $ventasSemanalesGarrafones[$key]->total_vendido
                : 0;
        }

        /**
         * calculo de pagos historicos de empleados omitiendo admins
         */
        $historicEmployeesPayments = Sale::historicEmployeesPayments();
        return view('sales.summary', [
            'labels' => $labels,
            'data' => $data,
            // 'productos' => $productos,
            'ventasProductos' => $ventasProductos,
            'coloresProductos' => $coloresProductos,
            'totalVentasSemana' => $totalVentasSemana,
            'totalPagoHoras' => $weeklyPayments,
            'ventasPorHora' => $salesPerHour,
            'ventasProductosGlobal' => $globalSalesPerProduct,
            'labelsDias' => $labelsDias,
            'dataDias' => $dataDias,
            'ventasSemanalesGarrafones' => $ventasSemanalesGarrafones,
            'labelsSemanasProd'     => $labelsSemanas,
            'totalesSemanasProd'   => $totalesSemana,
            'ultimasSemanas' => $historicEmployeesPayments['ultimasSemanas'],
            'pagosPorEmpleadoSemana' => $historicEmployeesPayments['pagosPorEmpleadoSemana'],
            'totalesSemanalesPagos' => $historicEmployeesPayments['totalesSemanalesPagos'],
            'empleados' => $currentEmployeesSalesWeek['empleados'],
            'ventasPorDia' => $currentEmployeesSalesWeek['ventasPorDia'],
            'diasSemanaOrden' => $currentEmployeesSalesWeek['diasSemanaOrden'],
            'totalesPorDiaReordenados' => $currentEmployeesSalesWeek['totalesPorDiaReordenados'],
            'totalesPorEmpleado' => $currentEmployeesSalesWeek['totalesPorEmpleado'],
            'ventasSemanaPorEmpleado' => $currentEmployeesSalesWeek['ventasSemanaPorEmpleado'],
        ]);
    }
}
