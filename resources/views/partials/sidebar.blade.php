<div class="app-sidebar__overlay" data-toggle="sidebar"></div>

<aside class="app-sidebar">
    <div class="app-sidebar__user">
        @auth
            <img width="40 px" class="app-sidebar__user-avatar"
                 src="{{ asset(Auth::user()->image ? 'images/user/'.Auth::user()->image : 'images/user/admin-icn.png') }}"
                 alt="User Image">
            <div>
                <p class="app-sidebar__user-name">{{ Auth::user()->fullname ?? 'Admin' }}</p>
            </div>
        @else
            <img width="40 px" class="app-sidebar__user-avatar"
                 src="{{ asset('images/user/admin-icn.png') }}"
                 alt="User Image">
            <div>
                <p class="app-sidebar__user-name">Guest</p>
            </div>
        @endauth
    </div>
    <ul class="app-menu">
        <li><a class="app-menu__item {{ request()->is('/') ? 'active' : ''}}" href="/"><i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span></a></li>

        
        <li class="treeview"><a class="app-menu__item {{ request()->is('tax*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-percent"></i><span class="app-menu__label">Tax</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{route('taxes.create')}}"><i class="icon fa fa-circle-o"></i> Add Tax</a></li>
                <li><a class="treeview-item" href="{{route('taxes.index')}}"><i class="icon fa fa-circle-o"></i> Manage Tax</a></li>
             </ul>
        </li>

        <li class="treeview "><a class="app-menu__item {{ request()->is('category*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-th"></i><span class="app-menu__label">Category</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item " href="{{route('categories.create')}}"><i class="icon fa fa-plus"></i>Create Category</a></li>
                <li><a class="treeview-item" href="{{route('categories.index')}}"><i class="icon fa fa-edit"></i>Manage Category</a></li>
            </ul>
        </li>

        <li class="treeview"><a class="app-menu__item {{ request()->is('product*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-cube"></i><span class="app-menu__label">Product</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{route('products.create')}}"><i class="icon fa fa-plus"></i> New Product</a></li>
                <li><a class="treeview-item" href="{{route('products.index')}}"><i class="icon fa fa-list"></i> Manage Products</a></li>
            </ul>
        </li>


        <li class="treeview"><a class="app-menu__item {{ request()->is('unit*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-bars"></i><span class="app-menu__label">Unit</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{route('units.create')}}"><i class="icon fa fa-circle-o"></i> Add Unit</a></li>
                <li><a class="treeview-item" href="{{route('units.index')}}"><i class="icon fa fa-circle-o"></i> Manage Unit</a></li>
            </ul>
        </li>

        <li class="treeview "><a class="app-menu__item {{ request()->is('invoice*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-file"></i><span class="app-menu__label">Invoice</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item " href="{{route('invoices.create')}}"><i class="icon fa fa-plus"></i>Create Invoice </a></li>
                <li><a class="treeview-item" href="{{route('invoices.index')}}"><i class="icon fa fa-edit"></i>Manage Invoice</a></li>
            </ul>
        </li>

        <li><a class="app-menu__item {{ request()->is('sales') ? 'active' : ''}}" href="/sales"><i class="app-menu__icon fa fa-dollar"></i><span class="app-menu__label">View Sales</span></a></li>

        <li class="treeview"><a class="app-menu__item {{ request()->is('custom-orders*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-shopping-cart"></i><span class="app-menu__label">Custom Orders</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{route('custom-orders.create')}}"><i class="icon fa fa-plus"></i> Create Order</a></li>
                <li><a class="treeview-item" href="{{route('custom-orders.index')}}"><i class="icon fa fa-list"></i> Manage Orders</a></li>
            </ul>
        </li>

        <li class="treeview"><a class="app-menu__item {{ request()->is('supplier*') || request()->is('purchases*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-truck"></i><span class="app-menu__label">Supplier</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{route('suppliers.create')}}"><i class="icon fa fa-circle-o"></i> Add Supplier</a></li>
                <li><a class="treeview-item" href="{{route('suppliers.index')}}"><i class="icon fa fa-circle-o"></i> Manage Suppliers</a></li>
                <li><a class="treeview-item" href="{{route('purchases.create')}}"><i class="icon fa fa-shopping-cart"></i> Add Purchase</a></li>
                <li><a class="treeview-item" href="{{route('purchases.index')}}"><i class="icon fa fa-list"></i> Manage Purchases</a></li>
            </ul>
        </li>


        <li class="treeview"><a class="app-menu__item {{ request()->is('customer*') ? 'active' : ''}}" href="#" data-toggle="treeview"><i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">Customer</span><i class="treeview-indicator fa fa-angle-right"></i></a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{route('customers.create')}}"><i class="icon fa fa-circle-o"></i> Add Customer</a></li>
                <li><a class="treeview-item" href="{{route('customers.index')}}"><i class="icon fa fa-circle-o"></i> Manage Customer</a></li>
            </ul>
        </li>



    </ul>
</aside>
