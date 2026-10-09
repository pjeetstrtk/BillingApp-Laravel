@extends('layouts.app')

@section('title', 'New Invoice')

@section('content')
@php
    $productMap = $products->mapWithKeys(fn ($p) => [$p->id => [
        'name'  => $p->name,
        'price' => (float) $p->price,
        'stock' => $p->stock,
    ]]);

    $oldQuantities = old('quantities', []);
    $oldItems = collect(old('product_ids', []))
        ->values()
        ->map(fn ($id, $i) => ['id' => (int) $id, 'qty' => (int) ($oldQuantities[$i] ?? 1)]);
@endphp

<h2>New Invoice</h2>

<form method="POST" action="{{ route('billings.store') }}" class="card p-4 shadow-sm mt-3">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="customer_id">Customer</label>
        <select id="customer_id" name="customer_id" class="form-select" required>
            <option value="">-- Select Customer --</option>
            @foreach ($customers as $c)
                <option value="{{ $c->id }}" @selected((string) old('customer_id') === (string) $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <h5>Items</h5>
    <table class="table" id="itemsTable">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="mb-3">
        <select id="productSelect" class="form-select">
            <option value="">-- Select Product --</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }} - Rs. {{ number_format($p->price, 2) }} ({{ $p->stock }} left)</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <button type="button" class="btn btn-outline-primary" id="addItemBtn">Add Item</button>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <h4>Total: Rs. <span id="total">0.00</span></h4>
    </div>

    <div>
        <button class="btn btn-primary">Generate Invoice</button>
        <a href="{{ route('billings.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const products = @json($productMap);
    const oldItems = @json($oldItems);

    const tbody = document.querySelector('#itemsTable tbody');
    const select = document.getElementById('productSelect');

    function cell(tr) {
        const td = document.createElement('td');
        tr.appendChild(td);
        return td;
    }

    function addRow(id, qty) {
        const product = products[id];
        if (!product) return;

        const tr = document.createElement('tr');
        tr.dataset.price = product.price;

        const nameTd = cell(tr);
        nameTd.textContent = product.name;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'product_ids[]';
        hidden.value = id;
        nameTd.appendChild(hidden);

        cell(tr).textContent = 'Rs. ' + product.price.toFixed(2);

        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';
        qtyInput.name = 'quantities[]';
        qtyInput.value = qty;
        qtyInput.min = 1;
        qtyInput.max = product.stock;
        qtyInput.className = 'form-control form-control-sm qty';
        qtyInput.style.width = '80px';
        qtyInput.addEventListener('input', calcTotal);
        cell(tr).appendChild(qtyInput);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-danger';
        removeBtn.textContent = 'X';
        removeBtn.addEventListener('click', () => { tr.remove(); calcTotal(); });
        cell(tr).appendChild(removeBtn);

        tbody.appendChild(tr);
        calcTotal();
    }

    function calcTotal() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const price = parseFloat(tr.dataset.price);
            const qty = parseInt(tr.querySelector('.qty').value) || 0;
            total += price * qty;
        });
        document.getElementById('total').textContent = total.toFixed(2);
    }

    document.getElementById('addItemBtn').addEventListener('click', () => {
        if (!select.value) return;
        addRow(select.value, 1);
    });

    // Restore rows after a failed submit
    oldItems.forEach(item => addRow(item.id, item.qty));
</script>
@endpush
