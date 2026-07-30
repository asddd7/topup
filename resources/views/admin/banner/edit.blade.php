<div class="modal fade"
     id="editBannerModal{{ $banner->id }}"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content shadow border-0 rounded-4">

            <div class="modal-header bg-warning">

                <h5 class="modal-title">

                    <i class="fa-solid fa-pen-to-square me-2"></i>

                    Edit Banner

                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('admin.banner.update',$banner->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

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
                                    value="{{ $banner->title }}"
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

                                        <option
                                            value="{{ $game->id }}"
                                            {{ $banner->game_id == $game->id ? 'selected' : '' }}>

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
                            value="{{ $banner->link }}">

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Deskripsi

                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            class="form-control">{{ $banner->description }}</textarea>

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
                                    class="form-control"
                                    value="{{ $banner->sort_order }}">

                            </div>

                        </div>

                        <div class="col-md-6 d-flex align-items-center">

                            <div class="form-check mt-3">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ $banner->is_active ? 'checked' : '' }}>

                                <label class="form-check-label">

                                    Banner Aktif

                                </label>

                            </div>

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Banner Saat Ini

                        </label>

                        <div class="text-center">

                            <img
                                src="{{ asset('storage/'.$banner->image) }}"
                                id="previewBanner{{ $banner->id }}"
                                class="banner-preview">

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Ganti Banner

                        </label>

                        <input
                            type="file"
                            name="image"
                            class="form-control"
                            accept="image/*"
                            onchange="previewEditBanner(event,{{ $banner->id }})">

                        <small class="text-muted">

                            Kosongkan jika tidak ingin mengganti gambar.

                        </small>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        class="btn btn-warning">

                        <i class="fa-solid fa-floppy-disk me-1"></i>

                        Update

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
<script>

function previewEditBanner(event,id){

    let image=document.getElementById('previewBanner'+id);

    image.src=URL.createObjectURL(event.target.files[0]);

}

</script>