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
                                @forelse ($ventasPorEmpleadoProcesadas as $empleado)
                                    <tr>
                                        <td>{{ $empleado['empleado'] }}</td>
                                        @foreach ($empleado['ventas'] as $venta)
                                            <td class="text-center">${{ number_format($venta, 2) }}</td>
                                        @endforeach
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
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td>Total: </td>
                                <td class="text-end">${{ number_format($totalVentasSemana, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php $auxTotal = 0.0; @endphp
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Pago por empleado - Semana actual</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th class="text-end">Horas Trabajadas</th>
                                <th class="text-end">Monto a Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($totalPagoHoras as $pago)
                                @php $auxTotal += (float) $pago->monto_pagado; @endphp
                                <tr>
                                    <td>{{ $pago->full_name ?? 'Desconocido' }}</td>
                                    <td class="text-end">{{ $pago->horas_trabajadas }}</td>
                                    <td class="text-end">${{ number_format($pago->monto_pagado, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin ventas en la semana</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary fw-bold">
                                <td>Total: </td>
                                <td></td>
                                <td class="text-end">${{ number_format($auxTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfica 1: Ventas históricas semanales -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Ventas históricas semanales</div>
                <div class="card-body" style="overflow-x: auto;">
                    <canvas id="ventasPorSemanaChart" style="height: 350px; min-width: 120%; max-width: 120%;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">Ventas semanales (Garrafones)</div>
                <div class="card-body" style="overflow-x: auto;">
                    <canvas id="ventasSemanalesProductosChart"
                        style="height: 350px; min-width: 120%; max-width: 120%;"></canvas>
                </div>
            </div>
        </div>


    </div>
    <div class="row">
        <!-- Gráfica 2: Ventas por Producto -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">Ventas por Producto (Semana Actual)</div>
                <div class="card-body" style="overflow-x: auto;">
                    <canvas id="productosChart" style="min-width: 90%; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfica 4: Dona Ventas históricas por producto -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    Ventas históricas por producto
                </div>
                <div class="card-body">
                    <canvas id="ventasProductoGlobalChart" style="min-width: 90%; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Gráfica 5: barras Ventas históricas por dia de la semana -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    Ventas históricas por día de la semana
                </div>
                <div class="card-body">
                    <canvas id="ventasPorDiaSemanaChart" style="height: 350px;"></canvas>
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

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        // === Gráfica: Ventas por Semana (Horizontal con Data Labels) ===
        const ctxSemana = document.getElementById('ventasPorSemanaChart').getContext('2d');

        new Chart(ctxSemana, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Ventas ($)',
                    data: @json($data),
                    backgroundColor: @json($colores),
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


        // === Gráfica 2: Ventas por Producto (con valores visibles) ===
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
            plugins: [ChartDataLabels], // <-- habilitamos el plugin
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- importante queremos scroll
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: $${ctx.parsed.toLocaleString()}`
                        }
                    },
                    datalabels: {
                        color: '#000',
                        formatter: value => `$${value.toLocaleString()}`,
                        font: {
                            weight: 'bold',
                            size: 13
                        },
                        textShadowBlur: 10,
                        textShadowColor: 'rgba(255, 255, 255, 1)',
                    }
                }
            }
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
                    backgroundColor: @json($colores),
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
        // === Gráfica 4: Ventas globales por producto ===
        const ctxGlobal = document
            .getElementById('ventasProductoGlobalChart')
            .getContext('2d');

        new Chart(ctxGlobal, {
            type: 'doughnut',
            data: {
                labels: @json($ventasProductosGlobal->pluck('producto')),
                datasets: [{
                    data: @json($ventasProductosGlobal->pluck('total_vendido')),
                    backgroundColor: @json($colores),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const cantidad = @json($ventasProductosGlobal->pluck('cantidad_vendida'))[context.dataIndex];
                                const total = context.parsed;
                                return `${context.label}: $${total.toLocaleString()} (${cantidad})`;
                            }
                        }
                    },
                    datalabels: {
                        color: '#000',
                        formatter: function(value, context) {
                            const cantidad = @json($ventasProductosGlobal->pluck('cantidad_vendida'))[context.dataIndex];
                            return `$${value.toLocaleString()} (${cantidad})`;
                        },
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        textShadowBlur: 10,
                        textShadowColor: 'rgba(255,255,255,1)'
                    }
                }
            }
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
                    backgroundColor: @json($colores),
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
        // ventas garrafones
        const ctxSemProd = document.getElementById('ventasSemanalesProductosChart').getContext('2d');

        new Chart(ctxSemProd, {
            type: 'bar',
            data: {
                labels: @json($labelsSemanasProd),
                datasets: [{
                    label: 'Ventas ($)',
                    data: @json($totalesSemanasProd),
                    backgroundColor: @json($colores),
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


        // const ctxSemProd = document
        //     .getElementById('ventasSemanalesProductosChart')
        //     .getContext('2d');

        // new Chart(ctxSemProd, {
        //     type: 'bar',
        //     data: {
        //         labels: @json($labelsSemanasProd),
        //         datasets: [{
        //             label: 'Ventas semanales ($)',
        //             data: @json($totalesSemanasProd),
        //             backgroundColor: @json($colores),
        //             borderWidth: 1
        //         }]
        //     },
        //     plugins: [ChartDataLabels],
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         scales: {
        //             y: {
        //                 beginAtZero: true,
        //                 title: {
        //                     display: true,
        //                     text: 'Monto ($)'
        //                 }
        //             }
        //         },
        //         plugins: {
        //             legend: {
        //                 display: false
        //             },
        //             tooltip: {
        //                 callbacks: {
        //                     label: ctx => `$${ctx.parsed.y.toLocaleString()}`
        //                 }
        //             },
        //             datalabels: {
        //                 anchor: 'end',
        //                 align: 'top',
        //                 color: '#000',
        //                 formatter: value => `$${value.toLocaleString()}`,
        //                 font: {
        //                     weight: 'bold',
        //                     size: 11
        //                 },
        //                 textShadowBlur: 10,
        //                 textShadowColor: 'rgba(255,255,255,0.8)'
        //             }
        //         }
        //     }
        // });
    </script>
@stop
