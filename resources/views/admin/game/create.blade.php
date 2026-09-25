<div
    class="modal fade"
    id="createGameModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            {{-- =========================================================
                HEADER
            ========================================================= --}}

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-gamepad me-2"></i>
                    Tambah Game
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            {{-- =========================================================
                FORM
            ========================================================= --}}

            <form
                action="{{ route('admin.game.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf

                <div class="modal-body">

                    {{-- =====================================================
                        NAMA GAME
                    ===================================================== --}}

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
                            required
                        >

                        @error('game_name')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- =====================================================
                        PUBLISHER
                    ===================================================== --}}

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Publisher
                        </label>

                        <input
                            type="text"
                            name="publisher"
                            class="form-control @error('publisher') is-invalid @enderror"
                            value="{{ old('publisher') }}"
                            placeholder="Contoh: Moonton"
                        >

                        @error('publisher')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>


                    {{-- =====================================================
                        ITEM CATEGORIES
                    ===================================================== --}}

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

                            Pilih kategori yang dapat digunakan
                            oleh game ini.

                        </small>

                    </div>


                    <hr>


                    {{-- =====================================================
                        MOO GOLD PLAYER FIELD IMPORT
                    ===================================================== --}}

                    <h6 class="fw-bold mb-3">

                        <i class="fa-solid fa-cloud me-2"></i>

                        MooGold Player Fields

                    </h6>


                    <div class="mb-3">

                        <label
                            class="form-label fw-semibold"
                        >
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

                                <i
                                    class="fa-solid fa-download me-1"
                                ></i>

                                Ambil Field

                            </button>

                        </div>


                        <small class="text-muted">

                            Hanya digunakan untuk mengambil
                            field player dari MooGold.
                            Product ID tidak disimpan ke tabel games.

                        </small>

                    </div>


                    <hr>


                    {{-- =====================================================
                        PLAYER FIELDS
                    ===================================================== --}}

                    <h6 class="fw-bold mb-3">

                        <i class="fa-solid fa-id-card me-2"></i>

                        Field Input Player

                    </h6>


                    <div id="playerFieldsCreate">

                    </div>


                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        onclick="addPlayerFieldCreate()"
                    >

                        <i class="fa fa-plus me-1"></i>

                        Tambah Field

                    </button>


                    {{-- =====================================================
                        LOGO
                    ===================================================== --}}

                    <div class="mb-3 mt-4">

                        <label class="form-label fw-semibold">
                            Logo Game
                        </label>


                        <input
                            type="file"
                            name="game_logo"
                            class="form-control"
                            accept="image/*"
                        >


                        <small class="text-muted">

                            JPG, PNG, WEBP • Maksimal 2MB

                        </small>

                    </div>


                    {{-- =====================================================
                        STATUS
                    ===================================================== --}}

                    <input
                        type="hidden"
                        name="is_active"
                        value="0"
                    >


                    <div class="form-check">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            id="create_game_is_active"
                            checked
                        >


                        <label
                            class="form-check-label"
                            for="create_game_is_active"
                        >

                            Game Aktif

                        </label>

                    </div>

                </div>


                {{-- =========================================================
                    FOOTER
                ========================================================= --}}

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >

                        Batal

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i
                            class="fa-solid fa-save me-1"
                        ></i>

                        Simpan Game

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
/*
|--------------------------------------------------------------------------
| PLAYER FIELD STATE
|--------------------------------------------------------------------------
*/

let fieldIndexCreate = 0;


/*
|--------------------------------------------------------------------------
| ELEMENTS
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| NORMALIZE MOO GOLD FIELD NAME
|--------------------------------------------------------------------------
*/

function normalizeMooGoldCreateField(
    field,
    index
) {

    const normalized =
        String(field || '')
            .trim()
            .toLowerCase()
            .replace(
                /[^a-z0-9]+/g,
                '_'
            )
            .replace(
                /^_+|_+$/g,
                ''
            );


    /*
    |--------------------------------------------------------------------------
    | USER ID
    |--------------------------------------------------------------------------
    */

    if (
        [
            'uid',
            'user_id',
            'userid',
            'role_id',
            'player_id',
            'account_id',
            'game_id',
        ].includes(normalized)
    ) {
        return 'user_id';
    }


    /*
    |--------------------------------------------------------------------------
    | SERVER / REGION
    |--------------------------------------------------------------------------
    */

    if (
        [
            'server',
            'server_id',
            'region',
            'region_id',
        ].includes(normalized)
    ) {
        return 'server';
    }


    /*
    |--------------------------------------------------------------------------
    | GENERIC
    |--------------------------------------------------------------------------
    */

    return (
        normalized ||
        `field_${index + 1}`
    );
}


/*
|--------------------------------------------------------------------------
| DETERMINE TYPE
|--------------------------------------------------------------------------
*/

function getMooGoldCreateFieldType(
    field
) {

    const normalized =
        String(field || '')
            .trim()
            .toLowerCase()
            .replace(
                /[^a-z0-9]+/g,
                '_'
            )
            .replace(
                /^_+|_+$/g,
                ''
            );


    /*
    |--------------------------------------------------------------------------
    | SERVER / REGION
    |--------------------------------------------------------------------------
    */

    if (
        [
            'server',
            'server_id',
            'region',
            'region_id',
        ].includes(normalized)
    ) {
        return 'server';
    }


    return 'text';
}


/*
|--------------------------------------------------------------------------
| DETERMINE DEFAULT INPUT MODE
|--------------------------------------------------------------------------
|
| User ID:
|     input
|
| Server / Region:
|     input
|
| Admin dapat mengubah server menjadi readonly.
|
*/

function getMooGoldCreateInputMode(
    internalName,
    type
) {

    if (
        internalName === 'user_id'
    ) {
        return 'input';
    }

    return 'input';
}


/*
|--------------------------------------------------------------------------
| RENDER MOO GOLD FIELDS
|--------------------------------------------------------------------------
*/

function renderMooGoldFieldsCreate(
    fields
) {

    playerFieldsCreate.innerHTML = '';


    if (
        !Array.isArray(fields) ||
        fields.length === 0
    ) {

        playerFieldsCreate.innerHTML = `
            <div class="alert alert-warning">

                <i
                    class="fa-solid fa-triangle-exclamation me-1"
                ></i>

                MooGold tidak mengembalikan
                field player untuk product ini.

            </div>
        `;

        return;
    }


    fields.forEach(
        function (
            moogoldField,
            index
        ) {

            const originalField =
                String(
                    moogoldField || ''
                ).trim();


            if (!originalField) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | FIELD DATA
            |--------------------------------------------------------------------------
            */

            const internalName =
                normalizeMooGoldCreateField(
                    originalField,
                    index
                );


            const type =
                getMooGoldCreateFieldType(
                    originalField
                );


            const inputMode =
                getMooGoldCreateInputMode(
                    internalName,
                    type
                );


            const placeholder =
                `Masukkan ${originalField}`;


            /*
            |--------------------------------------------------------------------------
            | CARD
            |--------------------------------------------------------------------------
            */

            const card =
                document.createElement(
                    'div'
                );

            card.className =
                'card mb-3 player-field';


            /*
            |--------------------------------------------------------------------------
            | HTML
            |--------------------------------------------------------------------------
            */

            card.innerHTML = `

                <div class="card-body">

                    <div class="row g-3">


                        {{-- NAME --}}

                        <div class="col-12 col-md-3">

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


                        {{-- LABEL --}}

                        <div class="col-12 col-md-3">

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


                        {{-- PLACEHOLDER --}}

                        <div class="col-12 col-md-3">

                            <label class="form-label">

                                Placeholder

                            </label>


                            <input
                                type="text"
                                class="form-control"
                                name="player_fields[${index}][placeholder]"
                                value="${placeholder}"
                            >

                        </div>


                        {{-- OPTIONS --}}

                        <div class="col-12 col-md-3">

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


                            <small class="text-muted">

                                Pisahkan dengan koma

                            </small>

                        </div>


                        {{-- TYPE --}}

                        <div class="col-12 col-md-3">

                            <label class="form-label">

                                Tipe

                            </label>


                            <select
                                class="form-select player-field-type"
                                name="player_fields[${index}][type]"
                            >

                                <option
                                    value="text"
                                    ${type === 'text'
                                        ? 'selected'
                                        : ''}
                                >
                                    Text
                                </option>


                                <option
                                    value="number"
                                    ${type === 'number'
                                        ? 'selected'
                                        : ''}
                                >
                                    Number
                                </option>


                                <option
                                    value="email"
                                    ${type === 'email'
                                        ? 'selected'
                                        : ''}
                                >
                                    Email
                                </option>


                                <option
                                    value="select"
                                    ${type === 'select'
                                        ? 'selected'
                                        : ''}
                                >
                                    Select
                                </option>


                                <option
                                    value="server"
                                    ${type === 'server'
                                        ? 'selected'
                                        : ''}
                                >
                                    Server
                                </option>

                            </select>

                        </div>


                        {{-- SOURCE --}}

                        <div class="col-12 col-md-3">

                            <label class="form-label">

                                Source

                            </label>


                            <select
                                class="form-select player-field-source"
                                name="player_fields[${index}][source]"
                            >

                                <option
                                    value="manual"
                                    ${
                                        type !== 'server'
                                            ? 'selected'
                                            : ''
                                    }
                                >
                                    Manual
                                </option>


                                <option
                                    value="moogold_server_list"
                                    ${
                                        type === 'server'
                                            ? 'selected'
                                            : ''
                                    }
                                >
                                    MooGold Server List
                                </option>

                            </select>


                            <small class="text-muted">

                                Sumber pilihan field

                            </small>

                        </div>


                        {{-- INPUT MODE --}}

                        <div class="col-12 col-md-3">

                            <label class="form-label">

                                Mode Input

                            </label>


                            ${
                                internalName === 'user_id'
                                    ? `

                                        <input
                                            type="text"
                                            class="form-control"
                                            value="Bisa Diinput"
                                            readonly
                                        >


                                        <input
                                            type="hidden"
                                            name="player_fields[${index}][input_mode]"
                                            value="input"
                                        >

                                    `
                                    : `

                                        <select
                                            class="form-select player-field-input-mode"
                                            name="player_fields[${index}][input_mode]"
                                        >

                                            <option
                                                value="input"
                                                selected
                                            >
                                                Bisa Diinput
                                            </option>


                                            <option
                                                value="readonly"
                                            >
                                                Read Only
                                            </option>

                                        </select>

                                    `
                            }

                        </div>


                        {{-- REMOVE --}}

                        <div
                            class="col-12 col-md-1 d-flex align-items-end"
                        >

                            <button
                                type="button"
                                class="btn btn-danger remove-field"
                            >

                                <i class="fa fa-trash"></i>

                            </button>

                        </div>

                    </div>


                    {{-- REQUIRED --}}

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


                    {{-- ORIGINAL MOOGOLD FIELD --}}

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
}


/*
|--------------------------------------------------------------------------
| LOAD MOO GOLD PLAYER FIELDS
|--------------------------------------------------------------------------
*/

loadMooGoldFieldsCreate?.addEventListener(
    'click',
    async function () {

        const productId =
            createMooGoldProductId
                ?.value
                ?.trim();


        /*
        |--------------------------------------------------------------------------
        | PRODUCT ID CHECK
        |--------------------------------------------------------------------------
        */

        if (!productId) {

            if (
                window.Swal
            ) {

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


        /*
        |--------------------------------------------------------------------------
        | BUTTON STATE
        |--------------------------------------------------------------------------
        */

        const originalHtml =
            this.innerHTML;


        this.disabled = true;


        this.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
            ></span>

            Mengambil Field...
        `;


        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */

        try {

            const response =
                await fetch(
                    `/api/v1/admin/moogold/product/${productId}`,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        credentials:
                            'same-origin'
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | FIELDS
            |--------------------------------------------------------------------------
            */

            const fields =
                data?.data?.fields
                ?? [];


            renderMooGoldFieldsCreate(
                fields
            );


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            if (
                window.Swal
            ) {

                Swal.fire({
                    icon: 'success',
                    title:
                        'Field Berhasil Diambil',
                    text:
                        `${fields.length} field ditemukan.`,
                    timer: 1500,
                    showConfirmButton: false
                });

            }

        } catch (
            error
        ) {

            console.error(
                '[MooGold Fields Create]',
                error
            );


            if (
                window.Swal
            ) {

                Swal.fire({
                    icon: 'error',
                    title:
                        'Gagal Mengambil Field',
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

            this.disabled =
                false;

            this.innerHTML =
                originalHtml;

        }

    }
);


/*
|--------------------------------------------------------------------------
| ADD MANUAL FIELD
|--------------------------------------------------------------------------
*/

function addPlayerFieldCreate()
{
    const i =
        fieldIndexCreate++;


    playerFieldsCreate
        .insertAdjacentHTML(
            'beforeend',
            `
                <div class="card mb-3 player-field">

                    <div class="card-body">

                        <div class="row g-3">


                            {{-- NAME --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Nama Field

                                </label>


                                <input
                                    type="text"
                                    class="form-control"
                                    name="player_fields[${i}][name]"
                                    placeholder="uid"
                                >

                            </div>


                            {{-- LABEL --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Label

                                </label>


                                <input
                                    type="text"
                                    class="form-control"
                                    name="player_fields[${i}][label]"
                                    placeholder="UID Player"
                                >

                            </div>


                            {{-- PLACEHOLDER --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Placeholder

                                </label>


                                <input
                                    type="text"
                                    class="form-control"
                                    name="player_fields[${i}][placeholder]"
                                    placeholder="Masukkan UID"
                                >

                            </div>


                            {{-- OPTIONS --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Options

                                </label>


                                <input
                                    type="text"
                                    class="form-control"
                                    name="player_fields[${i}][options]"
                                    placeholder="Asia,America,Europe"
                                >


                                <small class="text-muted">

                                    Pisahkan dengan koma

                                </small>

                            </div>


                            {{-- TYPE --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Tipe

                                </label>


                                <select
                                    class="form-select player-field-type"
                                    name="player_fields[${i}][type]"
                                >

                                    <option value="text">
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


                                    <option value="server">
                                        Server
                                    </option>

                                </select>

                            </div>


                            {{-- SOURCE --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Source

                                </label>


                                <select
                                    class="form-select player-field-source"
                                    name="player_fields[${i}][source]"
                                >

                                    <option value="manual">
                                        Manual
                                    </option>


                                    <option value="moogold_server_list">
                                        MooGold Server List
                                    </option>

                                </select>


                                <small class="text-muted">

                                    Sumber field

                                </small>

                            </div>


                            {{-- INPUT MODE --}}

                            <div class="col-12 col-md-3">

                                <label class="form-label">

                                    Mode Input

                                </label>


                                <select
                                    class="form-select player-field-input-mode"
                                    name="player_fields[${i}][input_mode]"
                                >

                                    <option
                                        value="input"
                                        selected
                                    >
                                        Bisa Diinput
                                    </option>


                                    <option
                                        value="readonly"
                                    >
                                        Read Only
                                    </option>

                                </select>


                                <small class="text-muted">

                                    Cara customer mengisi field

                                </small>

                            </div>


                            {{-- REMOVE --}}

                            <div
                                class="col-12 col-md-1 d-flex align-items-end"
                            >

                                <button
                                    type="button"
                                    class="btn btn-danger remove-field"
                                >

                                    <i class="fa fa-trash"></i>

                                </button>

                            </div>

                        </div>


                        {{-- REQUIRED --}}

                        <div class="form-check mt-3">

                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="player_fields[${i}][required]"
                                value="1"
                                checked
                            >


                            <label class="form-check-label">

                                Wajib Diisi

                            </label>

                        </div>

                    </div>

                </div>
            `
        );
}


/*
|--------------------------------------------------------------------------
| MODE INPUT CHANGE
|--------------------------------------------------------------------------
|
| Bila Type = Server:
|
| Read Only
|     -> MooGold Server List
|
| Bisa Diinput
|     -> Manual
|
*/

playerFieldsCreate?.addEventListener(
    'change',
    function (event) {

        /*
        |--------------------------------------------------------------------------
        | INPUT MODE
        |--------------------------------------------------------------------------
        */

        if (
            event.target.classList.contains(
                'player-field-input-mode'
            )
        ) {

            const card =
                event.target.closest(
                    '.player-field'
                );


            if (!card) {
                return;
            }


            const mode =
                event.target.value;


            const typeSelect =
                card.querySelector(
                    '.player-field-type'
                );


            const sourceSelect =
                card.querySelector(
                    '.player-field-source'
                );


            if (
                !typeSelect ||
                !sourceSelect
            ) {
                return;
            }


            if (
                typeSelect.value ===
                'server'
            ) {

                sourceSelect.value =
                    mode === 'readonly'
                        ? 'moogold_server_list'
                        : 'manual';
            }

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | TYPE CHANGE
        |--------------------------------------------------------------------------
        |
        | Bila admin mengubah Type menjadi Server,
        | Source mengikuti Mode Input.
        |
        */

        if (
            event.target.classList.contains(
                'player-field-type'
            )
        ) {

            const card =
                event.target.closest(
                    '.player-field'
                );


            if (!card) {
                return;
            }


            const type =
                event.target.value;


            const modeSelect =
                card.querySelector(
                    '.player-field-input-mode'
                );


            const sourceSelect =
                card.querySelector(
                    '.player-field-source'
                );


            if (
                !modeSelect ||
                !sourceSelect
            ) {
                return;
            }


            if (
                type === 'server'
            ) {

                sourceSelect.value =
                    modeSelect.value ===
                    'readonly'
                        ? 'moogold_server_list'
                        : 'manual';

            }

        }

    }
);


/*
|--------------------------------------------------------------------------
| REMOVE FIELD
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function (event) {

        const removeButton =
            event.target.closest(
                '.remove-field'
            );


        if (!removeButton) {
            return;
        }


        const fieldCard =
            removeButton.closest(
                '.player-field'
            );


        if (
            fieldCard
        ) {
            fieldCard.remove();
        }

    }
);

</script>