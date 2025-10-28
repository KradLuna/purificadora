<?php

namespace App\Http\Controllers;

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
        $sales = Sale::with('product')
            ->where('employee_id', $user->id)
            ->whereDate('created_at', today())
            ->latest();

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


        // Promedio histórico por día (de todos los lunes, martes, etc.)
        $promedioHistorico = Sale::select(
            DB::raw('DAYOFWEEK(created_at) as dia_num'),
            DB::raw('DAYNAME(created_at) as dia'),
            DB::raw('AVG(total) as promedio')
        )
            ->groupBy(DB::raw('DAYOFWEEK(created_at)'), DB::raw('DAYNAME(created_at)'))
            ->get()
            ->keyBy('dia');


        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $nombresEsp = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        $datosVentas = [];
        $colores = [];

        foreach ($diasSemana as $i => $diaIngles) {
            $totalDia = $ventasSemana[$diaIngles]->total ?? 0;
            $promedioDia = $promedioHistorico[$diaIngles]->promedio ?? 0;

            $datosVentas[] = round($totalDia, 2);
            // Verde si >= promedio, rojo si menor
            $colores[] = $totalDia >= $promedioDia ? '#28a745' : '#dc3545';
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

        return view('sales.index', [
            'nombresDias' => $nombresEsp,
            'datosVentas' => $datosVentas,
            'coloresDias' => $colores,
            'productos' => $productos,
            'totalesProductos' => $totalesProductos,
            'coloresProductos' => $coloresProductos,
            'ventasPorEmpleadoProcesadas' => $ventasPorEmpleadoProcesadas,
            'ventasSemanaPorEmpleado' => $ventasSemanaPorEmpleado
        ]);
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
        $result = Sale::logicalStore($request->validated());
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422); // código 422: error de validación
        }
        $totalSales = Sale::sumAllDailySales();
        return response()->json(['success' => true, 'totalSales' => $totalSales]);
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
        return redirect()->route('employees-sales.index')
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
        return redirect()->route('employees-sales.index')->with('success', 'Registro eliminado correctamente.');
    }
}
