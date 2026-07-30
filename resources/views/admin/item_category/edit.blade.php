<div class="modal fade"
id="editCategoryModal{{ $category->id }}"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="modal-header bg-warning">

<h5 class="modal-title">

Edit Kategori

</h5>

<button
class="btn-close"
data-bs-dismiss="modal">

</button>

</div>

<form
action="{{ route('admin.item-category.update',$category->id) }}"
method="POST">

@csrf
@method('PUT')

<div class="modal-body">

<label class="form-label">

Nama Kategori

</label>

<input
type="text"
name="category_name"
class="form-control"
value="{{ $category->category_name }}"
required>


<div class="form-check form-switch mt-3">

    <input
        class="form-check-input"
        type="checkbox"
        name="use_qty"
        value="1"
        {{ $category->use_qty ? 'checked' : '' }}>

    <label class="form-check-label">

        Gunakan Qty pada Nama Item

    </label>

</div>
</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Batal

</button>

<button
class="btn btn-warning">

Update

</button>

</div>

</form>

</div>

</div>

</div>