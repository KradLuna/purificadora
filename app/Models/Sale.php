<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'product_id',
        'amount',
        'total',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
    ];


    public static function logicalStore(array $data)
    {
        $errorMsg = "";
        if (!auth()->user()->canDoASale($errorMsg)) {
            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }
        /**
         * para aumentar stock
         * para los productos
         */

        $product = Product::find($data['product_id']);
        if (isset($product->stock) && $product->stock < $data['amount']) {
            return [
                'success' => false,
                'message' => "No hay suficientes insumos para realizar la venta."
            ];
        }
        if ($data['product_id'] >= 20 && $data['product_id'] <= 23) {
            $product->increaseStock($data['amount']);
        }
        $sale = DB::transaction(function () use ($data, $product) {
            $sale = Sale::create([
                'employee_id' => Auth::user()->id,
                'product_id' => $data['product_id'],
                'amount' => $data['amount'],
                'total' => $product->price * $data['amount'],
            ]);
            $sale->product->reduceStock($data['amount']);
            return $sale;
        });
        return [
            'success' => true,
            'sale' => $sale
        ];
    }

    public function logicalUpdate(array $data)
    {
        $this->fill($data);
        $this->total = Product::find($this->product_id)->price * $this->amount;
        $this->save();
    }

    public function sumAllDailySales()
    {
        return Sale::whereDate('created_at', today())
            ->where('employee_id', $this->user->id)
            ->sum('total');
    }



    /**
     * HISTORIC
     */
    public static function historicStore(array $data)
    {
        $sale = Sale::create([
            'employee_id' => $data['employee_id'],
            'product_id' => $data['product_id'],
            'amount' => $data['amount'],
            'total' => $data['total'],
        ]);
        return [
            'success' => true,
            'sale' => $sale
        ];
    }

    /**
     * formateo de fecha
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('Y-m-d H:i')
            : null;
    }

    /**
     * ------------------
     * consultas para obtener datos para graficar:
     * ---------------------------
     */

    /**
     * Ventas Semanales (Ventas históricas semanales) - Excluyendo ventas de garrafones
     */
    public static function weeklySales()
    {
        return DB::table('sales')
            ->selectRaw('YEAR(created_at) AS anio')
            ->selectRaw('WEEK(created_at, 1) AS semana')
            ->selectRaw('SUM(total) AS total_semana')
            ->whereNull('sales.deleted_at')
            ->whereNotIn('sales.product_id', function ($query) {
                $query->select('id')
                    ->from('products')
                    ->where('name', 'like', '%venta%');
            })
            ->where('created_at', '>=', now()->subWeeks(9)->startOfWeek())
            ->groupByRaw('YEAR(created_at), WEEK(created_at, 1)')
            ->orderBy('anio')
            ->orderBy('semana')
            ->get();
    }

    /**
     * ventas semanales de garrafon:
     */
    public static function currentSalesWeek()
    {
        // Ventas Semanales de Garrafones
        return Sale::query()
            ->selectRaw('
                YEAR(sales.created_at)    AS anio,
                WEEK(sales.created_at, 1) AS semana,
                SUM(sales.total)          AS total_vendido
            ')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->whereNull('sales.deleted_at')
            ->whereIn('products.id', [6, 7, 12, 13, 15]) // IDs de garrafones
            ->where('sales.created_at', '>=', now()->subWeeks(9)->startOfWeek())
            ->groupByRaw('YEAR(sales.created_at), WEEK(sales.created_at, 1)')
            ->orderBy('anio')
            ->orderBy('semana')
            ->get()
            ->keyBy(function ($item) {
                return $item->anio . '-' . $item->semana;
            });
    }


    /**
     * pagos semanal omitiendo administradores
     */
    static function weeklyPayments()
    {
        $sub = DB::table('records as e')
            ->select([
                'e.user_id',
                DB::raw('TIMESTAMPDIFF(SECOND, e.created_at, s.created_at) AS diff_seconds')
            ])
            ->leftJoin('records as s', function ($join) {
                $join->on('s.user_id', '=', 'e.user_id')
                    ->where('s.record_type_id', 4)
                    ->whereNull('s.deleted_at')  //filtramos soft deletes en el JOIN
                    ->whereRaw('s.created_at = (
                SELECT MIN(s2.created_at)
                FROM records s2
                WHERE s2.user_id = e.user_id
                  AND s2.record_type_id = 4
                  AND s2.created_at > e.created_at
                  AND s2.deleted_at IS NULL
            )');
            })
            ->where('e.record_type_id', 1)
            ->whereRaw('YEARWEEK(e.created_at, 1) = YEARWEEK(CURDATE(), 1)')
            ->whereNotNull('s.created_at');

        $payment_per_hour = config('constants.payment.per_hour');

        // Obtener IDs de administradores
        $adminIds = User::role('administrador')->pluck('id');

        return DB::table(DB::raw("({$sub->toSql()}) as t"))
            ->mergeBindings($sub)
            ->join('users as U', 'U.id', '=', 't.user_id')
            ->whereNotIn('U.id', $adminIds)
            ->select([
                'U.full_name',
                DB::raw('SEC_TO_TIME(SUM(t.diff_seconds)) AS horas_trabajadas'),
                DB::raw("ROUND(SUM(t.diff_seconds) / 3600 * $payment_per_hour, 2) AS monto_pagado"),
            ])
            ->groupBy('t.user_id', 'U.full_name')
            ->get();
    }

    /**
     * calculo de pagos historicos de empleados omitiendo admins
     * En el return, agrega:
     *     'ultimasSemanas' => $ultimasSemanas,
     *     'pagosPorEmpleadoSemana' => $empleados,
     *     'totalesSemanalesPagos' => $totalesSemanales
     */
    static function historicEmployeesPayments()
    {
        $data = [];
        $numberOfHistoricWeeks = 5;
        // === Pagos por empleado por semana (últimas 9 semanas, excluyendo admins) ===
        $payment_per_hour = config('constants.payment.per_hour');

        // Generar array con las últimas semanas en orden DESCENDENTE (actual primero)
        $ultimasSemanas = collect();
        for ($i = 0; $i <= $numberOfHistoricWeeks; $i++) {
            $fecha = now()->subWeeks($i);
            $inicioSemana = $fecha->copy()->startOfWeek(Carbon::MONDAY);
            $finSemana = $fecha->copy()->endOfWeek(Carbon::SUNDAY);

            $ultimasSemanas->push([
                'anio' => $fecha->year,
                'semana' => $fecha->weekOfYear,
                'inicio' => $inicioSemana,
                'fin' => $finSemana,
                'label' => "S" . $fecha->weekOfYear . " (" . $fecha->year . ")"
            ]);
        }
        // Las semanas ya están en orden descendente porque empezamos desde i=0 (semana actual)

        // Obtener IDs de administradores para excluirlos
        $adminIds = User::role('administrador')->pluck('id');

        // Consulta para obtener pagos por empleado y por semana
        $pagosPorEmpleadoSemana = DB::table('records as e')
            ->select([
                'e.user_id',
                'u.full_name',
                DB::raw('YEAR(e.created_at) as anio'),
                DB::raw('WEEK(e.created_at, 1) as semana'),
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, e.created_at, s.created_at)) AS total_segundos')
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
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->where('e.record_type_id', 1)
            ->whereNotIn('e.user_id', $adminIds) // Excluir admins
            ->whereNotNull('s.created_at')
            ->where('e.created_at', '>=', Carbon::now()->subWeeks($numberOfHistoricWeeks)->startOfWeek())
            ->groupBy('e.user_id', 'u.full_name', DB::raw('YEAR(e.created_at)'), DB::raw('WEEK(e.created_at, 1)'))
            ->orderBy('u.full_name')
            ->orderBy('anio', 'desc')  // Cambiado a descendente
            ->orderBy('semana', 'desc') // Cambiado a descendente
            ->get();

        // Procesar los datos para la tabla
        $empleados = $pagosPorEmpleadoSemana->groupBy('user_id')->map(function ($pagos, $userId) use ($ultimasSemanas, $payment_per_hour) {
            $empleado = [
                'nombre' => $pagos->first()->full_name,
                'pagos_por_semana' => []
            ];

            // Inicializar todas las semanas en 0
            foreach ($ultimasSemanas as $semana) {
                $key = $semana['anio'] . '-' . $semana['semana'];
                $empleado['pagos_por_semana'][$key] = 0;
            }

            // Llenar con los pagos reales
            foreach ($pagos as $pago) {
                $key = $pago->anio . '-' . $pago->semana;
                if (isset($empleado['pagos_por_semana'][$key])) {
                    $horas = $pago->total_segundos / 3600;
                    $empleado['pagos_por_semana'][$key] = round($horas * $payment_per_hour, 2);
                }
            }

            return $empleado;
        });

        // Calcular totales por semana
        $totalesSemanales = [];
        foreach ($ultimasSemanas as $semana) {
            $key = $semana['anio'] . '-' . $semana['semana'];
            $totalesSemanales[$key] = 0;

            foreach ($empleados as $empleado) {
                $totalesSemanales[$key] += $empleado['pagos_por_semana'][$key];
            }
        }

        $data['ultimasSemanas'] = $ultimasSemanas;
        $data['pagosPorEmpleadoSemana'] = $empleados;
        $data['totalesSemanalesPagos'] = $totalesSemanales;

        return $data;
    }

    /**
     * ventas de la semana actual por empleado:
     * 
     */
    static function currentEmployeesSalesWeek()
    {
        $currentEmployeesSalesWeek = [];

        // === Gráfica de Ventas por Día (Semana actual vs histórico) ===
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek   = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $ventasSemanaPorEmpleado = Sale::select(
            'employee_id',
            DB::raw('DAYNAME(created_at) as dia'),
            DB::raw('SUM(total) as total_dia')
        )
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->whereNull('deleted_at')
            ->groupBy('employee_id', DB::raw('DAYNAME(created_at)'), DB::raw('DAYOFWEEK(created_at)'))
            ->orderBy(DB::raw('DAYOFWEEK(created_at)'))
            ->with('user:id,full_name')
            ->get();

        // Obtener todos los empleados únicos con sus nombres
        $empleados = $ventasSemanaPorEmpleado->pluck('user.full_name', 'employee_id')->unique();

        // Crear array con los días de la semana en orden
        $diasSemanaOrden = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];

        // Inicializar array de ventas por día y empleado
        $ventasPorDia = [];
        foreach ($diasSemanaOrden as $diaIngles => $diaEspanol) {
            $ventasPorDia[$diaIngles] = [
                'dia_espanol' => $diaEspanol,
                'ventas' => []
            ];
            foreach ($empleados as $empleadoId => $empleadoNombre) {
                $ventasPorDia[$diaIngles]['ventas'][$empleadoId] = 0;
            }
        }

        // Llenar con las ventas reales
        foreach ($ventasSemanaPorEmpleado as $venta) {
            $dia = $venta->dia;
            $empleadoId = $venta->employee_id;
            if (isset($ventasPorDia[$dia])) {
                $ventasPorDia[$dia]['ventas'][$empleadoId] = $venta->total_dia;
            }
        }

        // Calcular totales por día
        $totalesPorDiaReordenados = [];
        foreach ($diasSemanaOrden as $diaIngles => $diaEspanol) {
            $totalesPorDiaReordenados[$diaIngles] = array_sum($ventasPorDia[$diaIngles]['ventas']);
        }

        // Calcular totales por empleado (para la última fila)
        $totalesPorEmpleado = [];
        foreach ($empleados as $empleadoId => $empleadoNombre) {
            $totalesPorEmpleado[$empleadoId] = 0;
            foreach ($ventasPorDia as $diaData) {
                $totalesPorEmpleado[$empleadoId] += $diaData['ventas'][$empleadoId];
            }
        }
        $currentEmployeesSalesWeek['empleados'] = $empleados;
        $currentEmployeesSalesWeek['ventasPorDia'] = $ventasPorDia;
        $currentEmployeesSalesWeek['diasSemanaOrden'] = $diasSemanaOrden;
        $currentEmployeesSalesWeek['totalesPorDiaReordenados'] = $totalesPorDiaReordenados;
        $currentEmployeesSalesWeek['totalesPorEmpleado'] = $totalesPorEmpleado;
        $currentEmployeesSalesWeek['ventasSemanaPorEmpleado'] = $ventasSemanaPorEmpleado;

        return $currentEmployeesSalesWeek;
    }

    /**
     * RELATIONSHIPS
     */
    /**
     * Get the user that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    /**
     * Get the product that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
