<!-- Create Item Modal -->
<div class="modal fade"
     id="createItemModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-box me-2"></i>
                    Tambah Item
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>


            
            <form action="{{ route('admin.game.items.store',$game->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-body">

                    {{-- =========================
                        INFORMASI GAME
                    ========================== --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Informasi Game
                    </h6>

                    <div class="mb-3">

                        <label class="form-label">
                            Game
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $game->game_name }}"
                            readonly>

                        <input
                            type="hidden"
                            name="game_id"
                            value="{{ $game->id }}">

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Kategori
                        </label>

                    <select
                        id="category_id"
                        name="category_id"
                        class="form-select">

                    @foreach($categories as $category)

                    <option
                    value="{{ $category->id }}"
                    data-useqty="{{ $category->use_qty }}">

                    {{ $category->category_name }}

                    </option>

                    @endforeach

                    </select>
        
                    </div>

                    <hr>

                    {{-- =========================
                        DETAIL ITEM
                    ========================== --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Detail Item
                    </h6>

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Item
                        </label>
                    <input
                        type="text"
                        id="item_name"
                        name="item_name"
                        class="form-control"
                        placeholder="Nama item"
                        required>

                    </div>

                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Qty
                            </label>

                            <input
                                type="number"
                                id="qty"
                                name="qty"
                                class="form-control"
                                value="1"
                                min="1"
                                required>

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Harga
                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                placeholder="20000"
                                required>

                        </div>

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Stock
                            </label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                value="0">

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Deskripsi
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Deskripsi item..."></textarea>

                    </div>

                    <hr>

                    {{-- =========================
                        GAMBAR
                    ========================== --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Gambar
                    </h6>

                    <div class="mb-3">

                        <label class="form-label">
                            Upload Gambar
                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control">

                        <small class="text-muted">
                            Format JPG / PNG (disarankan rasio 1:1)
                        </small>

                    </div>

                    <hr>

                    {{-- =========================
                        STATUS
                    ========================== --}}

                    <h6 class="fw-bold text-primary mb-3">
                        Pengaturan
                    </h6>

                    <div class="form-check form-switch mb-2">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="top_seller"
                            value="1">

                        <label class="form-check-label">
                            Jadikan Top Seller
                        </label>

                    </div>

                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked>

                        <label class="form-check-label">
                            Item Aktif
                        </label>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>

                        Simpan Item

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</div>        

<script>

function generateItemName(){

    let qty = document.getElementById('qty').value;

    let select = document.getElementById('category_id');

    let option = select.options[select.selectedIndex];

    let category = option.text;

    let useQty = option.dataset.useqty;

    if(select.value==""){

        document.getElementById('item_name').value="";

        return;

    }

    if(useQty=="1"){

        document.getElementById('item_name').value =
            qty+" "+category;

    }else{

        document.getElementById('item_name').value =
            category;

    }

}

document.getElementById('qty')
.addEventListener('input',generateItemName);

document.getElementById('category_id')
.addEventListener('change',generateItemName);

</script>