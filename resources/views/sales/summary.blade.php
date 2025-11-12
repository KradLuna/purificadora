@extends('adminlte::page')

@section('title', 'Resumen de Ventas')

@section('content_header')
    <x-header-section title="Resumen de Ventas" />
@stop

@section('content')

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ventas por empleado - Semana actual</h5>
                </div>
                <div class="card-body p-0">
                    {{-- Contenedor responsive con scroll horizontal --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>Empleado</th>
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miércoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                    <th>Sábado</th>
                                    <th>Domingo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ventasPorEmpleadoProcesadas as $empleado)
                                    <tr>
                                        <td>{{ $empleado['empleado'] }}</td>
                                        @foreach ($empleado['ventas'] as $venta)
                                            <td>${{ number_format($venta, 2) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ventas por empleado - Semana actual</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th class="text-end">Total de la semana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventasSemanaPorEmpleado as $venta)
                                <tr>
                                    <td>{{ $venta->user->full_name ?? 'Desconocido' }}</td>
                                    <td class="text-end">${{ number_format($venta->total_semana, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin ventas en la semana</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Gráfica 1: Ventas por Día -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Ventas de la Semana vs Promedio Histórico</div>
                <div class="card-body">
                    <canvas id="ventasSemanaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfica 2: Ventas por Producto -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">Ventas por Producto (Semana Actual)</div>
                <div class="card-body">
                    <canvas id="productosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // === Gráfica 1: Ventas por Día ===
        const ctx1 = document.getElementById('ventasSemanaChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: @json($nombresDias),
                datasets: [{
                    label: 'Ventas ($)',
                    data: @json($datosVentas),
                    backgroundColor: @json($coloresDias),
                    borderWidth: 1
                }]
            },
            options: {
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
                    }
                }
            }
        });

        // === Gráfica 2: Ventas por Producto ===
        const ctx2 = document.getElementById('productosChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: @json($productos),
                datasets: [{
                    data: @json($totalesProductos),
                    backgroundColor: @json($coloresProductos),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: $${ctx.parsed.toLocaleString()}`
                        }
                    }
                }
            }
        });
    </script>
@stop
