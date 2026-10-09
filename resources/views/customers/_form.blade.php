<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required maxlength="100" />
</div>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}" maxlength="100" />
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Phone</label>
    <input id="phone" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}" maxlength="15" />
</div>
<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <textarea id="address" name="address" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea>
</div>
