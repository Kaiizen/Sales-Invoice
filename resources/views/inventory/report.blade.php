@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Inventory Analytics Report</h3>
                        <div>
                            <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Stock Value by Category</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoryChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Stock Movement Trends</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="movementChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Inventory Valuation</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Total Items</th>
                                                    <th>Total Cost Value</th>
                                                    <th>Total Retail Value</th>
                                                    <th>Profit Potential</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($valuation as $item)
                                                    <tr>
                                                        <td>{{ $item->category_name }}</td>
                                                        <td>{{ $item->total_items }}</td>
                                                        <td>{{ number_format($item->total_cost, 2) }}</td>
                                                        <td>{{ number_format($item->total_retail, 2) }}</td>
                                                        <td>{{ number_format($item->total_retail - $item->total_cost, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-active">
                                                    <td><strong>Total</strong></td>
                                                    <td><strong>{{ $totals['items'] }}</strong></td>
                                                    <td><strong>{{ number_format($totals['cost'], 2) }}</strong></td>
                                                    <td><strong>{{ number_format($totals['retail'], 2) }}</strong></td>
                                                    <td><strong>{{ number_format($totals['retail'] - $totals['cost'], 2) }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    $categoryLabels = json_encode($categoryChart['labels']);
    $categoryData = json_encode($categoryChart['data']);
    $movementLabels = json_encode($movementChart['labels']);
    $movementInData = json_encode($movementChart['in']);
    $movementOutData = json_encode($movementChart['out']);
@endphp

<!-- First script tag for data passing -->
<script>
    window.inventoryData = {
        categoryChart: {
            labels: JSON.parse('<?php echo $categoryLabels; ?>'),
            data: JSON.parse('<?php echo $categoryData; ?>')
        },
        movementChart: {
            labels: JSON.parse('<?php echo $movementLabels; ?>'),
            in: JSON.parse('<?php echo $movementInData; ?>'),
            out: JSON.parse('<?php echo $movementOutData; ?>')
        }
    };
</script>

<!-- Second script tag for chart initialization -->
<script>
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: window.inventoryData.categoryChart.labels,
            datasets: [{
                data: window.inventoryData.categoryChart.data,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                    '#9966FF', '#FF9F40', '#8AC24A', '#607D8B'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Movement Chart
    const movementCtx = document.getElementById('movementChart').getContext('2d');
    new Chart(movementCtx, {
        type: 'line',
        data: {
            labels: window.inventoryData.movementChart.labels,
            datasets: [{
                label: 'Stock In',
                data: window.inventoryData.movementChart.in,
                borderColor: '#4BC0C0',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true
            }, {
                label: 'Stock Out',
                data: window.inventoryData.movementChart.out,
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
    
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                    '#9966FF', '#FF9F40', '#8AC24A', '#607D8B'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: window.inventoryData.categoryChart.labels,
            datasets: [{
                data: window.inventoryData.categoryChart.data,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                    '#9966FF', '#FF9F40', '#8AC24A', '#607D8B'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Movement Chart
    const movementCtx = document.getElementById('movementChart').getContext('2d');
    new Chart(movementCtx, {
        type: 'line',
        data: {
            labels: window.inventoryData.movementChart.labels,
            datasets: [{
                label: 'Stock In',
                data: window.inventoryData.movementChart.in,
                borderColor: '#4BC0C0',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true
            }, {
                label: 'Stock Out',
                data: window.inventoryData.movementChart.out,
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    new Chart(movementCtx, {
        type: 'line',
        data: {
            labels: movementLabels,
            datasets: [{
                label: 'Stock In',
                data: movementInData,
                borderColor: '#4BC0C0',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true
            }, {
                label: 'Stock Out',
                data: movementOutData,
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection