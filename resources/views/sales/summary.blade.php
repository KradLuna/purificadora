@extends('adminlte::page')

@section('title', 'Resumen de Ventas')

@section('content_header')
    <x-header-section title="Resumen de Ventas" />
@stop

@section('content')

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-chart-line me-2"></i>
                        Ventas por empleado - Semana actual
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="bg-dark text-white text-center">
                                <tr>
                                    <th style="background-color: #2c3e50;">Día</th>
                                    @foreach ($empleados as $empleadoId => $empleadoNombre)
                                        <th style="background-color: #34495e;">{{ $empleadoNombre }}</th>
                                    @endforeach
                                    <th style="background-color: #2c3e50;">Total del Día</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diasSemanaOrden as $diaIngles => $diaEspanol)
                                    <tr>
                                        <td class="fw-bold" style="background-color: #f8f9fa;">
                                            <i class="fas fa-calendar-day me-1"></i> {{ $diaEspanol }}
                                        </td>
                                        @foreach ($empleados as $empleadoId => $empleadoNombre)
                                            <td class="text-end fw-semibold">
                                                ${{ number_format($ventasPorDia[$diaIngles]['ventas'][$empleadoId] ?? 0, 2) }}
                                            </td>
                                        @endforeach
                                        <td class="text-end fw-bold" style="background-color: #e7f3ff; color: #0066cc;">
                                            ${{ number_format($totalesPorDiaReordenados[$diaIngles] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="background-color: #f8f9fa;">
                                <tr class="fw-bold">
                                    <td class="text-end" style="background-color: #2c3e50; color: white;">TOTAL POR
                                        EMPLEADO:</td>
                                    @foreach ($empleados as $empleadoId => $empleadoNombre)
                                        <td class="text-end" style="background-color: #e7f3ff;">
                                            ${{ number_format($totalesPorEmpleado[$empleadoId] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                    <td class="text-end" style="background-color: #0066cc; color: white;">
                                        ${{ number_format(array_sum($totalesPorDiaReordenados), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php $auxTotal = 0.0; @endphp
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Pago por empleado - Semana actual
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #2c3e50;">
                                <tr>
                                    <th class="text-white"><i class="fas fa-user me-1"></i> Empleado</th>
                                    <th class="text-end text-white"><i class="fas fa-clock me-1"></i> Horas Trabajadas</th>
                                    <th class="text-end text-white"><i class="fas fa-money-bill-wave me-1"></i> Monto a
                                        Pagar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totalPagoHoras as $pago)
                                    @php $auxTotal += (float) $pago->monto_pagado; @endphp
                                    <tr style="background-color: #f8f9fa; border-left: 3px solid #11998e;">
                                        <td class="fw-bold">
                                            <i class="fas fa-user-circle text-success me-1"></i>
                                            {{ $pago->full_name ?? 'Desconocido' }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            {{ $pago->horas_trabajadas }} hrs
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            ${{ number_format($pago->monto_pagado, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-1"></i> Sin pagos en la semana
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot style="background-color: #e7f3ff;">
                                <tr class="fw-bold">
                                    <td class="text-end" style="background-color: #11998e; color: white;">TOTAL GENERAL:
                                    </td>
                                    <td style="background-color: #11998e;"></td>
                                    <td class="text-end" style="background-color: #0d7a6e; color: white; font-size: 1.1em;">
                                        ${{ number_format($auxTotal, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-history me-2"></i>
                        Pagos por empleado - Últimas {{ count($ultimasSemanas) }} semanas
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead style="background-color: #2c3e50;">
                                <tr class="text-center">
                                    <th style="background-color: #2c3e50; color: white;"><i class="fas fa-user me-1"></i>
                                        Empleado</th>
                                    @foreach ($ultimasSemanas as $semana)
                                        <th style="background-color: #34495e; color: white;">
                                            <i class="fas fa-calendar-week me-1"></i> {{ $semana['label'] }}
                                        </th>
                                    @endforeach
                                    <th style="background-color: #2c3e50; color: white;"><i
                                            class="fas fa-chart-line me-1"></i> Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pagosPorEmpleadoSemana as $empleado)
                                    @php $totalEmpleado = 0; @endphp
                                    <tr style="border-left: 3px solid #667eea;">
                                        <td class="fw-bold" style="background-color: #f8f9fa;">
                                            <i class="fas fa-user-circle text-primary me-1"></i> {{ $empleado['nombre'] }}
                                        </td>
                                        @foreach ($ultimasSemanas as $semana)
                                            @php
                                                $key = $semana['anio'] . '-' . $semana['semana'];
                                                $monto = $empleado['pagos_por_semana'][$key] ?? 0;
                                                $totalEmpleado += $monto;
                                                $bgColor = $loop->iteration % 2 == 0 ? '#ffffff' : '#f8f9fa';
                                            @endphp
                                            <td class="text-end fw-semibold"
                                                style="background-color: {{ $bgColor }};">
                                                ${{ number_format($monto, 2) }}
                                            </td>
                                        @endforeach
                                        <td class="text-end fw-bold" style="background-color: #e7f3ff; color: #0066cc;">
                                            ${{ number_format($totalEmpleado, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($ultimasSemanas) + 2 }}" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-1"></i> No hay datos de pagos en las últimas
                                            semanas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot style="background-color: #f8f9fa;">
                                <tr class="fw-bold">
                                    <td class="text-end" style="background-color: #667eea; color: white;">TOTAL POR SEMANA:
                                    </td>
                                    @foreach ($ultimasSemanas as $semana)
                                        @php
                                            $key = $semana['anio'] . '-' . $semana['semana'];
                                            $totalSemana = $totalesSemanalesPagos[$key] ?? 0;
                                        @endphp
                                        <td class="text-end" style="background-color: #e7f3ff;">
                                            ${{ number_format($totalSemana, 2) }}
                                        </td>
                                    @endforeach
                                    <td class="text-end"
                                        style="background-color: #764ba2; color: white; font-size: 1.05em;">
                                        ${{ number_format(array_sum($totalesSemanalesPagos), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-chart-line me-2"></i>
                        Resumen de Ventas por Producto - Últimas {{ count($semanasFormateadas) }} semanas
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="bg-dark text-white text-center">
                                <tr>
                                    <th style="background-color: #2c3e50;">Concepto</th>
                                    @foreach ($semanasFormateadas as $semana)
                                        <th style="background-color: #34495e;">{{ $semana['label'] }}</th>
                                    @endforeach
                                    <th style="background-color: #2c3e50;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Ventas Parciales (Grupo 1) --}}
                                <tr class="table-active" style="background-color: #e7f3ff;">
                                    <td class="fw-bold text-primary fs-5">
                                        <i class="fas fa-calculator me-1"></i></i> Ventas
                                    </td>
                                    @php $totalParciales = 0; @endphp
                                    @foreach ($semanasFormateadas as $semana)
                                        @php
                                            $monto = $datosPorSemana[$semana['key']]['Ventas Parciales'] ?? 0;
                                            $totalParciales += $monto;
                                        @endphp
                                        <td class="text-end fw-semibold">${{ number_format($monto, 2) }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-warning bg-opacity-10">
                                        ${{ number_format($totalParciales, 2) }}</td>
                                </tr>

                                {{-- Garrafones (Grupo 2) --}}
                                <tr style="background-color: #d1ecf1;">
                                    <td class="fw-bold text-info">
                                        <i class="fas fa-fill-drip me-1"></i> Garrafones
                                    </td>
                                    @php $totalGarrafones = 0; @endphp
                                    @foreach ($semanasFormateadas as $semana)
                                        @php
                                            $monto = $datosPorSemana[$semana['key']]['Garrafones'] ?? 0;
                                            $totalGarrafones += $monto;
                                        @endphp
                                        <td class="text-end">${{ number_format($monto, 2) }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-info bg-opacity-10">
                                        ${{ number_format($totalGarrafones, 2) }}</td>
                                </tr>

                                {{-- Hielo (Grupo 3) --}}
                                <tr style="background-color: #d4edda;">
                                    <td class="fw-bold text-success">
                                        <i class="fas fa-ice-cream me-1"></i> Hielo
                                    </td>
                                    @php $totalHielo = 0; @endphp
                                    @foreach ($semanasFormateadas as $semana)
                                        @php
                                            $monto = $datosPorSemana[$semana['key']]['Hielo'] ?? 0;
                                            $totalHielo += $monto;
                                        @endphp
                                        <td class="text-end">${{ number_format($monto, 2) }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-success bg-opacity-10">
                                        ${{ number_format($totalHielo, 2) }}</td>
                                </tr>

                                {{-- Ventas Netas
                                <tr class="table-active" style="background-color: #e7f3ff;">
                                    <td class="fw-bold text-primary fs-5">
                                        <i class="fas fa-calculator me-1"></i> VENTAS NETAS
                                    </td>
                                    @php $totalNeto = 0; @endphp
                                    @foreach ($semanasFormateadas as $semana)
                                        @php
                                            $monto = $ventasNetasPorSemana[$semana['key']] ?? 0;
                                            $totalNeto += $monto;
                                        @endphp
                                        <td class="text-end fw-bold text-primary">${{ number_format($monto, 2) }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-primary text-white">${{ number_format($totalNeto, 2) }}
                                    </td>
                                </tr> --}}
                            </tbody>
                            <tfoot style="background-color: #f8f9fa;">
                                <tr class="fw-bold">
                                    <td class="text-end bg-secondary text-white">TOTAL POR SEMANA:</td>
                                    @foreach ($semanasFormateadas as $semana)
                                        @php
                                            $totalSemana = $totalesSemanales[$semana['key']] ?? 0;
                                        @endphp
                                        <td class="text-end bg-secondary bg-opacity-10">
                                            ${{ number_format($totalSemana, 2) }}</td>
                                    @endforeach
                                    @php
                                        $granTotal = array_sum($totalesSemanales);
                                    @endphp
                                    <td class="text-end bg-dark text-white">${{ number_format($granTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfica 2: Ventas por Producto -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient"
                    style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-chart-pie me-2"></i>
                            Ventas semana actual por producto
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-chart-simple"></i> Tipo
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"
                                        onclick="changeChartType('doughnut')">Dona</a></li>
                                <li><a class="dropdown-item" href="#" onclick="changeChartType('pie')">Pastel</a>
                                </li>
                                <li><a class="dropdown-item" href="#" onclick="changeChartType('bar')">Barras</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div style="height: 500px; position: relative;">
                        <canvas id="productosChart"></canvas>
                    </div>

                    {{-- Resumen de productos --}}
                    {{-- Tarjeta resumen debajo de la gráfica --}}
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body p-2 text-center">
                                    <small>💰 Total Ventas</small>
                                    <h6 class="mb-0">${{ number_format($ventasProductos->sum('total_vendido'), 2) }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success text-white">
                                <div class="card-body p-2 text-center">
                                    <small>📦 Total Unidades</small>
                                    <h6 class="mb-0">{{ number_format($ventasProductos->sum('cantidad_vendida')) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica 4: Dona Ventas históricas por producto -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient"
                    style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-chart-line me-2"></i>
                            Ventas históricas por producto
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-chart-simple"></i> Tipo
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"
                                        onclick="changeGlobalChartType('doughnut')">Dona</a></li>
                                <li><a class="dropdown-item" href="#"
                                        onclick="changeGlobalChartType('pie')">Pastel</a></li>
                                <li><a class="dropdown-item" href="#"
                                        onclick="changeGlobalChartType('bar')">Barras</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div style="height: 500px; position: relative;">
                        <canvas id="ventasProductoGlobalChart"></canvas>
                    </div>

                    {{-- Badges de resumen rápido --}}
                    <div class="row mt-4 g-2">
                        <div class="col-4">
                            <div class="alert alert-danger alert-sm mb-0 text-center">
                                <small><i class="fas fa-calendar-alt me-1"></i> Histórico total</small>
                                <br>
                                <strong>{{ $ventasProductosGlobal->count() }} productos</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="alert alert-warning alert-sm mb-0 text-center">
                                <small><i class="fas fa-dollar-sign me-1"></i> Total ventas</small>
                                <br>
                                <strong>${{ number_format($ventasProductosGlobal->sum('total_vendido'), 0) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="alert alert-info alert-sm mb-0 text-center">
                                <small><i class="fas fa-boxes me-1"></i> Total unidades</small>
                                <br>
                                <strong>{{ number_format($ventasProductosGlobal->sum('cantidad_vendida')) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="alert alert-secondary alert-sm mb-0">
                                <i class="fas fa-chart-line me-1"></i>
                                <small>Top producto: <strong>
                                        @php
                                            $top = $ventasProductosGlobal->sortByDesc('total_vendido')->first();
                                        @endphp
                                        {{ $top->producto ?? 'N/A' }}
                                    </strong> con ${{ number_format($top->total_vendido ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Gráfica 5: barras Ventas históricas por dia de la semana -->
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    Ventas ultimas 9 semanas por día
                </div>
                <div class="card-body" style="height: 450px; overflow-y: auto;">
                    <canvas id="ventasPorDiaSemanaChart" style="height: 400px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <!-- Gráfica 3: Ventas históricas por hora -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">Ventas históricas por hora</div>
                <div class="card-body" style="overflow-x: auto;">
                    <canvas id="ventasPorHoraChart" style="height: 400px; min-width: 120%; max-width: 120%;"></canvas>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        /* Animación suave para la gráfica */
        canvas {
            transition: all 0.3s ease;
        }

        /* Mejora del scroll si es necesario */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #11998e;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0d7a6e;
        }

        /* Efecto de brillo en la gráfica */
        @keyframes pulse {
            0% {
                filter: drop-shadow(0 0 0px rgba(17, 153, 142, 0.2));
            }

            100% {
                filter: drop-shadow(0 0 15px rgba(17, 153, 142, 0.4));
            }
        }

        canvas:hover {
            animation: pulse 1s ease-in-out infinite alternate;
        }
    </style>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // === GRÁFICA 2: Ventas semana actual (CORREGIDA) ===
        const ctx2 = document.getElementById('productosChart').getContext('2d');

        let productosLabelsRaw = @json($ventasProductos->pluck('producto'));
        let productosMontosRaw = @json($ventasProductos->pluck('total_vendido'));
        let productosCantidadesRaw = @json($ventasProductos->pluck('cantidad_vendida'));

        // ✅ CONVERTIR A NÚMEROS
        const productosLabels = productosLabelsRaw;
        const productosMontos = productosMontosRaw.map(val => parseFloat(val) || 0);
        const productosCantidades = productosCantidadesRaw.map(val => parseInt(val) || 0);

        const totalSemanalMonto = productosMontos.reduce((a, b) => a + b, 0);
        const totalSemanalCantidad = productosCantidades.reduce((a, b) => a + b, 0);

        // Colores más vibrantes y profesionales
        const coloresProductos = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7B731', '#5D9BEC', '#F9A26C',
            '#B83B5E', '#6C5B7B', '#F08A5D', '#B83B5E', '#2C3E50'
        ];

        // Configuración de la gráfica
        let productosChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: productosLabels,
                datasets: [{
                    data: productosMontos,
                    backgroundColor: coloresProductos.slice(0, productosLabels.length),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 15,
                    cutout: '60%', // Para efecto dona
                    borderRadius: 5,
                    spacing: 2
                }]
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.2,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const monto = data.datasets[0].data[i];
                                        const cantidad = productosCantidades[i];
                                        return {
                                            text: `${label} - $${monto.toLocaleString()} (${cantidad} und)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            index: i,
                                            fontColor: '#2c3e50'
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#11998e',
                        borderWidth: 2,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                const cantidad = productosCantidades[context.dataIndex];
                                const monto = context.parsed;
                                const porcentaje = ((monto / productosMontos.reduce((a, b) => a + b, 0)) * 100)
                                    .toFixed(1);
                                return [
                                    `💰 Monto: $${monto.toLocaleString()}`,
                                    `📦 Cantidad: ${cantidad} unidades`,
                                    `📊 Porcentaje: ${porcentaje}%`
                                ];
                            },
                            footer: function(tooltipItems) {
                                return `Total: $${productosMontos.reduce((a,b) => a+b, 0).toLocaleString()}`;
                            }
                        },
                        bodySpacing: 5,
                        titleSpacing: 5,
                        padding: 12
                    },
                    datalabels: {
                        color: '#fff',
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        borderRadius: 8,
                        padding: {
                            left: 6,
                            right: 6,
                            top: 4,
                            bottom: 4
                        },
                        formatter: function(value, context) {
                            const cantidad = productosCantidades[context.dataIndex];
                            const porcentaje = ((value / productosMontos.reduce((a, b) => a + b, 0)) * 100)
                                .toFixed(1);
                            if (porcentaje < 3) return ''; // No mostrar si es muy pequeño
                            return `$${value.toLocaleString()}\n(${cantidad})`;
                        },
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        textAlign: 'center',
                        offset: 5
                    }
                },
                onClick: function(event, activeElements) {
                    if (activeElements.length > 0) {
                        const index = activeElements[0].index;
                        const producto = productosLabels[index];
                        const monto = productosMontos[index];
                        const cantidad = productosCantidades[index];
                        alert(`📊 ${producto}\n💰 Monto: $${monto.toLocaleString()}\n📦 Unidades: ${cantidad}`);
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Función para cambiar tipo de gráfica
        function changeChartType(type) {
            productosChart.destroy();
            productosChart = new Chart(ctx2, {
                type: type,
                data: productosChart.config.data,
                plugins: [ChartDataLabels],
                options: {
                    ...productosChart.config.options,
                    maintainAspectRatio: true,
                    aspectRatio: type === 'bar' ? 1.5 : 1.2,
                    plugins: {
                        ...productosChart.config.options.plugins,
                        datalabels: {
                            ...productosChart.config.options.plugins.datalabels,
                            formatter: function(value, context) {
                                if (type === 'bar') {
                                    const cantidad = productosCantidades[context.dataIndex];
                                    return `$${value.toLocaleString()} (${cantidad})`;
                                }
                                return productosChart.config.options.plugins.datalabels.formatter(value,
                                    context);
                            }
                        }
                    }
                }
            });
        }

        // Agregar efecto hover en la tarjeta
        document.querySelector('.card').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'all 0.3s ease';
        });

        document.querySelector('.card').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });

        // === Gráfica 3: Ventas por Hora ===
        const ctx = document.getElementById('ventasPorHoraChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($ventasPorHora->pluck('hora')),
                datasets: [{
                    label: 'Ventas por hora ($)',
                    data: @json($ventasPorHora->pluck('venta')),
                    backgroundColor: @json($coloresProductos),
                    borderWidth: 1
                }]
            },
            plugins: [ChartDataLabels], // <-- habilitamos el plugin
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- importante queremos scroll
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto ($)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        color: '#000',
                        anchor: 'end',
                        align: function(context) {
                            const value = context.dataset.data[context.dataIndex];
                            return value < 1000 ? 'right' : 'left';
                        },
                        formatter: value => `$${value.toLocaleString()}`,
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        textShadowBlur: 10,
                        textShadowColor: 'rgba(255, 255, 255, 0.3)',
                    },
                    tooltip: {
                        enabled: false // el tooltip sigue funcionando si lo quieres
                    }
                }
            }
        });

        // === GRÁFICA 4: Ventas globales por producto (CORREGIDA - SIN NaN) ===
        const ctxGlobal = document.getElementById('ventasProductoGlobalChart').getContext('2d');

        // Obtener datos raw (vienen como strings)
        let productosGlobalLabelsRaw = @json($ventasProductosGlobal->pluck('producto'));
        let productosGlobalMontosRaw = @json($ventasProductosGlobal->pluck('total_vendido'));
        let productosGlobalCantidadesRaw = @json($ventasProductosGlobal->pluck('cantidad_vendida'));

        // ✅ CONVERTIR STRINGS A NÚMEROS
        const productosGlobalLabels = productosGlobalLabelsRaw;
        const productosGlobalMontos = productosGlobalMontosRaw.map(val => parseFloat(val) || 0);
        const productosGlobalCantidades = productosGlobalCantidadesRaw.map(val => parseInt(val) || 0);

        // Calcular totales (ahora con números)
        const totalGlobalMonto = productosGlobalMontos.reduce((a, b) => a + b, 0);
        const totalGlobalCantidad = productosGlobalCantidades.reduce((a, b) => a + b, 0);

        // Colores degradados para histórico
        const coloresGlobales = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7B731', '#5D9BEC', '#F9A26C',
            '#B83B5E', '#6C5B7B', '#F08A5D', '#B83B5E', '#2C3E50',
            '#E74C3C', '#3498DB', '#2ECC71', '#F39C12', '#9B59B6'
        ];

        let ventasGlobalChart = new Chart(ctxGlobal, {
            type: 'doughnut',
            data: {
                labels: productosGlobalLabels,
                datasets: [{
                    data: productosGlobalMontos,
                    backgroundColor: coloresGlobales.slice(0, productosGlobalLabels.length),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 15,
                    cutout: '55%',
                    borderRadius: 8,
                    spacing: 3
                }]
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.2,
                layout: {
                    padding: {
                        top: 20,
                        bottom: 20,
                        left: 15,
                        right: 15
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 10,
                                weight: '500'
                            },
                            padding: 10,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 6,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const monto = data.datasets[0].data[i];
                                        const cantidad = productosGlobalCantidades[i];
                                        const porcentaje = totalGlobalMonto > 0 ? ((monto /
                                            totalGlobalMonto) * 100).toFixed(1) : 0;
                                        return {
                                            text: `${label} - $${monto.toLocaleString()} (${cantidad} und) ${porcentaje}%`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            index: i,
                                            fontColor: '#2c3e50'
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.9)',
                        titleColor: '#f5576c',
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyColor: '#fff',
                        bodyFont: {
                            size: 12
                        },
                        borderColor: '#f5576c',
                        borderWidth: 2,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const cantidad = productosGlobalCantidades[context.dataIndex];
                                const monto = context.parsed;
                                const porcentajeMonto = totalGlobalMonto > 0 ? ((monto / totalGlobalMonto) *
                                    100).toFixed(1) : 0;
                                const porcentajeCantidad = totalGlobalCantidad > 0 ? ((cantidad /
                                    totalGlobalCantidad) * 100).toFixed(1) : 0;
                                const promedio = cantidad > 0 ? (monto / cantidad).toFixed(2) : 0;
                                return [
                                    `💰 Monto: $${monto.toLocaleString()} (${porcentajeMonto}%)`,
                                    `📦 Cantidad: ${cantidad.toLocaleString()} unidades (${porcentajeCantidad}%)`,
                                    `📊 Promedio: $${promedio} por unidad`
                                ];
                            },
                            footer: function() {
                                return `📈 Total histórico: $${totalGlobalMonto.toLocaleString()}`;
                            }
                        },
                        bodySpacing: 8,
                        titleSpacing: 8,
                        padding: 12,
                        caretSize: 8
                    },
                    datalabels: {
                        color: '#fff',
                        backgroundColor: 'rgba(0,0,0,0.75)',
                        borderRadius: 10,
                        padding: {
                            left: 8,
                            right: 8,
                            top: 5,
                            bottom: 5
                        },
                        formatter: function(value, context) {
                            const cantidad = productosGlobalCantidades[context.dataIndex];
                            const porcentaje = totalGlobalMonto > 0 ? ((value / totalGlobalMonto) * 100)
                                .toFixed(1) : 0;
                            if (porcentaje < 4) return '';
                            if (porcentaje < 8) return `${porcentaje}%`;
                            return `${porcentaje}%\n$${Math.round(value/1000)}k`;
                        },
                        font: {
                            weight: 'bold',
                            size: 10
                        },
                        textAlign: 'center',
                        offset: 8,
                        clamp: true
                    }
                },
                onClick: function(event, activeElements) {
                    if (activeElements.length > 0) {
                        const index = activeElements[0].index;
                        const producto = productosGlobalLabels[index];
                        const monto = productosGlobalMontos[index];
                        const cantidad = productosGlobalCantidades[index];
                        const porcentajeMonto = totalGlobalMonto > 0 ? ((monto / totalGlobalMonto) * 100)
                            .toFixed(1) : 0;
                        const porcentajeCantidad = totalGlobalCantidad > 0 ? ((cantidad / totalGlobalCantidad) *
                            100).toFixed(1) : 0;
                        const promedio = cantidad > 0 ? (monto / cantidad).toFixed(2) : 0;

                        // SweetAlert (opcional)
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: producto,
                                html: `
                            <div class="text-start">
                                <p><strong>💰 Monto total:</strong> $${monto.toLocaleString()} (${porcentajeMonto}% del total)</p>
                                <p><strong>📦 Unidades vendidas:</strong> ${cantidad.toLocaleString()} (${porcentajeCantidad}%)</p>
                                <p><strong>📊 Precio promedio:</strong> $${promedio}</p>
                                <div class="progress mt-3">
                                    <div class="progress-bar bg-danger" style="width: ${porcentajeMonto}%">${porcentajeMonto}%</div>
                                </div>
                            </div>
                        `,
                                icon: 'info',
                                confirmButtonColor: '#f5576c',
                                confirmButtonText: 'Cerrar'
                            });
                        } else {
                            alert(
                                `📊 ${producto}\n💰 Monto: $${monto.toLocaleString()} (${porcentajeMonto}%)\n📦 Unidades: ${cantidad}`
                            );
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1200,
                    easing: 'easeOutCubic'
                }
            }
        });

        // Función para cambiar tipo de gráfica
        function changeGlobalChartType(type) {
            if (ventasGlobalChart) {
                ventasGlobalChart.destroy();
            }

            ventasGlobalChart = new Chart(ctxGlobal, {
                type: type,
                data: {
                    labels: productosGlobalLabels,
                    datasets: [{
                        data: productosGlobalMontos,
                        backgroundColor: coloresGlobales.slice(0, productosGlobalLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                plugins: [ChartDataLabels],
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: type === 'bar' ? 1.5 : 1.2,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        datalabels: {
                            color: '#000',
                            formatter: function(value, context) {
                                const cantidad = productosGlobalCantidades[context.dataIndex];
                                if (type === 'bar') {
                                    return `$${value.toLocaleString()}`;
                                }
                                const porcentaje = totalGlobalMonto > 0 ? ((value / totalGlobalMonto) * 100)
                                    .toFixed(1) : 0;
                                if (porcentaje < 5) return '';
                                return `${porcentaje}%`;
                            },
                            font: {
                                weight: 'bold',
                                size: 11
                            }
                        }
                    }
                }
            });
        }

        // Mostrar en consola que todo está bien
        console.log('✅ Gráfica histórica cargada correctamente', {
            productos: productosGlobalLabels.length,
            montoTotal: totalGlobalMonto,
            cantidadTotal: totalGlobalCantidad
        });

        //ventas históricas por dia de la semana
        const ctxDiaSemana = document
            .getElementById('ventasPorDiaSemanaChart')
            .getContext('2d');

        new Chart(ctxDiaSemana, {
            type: 'bar',
            data: {
                labels: @json($labelsDias),
                datasets: [{
                    label: 'Ventas por día ($)',
                    data: @json($dataDias),
                    backgroundColor: @json($coloresProductos),
                    borderWidth: 1
                }]
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto ($)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `$${ctx.parsed.y.toLocaleString()}`
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#000',
                        formatter: value => `$${value.toLocaleString()}`,
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        textShadowBlur: 10,
                        textShadowColor: 'rgba(255,255,255,0.8)'
                    }
                }
            }
        });
    </script>

@stop
