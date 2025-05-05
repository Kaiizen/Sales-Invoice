<div class="tile">
    <h3 class="tile-title">Inventory Details</h3>
    <div class="tile-body">
        <div class="row">
            <div class="form-group col-md-6">
                <label class="control-label">Current Stock</label>
                <input value="{{ old('current_stock', 0) }}" name="current_stock" class="form-control" type="number" placeholder="Current quantity in stock">
            </div>
            <div class="form-group col-md-6">
                <label class="control-label">Minimum Stock</label>
                <input value="{{ old('minimum_stock', 5) }}" name="minimum_stock" class="form-control" type="number" placeholder="Minimum quantity threshold">
                <small class="form-text text-muted">You'll receive alerts when stock falls below this level.</small>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <div class="alert alert-info">
                    <strong>Automatic Reordering</strong>
                    <p>The system will notify you when stock falls below the minimum level.</p>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="control-label">Location</label>
                <input value="{{ old('location', '') }}" name="location" class="form-control" type="text" placeholder="Storage location (optional)">
            </div>
        </div>
    </div>
</div>