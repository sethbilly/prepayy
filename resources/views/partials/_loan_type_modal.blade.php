{{--Loan type modal--}}
<div class="modal fade"
     id="loan-type-modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="loan-type-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                Add Loan Type
            </div>
            <form method="post" action="{{route('loan_products.types.store')}}">
                {!! csrf_field() !!}
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="name-input">Loan Type</label>
                        <input type="text" id="name-input" class="form-control" name="name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Edit loan type modal --}}
<div class="modal fade"
     id="edit-loan-type-modal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="edit-loan-type-label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                Edit Loan Type
            </div>
            <form method="post">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="put">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="name-input">Loan Type</label>
                        <input type="text" id="name-input" class="form-control" name="name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>