@extends('layouts.master')

@section('title', 'Purchases | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Purchases</h1>
                <p>List of all supplier purchases</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Purchases</li>
                <li class="breadcrumb-item active"><a href="#">Purchase List</a></li>
            </ul>
        </div>
        <div class="mb-3">
            <a class="btn btn-primary" href="{{ route('purchases.create') }}">
                <i class="fa fa-plus"></i> Add New Purchase
            </a>
        </div>

        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="purchasesTable">
                                <thead>
                                    <tr>
                                        <th>Purchase ID</th>
                                        <th>Supplier</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->id }}</td>
                                            <td>{{ $purchase->supplier->name }}</td>
                                            <td>{{ $purchase->date }}</td>
                                            <td>{{ number_format($purchase->total_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                                <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $purchase->id }}')">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                                <form id="delete-form-{{ $purchase->id }}" action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('js')
    <script type="text/javascript" src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">
        $('#purchasesTable').DataTable();

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this purchase? This will reverse all inventory changes.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endpush
