<div class="modal fade"
id="createCategoryModal"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="modal-header bg-primary text-white">

<h5 class="modal-title">

Tambah Kategori

</h5>

<button
class="btn-close btn-close-white"
data-bs-dismiss="modal">

</button>

</div>

<form
action="{{ route('admin.item-category.store') }}"
method="POST">

@csrf

<div class="modal-body">

<label class="form-label">

Nama Kategori

</label>

<input
type="text"
name="category_name"
class="form-control"
required>


<div class="form-check form-switch">

    <input
        type="checkbox"
        class="form-check-input"
        name="use_qty"
        value="1"
        checked>

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
class="btn btn-primary">

Simpan

</button>

</div>

</form>

</div>

</div>

</div>