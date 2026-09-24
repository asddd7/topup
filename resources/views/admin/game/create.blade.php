<div class="modal fade"
     id="createGameModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-gamepad me-2"></i>
                    Tambah Game
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('admin.game.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-body">

                    {{-- Nama Game --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Nama Game
                        </label>

                        <input
                            type="text"
                            name="game_name"
                            class="form-control @error('game_name') is-invalid @enderror"
                            value="{{ old('game_name') }}"
                            placeholder="Contoh: Mobile Legends"
                            required>

                        @error('game_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Publisher --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Publisher
                        </label>

                        <input
                            type="text"
                            name="publisher"
                            class="form-control @error('publisher') is-invalid @enderror"
                            value="{{ old('publisher') }}"
                            placeholder="Contoh: Moonton">

                    </div>

                    {{-- =========================================================
                        ITEM CATEGORIES
                    ========================================================= --}}

                    <div class="mb-4">

                        <label class="form-label fw-bold">
                            <i class="fa-solid fa-layer-group me-1"></i>
                            Kategori Item
                        </label>

                        <div class="border rounded p-3 bg-light">

                                @forelse($categories as $category)

                                    <div class="form-check mb-2">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="category_ids[]"
                                            value="{{ $category->id }}"
                                            id="category_create_{{ $category->id }}"
                                            {{ in_array(
                                                $category->id,
                                                old('category_ids', [])
                                            ) ? 'checked' : '' }}
                                        >

                                        <label
                                            class="form-check-label"
                                            for="category_create_{{ $category->id }}"
                                        >

                                            {{ $category->category_name }}

                                            @if($category->use_qty)
                                                <small class="text-muted">
                                                    (Qty)
                                                </small>
                                            @endif

                                        </label>

                                    </div>

                                @empty

                                <div class="text-muted">
                                    Belum ada kategori item.
                                </div>

                            @endforelse

                        </div>

                        <small class="text-muted">
                            Pilih kategori yang dapat digunakan oleh game ini.
                        </small>

                    </div>
                                            
                        <hr>

                        <h6 class="fw-bold mb-3">
                            <i class="fa-solid fa-cloud me-2"></i>
                            MooGold Player Fields
                        </h6>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Product ID MooGold
                            </label>

                            <div class="input-group">

                                <input
                                    type="text"
                                    id="createMooGoldProductId"
                                    class="form-control"
                                    placeholder="Contoh: 4168026"
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-success"
                                    id="loadMooGoldFieldsCreate"
                                >
                                    <i class="fa-solid fa-download me-1"></i>
                                    Ambil Field
                                </button>

                            </div>

                            <small class="text-muted">
                                Hanya digunakan untuk mengambil field player dari MooGold.
                                Product ID tidak disimpan ke tabel games.
                            </small>

                        </div>

                    <hr>

                    <h6 class="fw-bold mb-3">

                    <i class="fa-solid fa-id-card me-2"></i>

                    Field Input Player

                    </h6>


                    <div id="playerFieldsCreate">


                    </div>


                    <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="addPlayerFieldCreate()">

                    <i class="fa fa-plus"></i>

                    Tambah Field

                    </button>


                    {{-- Logo --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Logo Game
                        </label>

                        <input
                            type="file"
                            name="game_logo"
                            class="form-control"
                            accept="image/*">

                        <small class="text-muted">
                            JPG, PNG, WEBP • Maksimal 2MB
                        </small>

                    </div>


                    {{-- Status --}}
                    <input
                        type="hidden"
                        name="is_active"
                        value="0">

                    <div class="form-check">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            id="is_active"
                            checked>

                        <label
                            class="form-check-label"
                            for="is_active">

                            Game Aktif

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
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>
                        Simpan Game

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

let fieldIndexCreate = 0;

const loadMooGoldFieldsCreate =
    document.getElementById(
        'loadMooGoldFieldsCreate'
    );

const createMooGoldProductId =
    document.getElementById(
        'createMooGoldProductId'
    );

const playerFieldsCreate =
    document.getElementById(
        'playerFieldsCreate'
    );

function getMooGoldCreateFieldType(field) {

    const normalized =
        String(field || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_');

    if (
        normalized === 'server' ||
        normalized === 'server_id'
    ) {
        return 'server';
    }

    if (
        normalized === 'uid' ||
        normalized === 'user_id' ||
        normalized === 'userid' ||
        normalized === 'role_id' ||
        normalized === 'player_id' ||
        normalized === 'account_id'
    ) {
        return 'text';
    }

    return 'text';
}

const type =
    getMooGoldCreateFieldType(originalField);

function normalizeMooGoldCreateField(field) {

    const normalized =
        String(field || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');


    if (
        [
            'uid',
            'user_id',
            'userid',
            'role_id',
            'player_id',
            'account_id',
            'game_id'
        ].includes(normalized)
    ) {
        return 'user_id';
    }


    if (
        [
            'server',
            'server_id',
            'zone',
            'zone_id',
            'region',
            'region_id',
            'world',
            'world_id'
        ].includes(normalized)
    ) {
        return 'server';
    }


    return normalized || 'player_field';
}

loadMooGoldFieldsCreate?.addEventListener(
    'click',
    async function () {

        const productId =
            createMooGoldProductId.value.trim();


        if (!productId) {

            if (window.Swal) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Product ID Kosong',
                    text:
                        'Masukkan Product ID MooGold terlebih dahulu.'
                });

            } else {

                alert(
                    'Masukkan Product ID MooGold terlebih dahulu.'
                );

            }

            return;
        }


        const originalHtml =
            this.innerHTML;

        this.disabled = true;

        this.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
            ></span>
            Mengambil Field...
        `;


        try {

            const response =
                await fetch(
                    `/api/v1/admin/moogold/product/${productId}`,
                    {
                        headers: {
                            Accept:
                                'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest'
                        },
                        credentials:
                            'same-origin'
                    }
                );


            const data =
                await response.json();


            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.message ||
                    'Gagal mengambil product MooGold.'
                );

            }


            const fields =
                data?.data?.fields ?? [];


            playerFieldsCreate.innerHTML = '';


            fields.forEach(
                function (moogoldField, index) {

                    const originalField =
                        String(
                            moogoldField || ''
                        ).trim();

                    if (!originalField) {
                        return;
                    }


                    const internalName =
                        normalizeMooGoldCreateField(
                            originalField
                        );


                    const card =
                        document.createElement(
                            'div'
                        );

                    card.className =
                        'card mb-3 player-field';


                    card.innerHTML = `
                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Nama Field
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="player_fields[${index}][name]"
                                        value="${internalName}"
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Label
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="player_fields[${index}][label]"
                                        value="${originalField}"
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Placeholder
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="player_fields[${index}][placeholder]"
                                        value="Masukkan ${originalField}"
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Options
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="player_fields[${index}][options]"
                                        value=""
                                        placeholder="Kosongkan jika tidak ada"
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Tipe
                                    </label>

                                    <select
                                        class="form-select"
                                        name="player_fields[${index}][type]"
                                    >
                                        <option
                                            value="text"
                                            ${type === 'text' ? 'selected' : ''}
                                        >
                                            Text
                                        </option>

                                        <option value="number">
                                            Number
                                        </option>

                                        <option value="email">
                                            Email
                                        </option>

                                        <option value="select">
                                            Select
                                        </option>

                                        <option
                                            value="server"
                                            ${type === 'server' ? 'selected' : ''}
                                        >
                                            Server
                                        </option>

                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">
                                        Source
                                    </label>

                                    <select
                                        class="form-select"
                                        name="player_fields[${index}][source]"
                                    >
                                        <option value="manual" selected>
                                            Manual
                                        </option>

                                        <option value="moogold_server_list">
                                            MooGold Server List
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <button
                                        type="button"
                                        class="btn btn-danger remove-field"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>

                            </div>

                            <div class="form-check mt-3">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="player_fields[${index}][required]"
                                    value="1"
                                    checked
                                >

                                <label class="form-check-label">
                                    Wajib Diisi
                                </label>

                            </div>

                            <input
                                type="hidden"
                                name="player_fields[${index}][moogold_field]"
                                value="${originalField}"
                            >

                        </div>
                    `;


                    playerFieldsCreate.appendChild(
                        card
                    );

                }
            );


            fieldIndexCreate =
                fields.length;


            if (window.Swal) {

                Swal.fire({
                    icon: 'success',
                    title: 'Field Berhasil Diambil',
                    text:
                        `${fields.length} field ditemukan.`,
                    timer: 1500,
                    showConfirmButton: false
                });

            }


        } catch (error) {

            console.error(
                '[MooGold Fields Create]',
                error
            );


            if (window.Swal) {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text:
                        error.message ||
                        'Gagal mengambil field MooGold.'
                });

            } else {

                alert(
                    error.message ||
                    'Gagal mengambil field MooGold.'
                );

            }


        } finally {

            this.disabled = false;

            this.innerHTML =
                originalHtml;

        }

    }
);


function addPlayerFieldCreate(){

    let i = fieldIndexCreate++;


    document
        .getElementById('playerFieldsCreate')
        .insertAdjacentHTML(
            'beforeend',

            `

            <div class="card mb-3 player-field">

                <div class="card-body">

                    <div class="row g-3">


                        <div class="col-12 col-md-3">

                            <label class="form-label">
                                Nama Field
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="player_fields[${i}][name]"
                                placeholder="uid">

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label">
                                Label
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="player_fields[${i}][label]"
                                placeholder="UID Player">

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label">
                                Placeholder
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="player_fields[${i}][placeholder]"
                                placeholder="Masukkan UID">

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label">
                                Options
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="player_fields[${i}][options]"
                                placeholder="Asia,America,Europe">

                            <small class="text-muted">
                                Pisahkan dengan koma
                            </small>

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label">
                                Tipe
                            </label>

                            <select
                                class="form-select"
                                name="player_fields[${i}][type]">

                                    <option
                                        value="text"
                                        ${type === 'text' ? 'selected' : ''}
                                    >
                                        Text
                                    </option>

                                    <option value="number">
                                        Number
                                    </option>

                                    <option value="email">
                                        Email
                                    </option>

                                    <option value="select">
                                        Select
                                    </option>

                                    <option
                                        value="server"
                                        ${type === 'server' ? 'selected' : ''}
                                    >
                                        Server
                                    </option>

                            </select>

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label">
                                Source
                            </label>

                            <select
                                class="form-select"
                                name="player_fields[${i}][source]">

                                <option value="manual">
                                    Manual
                                </option>

                                <option value="moogold_server_list">
                                    MooGold Server List
                                </option>

                            </select>

                            <small class="text-muted">
                                Sumber pilihan field
                            </small>

                        </div>


                        <div class="col-12 col-md-1 d-flex align-items-end">

                            <button
                                type="button"
                                class="btn btn-danger remove-field">

                                <i class="fa fa-trash"></i>

                            </button>

                        </div>


                    </div>


                    <div class="form-check mt-3">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="player_fields[${i}][required]"
                            value="1"
                            checked>

                        <label class="form-check-label">

                            Wajib Diisi

                        </label>

                    </div>


                </div>

            </div>

            `
        );

}


document.addEventListener('click', function(e){

    if(e.target.closest('.remove-field')){

        e.target.closest('.player-field').remove();

    }

});

</script>