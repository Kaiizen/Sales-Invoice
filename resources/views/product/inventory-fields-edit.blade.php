<div class="tile">
    <h3 class="tile-title">Inventory Details</h3>
    <div class="tile-body">
        <div class="row">
            <div class="form-group col-md-6">
                <label class="control-label">Current Stock</label>
                <input value="{{ $product->current_stock }}" name="current_stock" class="form-control" type="number" placeholder="Current quantity in stock">
            </div>
            <div class="form-group col-md-6">
                <label class="control-label">Minimum Stock</label>
                <input value="{{ $product->minimum_stock }}" name="minimum_stock" class="form-control" type="number" placeholder="Minimum quantity threshold">
                <small class="form-text text-muted">You'll receive alerts when stock falls below this level.</small>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="auto_reorder" value="1" {{ $product->auto_reorder ? 'checked' : '' }}>
                        Enable automatic reorder
                    </label>
                    <small class="form-text text-muted">System will suggest reordering when stock falls below minimum level.</small>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="control-label">Location</label>
                <input value="{{ $product->location }}" name="location" class="form-control" type="text" placeholder="Storage location (optional)">
            </div>
        </div>
    </div>
</div>