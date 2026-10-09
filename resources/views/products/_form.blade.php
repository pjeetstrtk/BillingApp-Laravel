<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required maxlength="100" />
</div>
<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" class="form-control">{{ old('description', $product->description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label for="price" class="form-label">Price</label>
    <input id="price" name="price" type="number" step="0.01" min="0" class="form-control" value="{{ old('price', $product->price ?? '') }}" required />
</div>
<div class="mb-3">
    <label for="stock" class="form-label">Stock</label>
    <input id="stock" name="stock" type="number" min="0" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" required />
</div>
