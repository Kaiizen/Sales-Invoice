<div class="tile">
    <h3 class="tile-title">Fabric Details</h3>
    <div class="tile-body">
        <div class="row">
            <div class="form-group col-md-6">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="is_fabric" value="1" {{ $product->is_fabric ? 'checked' : '' }}>
                        This is a fabric product
                    </label>
                </div>
            </div>
            <div class="form-group col-md-6">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="track_by_roll" value="1" {{ $product->track_by_roll ? 'checked' : '' }}>
                        Track by roll (for fabrics)
                    </label>
                </div>
            </div>
        </div>

        <div id="fabric-fields" class="{{ $product->is_fabric ? '' : 'd-none' }}">

            <div class="row">
                <div class="form-group col-md-6">
                    <label class="control-label">Total Square Feet</label>
                    <input value="{{ $product->total_square_feet }}" name="total_square_feet" class="form-control" type="number" step="0.01" placeholder="Total square feet available">
                </div>
                <div class="form-group col-md-6">
                    <label class="control-label">Alert Threshold (%)</label>
                    <input value="{{ $product->alert_threshold_percent }}" name="alert_threshold_percent" class="form-control" type="number" min="1" max="100" placeholder="Alert when below this percentage">
                    <small class="form-text text-muted">You'll receive alerts when fabric is below this percentage of the original amount.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="add-roll-section" class="{{ ($product->is_fabric && $product->track_by_roll) ? '' : 'd-none' }}">
    <div class="tile">
        <h3 class="tile-title">Add New Fabric Roll</h3>
        <div class="tile-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label class="control-label">Roll Width (feet)</label>
                    <input name="roll_width" class="form-control" type="number" step="0.01" placeholder="Width in feet">
                </div>
                <div class="form-group col-md-4">
                    <label class="control-label">Roll Length (feet)</label>
                    <input name="roll_length" class="form-control" type="number" step="0.01" placeholder="Length in feet">
                </div>
                <div class="form-group col-md-4">
                    <label class="control-label">Number of Rolls</label>
                    <input name="number_of_rolls" class="form-control" type="number" min="1" step="1" value="1" placeholder="Number of rolls">
                    <small class="form-text text-muted">Number of identical rolls to add</small>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="add_new_roll" value="1">
                            Add this roll to inventory
                        </label>
                        <small class="form-text text-muted">Check this box to add a new roll with the dimensions above to the inventory.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        // Toggle fabric fields visibility
        $('input[name="is_fabric"]').change(function() {
            if(this.checked) {
                $('#fabric-fields').removeClass('d-none');
            } else {
                $('#fabric-fields').addClass('d-none');
                $('input[name="track_by_roll"]').prop('checked', false);
                $('#add-roll-section').addClass('d-none');
            }
        });

        // Toggle roll tracking fields visibility
        $('input[name="track_by_roll"]').change(function() {
            if(this.checked && $('input[name="is_fabric"]').is(':checked')) {
                $('#add-roll-section').removeClass('d-none');
            } else {
                $('#add-roll-section').addClass('d-none');
            }
        });

        // Calculate square feet when dimensions or number of rolls change
        $('input[name="roll_width"], input[name="roll_length"], input[name="number_of_rolls"]').change(function() {
            var width = parseFloat($('input[name="roll_width"]').val()) || 0;
            var length = parseFloat($('input[name="roll_length"]').val()) || 0;
            var numberOfRolls = parseInt($('input[name="number_of_rolls"]').val()) || 1;
            
            var squareFeetPerRoll = width * length; // Calculate square feet per roll (dimensions are in feet)
            var totalSquareFeet = squareFeetPerRoll * numberOfRolls; // Calculate total square feet for all rolls
            
            // Display the calculated square feet
            if (width > 0 && length > 0) {
                if (!$('#square-feet-per-roll-display').length) {
                    $('#add-roll-section .tile-body').append('<div id="square-feet-per-roll-display" class="alert alert-info mt-3">Each roll contains <strong>' + squareFeetPerRoll.toFixed(2) + '</strong> square feet of fabric.</div>');
                    $('#add-roll-section .tile-body').append('<div id="total-square-feet-display" class="alert alert-success mt-3">Total for all rolls: <strong>' + totalSquareFeet.toFixed(2) + '</strong> square feet of fabric.</div>');
                } else {
                    $('#square-feet-per-roll-display strong').text(squareFeetPerRoll.toFixed(2));
                    $('#total-square-feet-display strong').text(totalSquareFeet.toFixed(2));
                }
            }
        });
    });
</script>
@endpush