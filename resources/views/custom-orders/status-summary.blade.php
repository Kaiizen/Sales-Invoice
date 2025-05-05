<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title"><i class="fa fa-tasks"></i> Order Status Summary</h3>
                <div class="btn-group">
                    <a class="btn btn-primary" href="{{ route('custom-orders.index') }}">
                        <i class="fa fa-list"></i> All Orders
                    </a>
                    <a class="btn btn-info" href="{{ route('custom-orders.kanban') }}">
                        <i class="fa fa-columns"></i> Kanban View
                    </a>
                </div>
            </div>
            <div class="tile-body">
                <div class="row">
                    @foreach(App\CustomOrder::STATUSES as $status)
                    <div class="col-md-3 col-sm-6">
                        <div class="widget-small {{ $status === 'Pending' ? 'warning' : ($status === 'In Production' ? 'info' : ($status === 'Ready' ? 'success' : 'primary')) }} coloured-icon">
                            <i class="icon fa {{ $status === 'Pending' ? 'fa-clock-o' : ($status === 'In Production' ? 'fa-cogs' : ($status === 'Ready' ? 'fa-check' : 'fa-truck')) }} fa-3x"></i>
                            <div class="info">
                                <h4>{{ $status }}</h4>
                                <p><b>{{ $orders->where('status', $status)->count() }}</b></p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="tile">
                            <h3 class="tile-title">Orders by Type</h3>
                            <div class="embed-responsive embed-responsive-16by9">
                                <canvas class="embed-responsive-item" id="ordersByTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tile">
                            <h3 class="tile-title">Orders by Status</h3>
                            <div class="embed-responsive embed-responsive-16by9">
                                <canvas class="embed-responsive-item" id="ordersByStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/plugins/chart.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the orders data from the page
        const orders = @json($orders);
        
        // Prepare data for Orders by Type chart
        const flagTypes = {};
        orders.forEach(order => {
            if (!flagTypes[order.flag_type]) {
                flagTypes[order.flag_type] = 0;
            }
            flagTypes[order.flag_type]++;
        });
        
        // Prepare data for Orders by Status chart
        const statuses = {};
        orders.forEach(order => {
            if (!statuses[order.status]) {
                statuses[order.status] = 0;
            }
            statuses[order.status]++;
        });
        
        // Create Orders by Type chart
        const typeCtx = document.getElementById('ordersByTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(flagTypes),
                datasets: [{
                    data: Object.values(flagTypes),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Create Orders by Status chart
        const statusCtx = document.getElementById('ordersByStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statuses),
                datasets: [{
                    data: Object.values(statuses),
                    backgroundColor: [
                        '#FFC107', '#17A2B8', '#28A745', '#007BFF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@endpush