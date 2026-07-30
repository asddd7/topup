<div class="modal fade"
     id="createBannerModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content shadow border-0 rounded-4">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">

                    <i class="fa-solid fa-images me-2"></i>

                    Tambah Banner

                </h5>

                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">

                </button>

            </div>

            <form action="{{ route('admin.banner.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">

                                    Judul Banner

                                </label>

                                <input
                                    type="text"
                                    name="title"
                                    class="form-control"
                                    required>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">

                                    Game

                                </label>

                                <select
                                    name="game_id"
                                    class="form-select">

                                    <option value="">

                                        Semua Game

                                    </option>

                                    @foreach($games as $game)

                                        <option value="{{ $game->id }}">

                                            {{ $game->game_name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Link Banner

                        </label>
                        <input
                            type="url"
                            name="link"
                            class="form-control"
                            placeholder="https://">
                        <small class="text-muted">

                        Jika Game dipilih, Link akan diabaikan.
                        Jika kosong, banner hanya gambar.

                        </small>
                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Deskripsi

                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            class="form-control"></textarea>

                    </div>

                    <div class="row">

                        <div class="col-md-6">

                            <div class="mb-3">

                                <label class="form-label">

                                    Urutan Banner

                                </label>

                                <input
                                    type="number"
                                    name="sort_order"
                                    value="0"
                                    class="form-control">

                            </div>

                        </div>

                        <div class="col-md-6 d-flex align-items-center">

                            <div class="form-check mt-3">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    checked>

                                <label class="form-check-label">

                                    Banner Aktif

                                </label>

                            </div>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Upload Banner

                        </label>

                        <input
                            type="file"
                            name="image"
                            id="bannerImage"
                            class="form-control"
                            accept="image/*"
                            onchange="previewBanner(event)"
                            required>

                    </div>

                    <div class="text-center">

                        <img
                            id="bannerPreview"
                            class="banner-preview d-none">

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

function previewBanner(event){

    let image=document.getElementById('bannerPreview');

    image.src=URL.createObjectURL(event.target.files[0]);

    image.classList.remove('d-none');

}

</script>