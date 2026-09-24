<div class="modal fade"
     id="editGameModal{{ $game->id }}"
     tabindex="-1">


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content shadow border-0 rounded-4">


<div class="modal-header bg-warning">


<h5 class="modal-title">

<i class="fa-solid fa-gamepad me-2"></i>

Edit Game

</h5>


<button type="button"
        class="btn-close"
        data-bs-dismiss="modal">

</button>


</div>



<form action="{{route('admin.game.update',$game->id)}}"
      method="POST"
      enctype="multipart/form-data">


@csrf

@method('PUT')



<div class="modal-body">



<div class="mb-3">


<label class="form-label">

Nama Game

</label>


<input type="text"
       name="game_name"
       class="form-control"
       value="{{ $game->game_name }}"
       required>


</div>




<div class="mb-3">


<label class="form-label">

Publisher

</label>


<input type="text"
       name="publisher"
       class="form-control"
       value="{{ $game->publisher }}">


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

            @php
                $selectedCategoryIds = $game->itemCategories
                    ->pluck('id')
                    ->toArray();
            @endphp

            <div class="form-check mb-2">

                <input
                    class="form-check-input"
                    type="checkbox"
                    name="category_ids[]"
                    value="{{ $category->id }}"
                    id="category_edit_{{ $game->id }}_{{ $category->id }}"
                    {{ in_array(
                        $category->id,
                        old('category_ids', $selectedCategoryIds)
                    ) ? 'checked' : '' }}
                >

                <label
                    class="form-check-label"
                    for="category_edit_{{ $game->id }}_{{ $category->id }}"
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

{{-- =========================================================
    MOOGOLD SERVER
========================================================= --}}

<div class="mb-4">

    <label class="form-label fw-bold">
        <i class="fa-solid fa-server me-1"></i>
        Server MooGold
    </label>

    @if($items->isNotEmpty())

        <div class="mb-3">

            <label class="form-label">
                Produk / Item Sumber Server
            </label>

            <select
                id="moogoldItem{{ $game->id }}"
                class="form-select">

                <option value="">
                    Pilih item...
                </option>

                @foreach($items as $item)

                    <option
                        value="{{ $item->id }}"
                        data-product-id="{{ $item->moogold_product_id }}">

                        {{ $item->item_name }}
                        (Product ID: {{ $item->moogold_product_id }})

                    </option>

                @endforeach

            </select>

            <small class="text-muted">
                Server akan diambil dari MooGold berdasarkan product ID item ini.
            </small>

        </div>

        <div class="mb-3">

            <label class="form-label">
                Server
            </label>

            <select
                name="moogold_server_id"
                id="moogoldServer{{ $game->id }}"
                class="form-select">

                @if($game->moogold_server_id)

                    <option
                        value="{{ $game->moogold_server_id }}"
                        selected>

                        {{ $game->moogold_server_name }}
                        ({{ $game->moogold_server_id }})

                    </option>

                @else

                    <option value="">
                        Belum memilih server
                    </option>

                @endif

            </select>

            <input
                type="hidden"
                name="moogold_server_name"
                id="moogoldServerName{{ $game->id }}"
                value="{{ $game->moogold_server_name }}">

            <small class="text-muted">
                Satu game hanya dapat memiliki satu server.
            </small>

        </div>

        <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            id="loadMooGoldServers{{ $game->id }}">

            <i class="fa-solid fa-cloud-arrow-down me-1"></i>
            Ambil Server MooGold

        </button>

    @else

        <div class="alert alert-warning mb-0">

            <i class="fa-solid fa-triangle-exclamation me-1"></i>

            Belum ada item dengan
            <code>moogold_product_id</code>
            untuk game ini.

            Buat atau mapping item MooGold terlebih dahulu.

        </div>

    @endif

</div>

<hr>

<h6 class="fw-bold mb-3">

<i class="fa-solid fa-id-card me-2"></i>

Field Input Player

</h6>

<div class="mb-3">
    <button
        type="button"
        class="btn btn-outline-success btn-sm"
        id="loadMooGoldFields{{ $game->id }}"
    >
        <i class="fa-solid fa-download me-1"></i>
        Ambil Field dari MooGold
    </button>

    <small class="text-muted d-block mt-1">
        Field akan diambil dari Product ID MooGold pada item sumber.
    </small>
</div>

<div id="playerFields{{ $game->id }}">

@php

$fields = $game->player_fields ?? [];

@endphp

@foreach($fields as $i => $field)

<div class="card mb-3 player-field">

<div class="card-body">

<div class="row">

<div class="col-md-2">

<label>Nama Field</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][name]"
value="{{ $field['name'] ?? '' }}"
placeholder="uid">

</div>

<div class="col-md-2">

<label>Label</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][label]"
value="{{ $field['label'] ?? '' }}"
placeholder="UID Player">

</div>

<div class="col-md-2">

<label>Placeholder</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][placeholder]"
value="{{ $field['placeholder'] ?? '' }}"
placeholder="Masukkan UID">

</div>
<div class="col-md-3">

<label>Options</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][options]"
value="{{ $field['options'] ?? '' }}"
placeholder="Asia,America,Europe">

<small class="text-muted">

Pisahkan dengan koma

</small>

</div>
<div class="col-md-2">

    <label>Tipe</label>

    <select
        class="form-select"
        name="player_fields[{{ $i }}][type]"
    >

        <option
            value="text"
            {{ ($field['type'] ?? '') === 'text' ? 'selected' : '' }}
        >
            Text
        </option>

        <option
            value="number"
            {{ ($field['type'] ?? '') === 'number' ? 'selected' : '' }}
        >
            Number
        </option>

        <option
            value="email"
            {{ ($field['type'] ?? '') === 'email' ? 'selected' : '' }}
        >
            Email
        </option>

        <option
            value="select"
            {{ ($field['type'] ?? '') === 'select' ? 'selected' : '' }}
        >
            Select
        </option>

        <option
            value="server"
            {{ ($field['type'] ?? '') === 'server' ? 'selected' : '' }}
        >
            Server
        </option>

    </select>

</div>
<div class="col-md-2">

    <label>Source</label>

    <select
        class="form-select"
        name="player_fields[{{ $i }}][source]"
    >

        <option
            value="manual"
            {{ ($field['source'] ?? 'manual') === 'manual' ? 'selected' : '' }}
        >
            Manual
        </option>

        <option
            value="moogold_server_list"
            {{ ($field['source'] ?? '') === 'moogold_server_list' ? 'selected' : '' }}
        >
            MooGold Server List
        </option>

    </select>

</div>
<div class="col-md-1 d-flex align-items-end">

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
name="player_fields[{{ $i }}][required]"
value="1"
{{ !empty($field['required']) ? 'checked' : '' }}>

<label class="form-check-label">

Wajib Diisi

</label>

<input
    type="hidden"
    name="player_fields[{{ $i }}][moogold_field]"
    value="{{ $field['moogold_field'] ?? '' }}"
>

</div>

</div>

</div>

@endforeach

</div>

<button
type="button"
class="btn btn-outline-primary btn-sm"
onclick="addPlayerField{{ $game->id }}()">

<i class="fa fa-plus"></i>

Tambah Field

</button>


<div class="mb-3">


<label class="form-label">

Logo Game

</label>


@if($game->game_logo)

<div class="mb-2">

<img src="{{asset('storage/'.$game->game_logo)}}"
     width="80"
     class="rounded shadow">

</div>

@endif



<input type="file"
       name="game_logo"
       class="form-control">


<small class="text-muted">

Kosongkan jika tidak ingin mengganti logo

</small>


</div>





<div class="form-check">


<input type="checkbox"
       name="is_active"
       value="1"
       class="form-check-input"

       {{ $game->is_active ? 'checked' : '' }}

>


<label class="form-check-label">

Game Aktif

</label>


</div>



</div>




<div class="modal-footer">


<button type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal">

Batal

</button>



<button type="submit"
        class="btn btn-warning">

<i class="fa-solid fa-save"></i>

Update

</button>


</div>



</form>



</div>

</div>

</div>
<script>

let fieldIndex{{ $game->id }} =
{{ count($fields) }};

function addPlayerField{{ $game->id }}(){

let i = fieldIndex{{ $game->id }}++;

document
.getElementById('playerFields{{ $game->id }}')
.insertAdjacentHTML(
'beforeend',

`
<div class="card mb-3 player-field">

<div class="card-body">

<div class="row">

<div class="col-md-3">

<label>Nama Field</label>

<input
type="text"
class="form-control"
name="player_fields[${i}][name]"
placeholder="uid">

</div>

<div class="col-md-3">

<label>Label</label>

<input
type="text"
class="form-control"
name="player_fields[${i}][label]"
placeholder="UID">

</div>

<div class="col-md-3">

<label>Placeholder</label>

<input
type="text"
class="form-control"
name="player_fields[${i}][placeholder]"
placeholder="Masukkan UID">

</div>

<div class="col-md-2">

<label>Tipe</label>

<select
class="form-select"
name="player_fields[${i}][type]">

<option value="text">Text</option>

<option value="number">Number</option>

<option value="email">Email</option>

<option value="select">Select</option>

<option value="server">
    Server
</option>

</select>

</div>

<div class="col-md-1 d-flex align-items-end">

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
value="1">

<label class="form-check-label">

Wajib Diisi

</label>

</div>

</div>

</div>
`
);

}

document.addEventListener('click',function(e){

if(e.target.closest('.remove-field')){

e.target.closest('.player-field').remove();

}

});
/*
|--------------------------------------------------------------------------
| MOO GOLD SERVER LIST
|--------------------------------------------------------------------------
*/

const loadMooGoldServers{{ $game->id }} =
    document.getElementById(
        'loadMooGoldServers{{ $game->id }}'
    );

const moogoldItem{{ $game->id }} =
    document.getElementById(
        'moogoldItem{{ $game->id }}'
    );

const moogoldServer{{ $game->id }} =
    document.getElementById(
        'moogoldServer{{ $game->id }}'
    );

const moogoldServerName{{ $game->id }} =
    document.getElementById(
        'moogoldServerName{{ $game->id }}'
    );

const loadMooGoldFields{{ $game->id }} =
    document.getElementById(
        'loadMooGoldFields{{ $game->id }}'
    );

const playerFieldsContainer{{ $game->id }} =
    document.getElementById(
        'playerFields{{ $game->id }}'
    );

/*
|--------------------------------------------------------------------------
| MOO GOLD PLAYER FIELDS
|--------------------------------------------------------------------------
*/

function normalizeMooGoldFieldName(field) {

    const normalized =
        String(field || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');

    /*
    |--------------------------------------------------------------------------
    | USER ID ALIASES
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
            'game_id'
        ].includes(normalized)
    ) {
        return 'user_id';
    }


    /*
    |--------------------------------------------------------------------------
    | SERVER ALIASES
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | GENERIC FIELD
    |--------------------------------------------------------------------------
    */

    return normalized || 'player_field';
}

function getMooGoldFieldType(field) {

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

    return 'text';
}


function renderMooGoldFields{{ $game->id }}(fields) {

    playerFieldsContainer{{ $game->id }}.innerHTML = '';

    if (!Array.isArray(fields) || fields.length === 0) {

        playerFieldsContainer{{ $game->id }}.innerHTML = `
            <div class="alert alert-warning">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                MooGold tidak mengembalikan field player untuk product ini.
            </div>
        `;

        return;
    }


    fields.forEach(function (moogoldField, index) {

        const originalField =
            String(moogoldField || '').trim();

        if (!originalField) {
            return;
        }


        const internalName =
            normalizeMooGoldFieldName(originalField);

        const type =
            getMooGoldFieldType(originalField);

        const placeholder =
            `Masukkan ${originalField}`;


        const wrapper =
            document.createElement('div');

        wrapper.className =
            'card mb-3 player-field';


        wrapper.innerHTML = `
            <div class="card-body">

                <div class="row g-3">

                    <div class="col-12 col-md-2">

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


                    <div class="col-12 col-md-2">

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


                    <div class="col-12 col-md-2">

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


                    <div class="col-12 col-md-2">

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


                    <div class="col-12 col-md-2">

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

                            <option
                                value="number"
                                ${type === 'number' ? 'selected' : ''}
                            >
                                Number
                            </option>

                            <option
                                value="email"
                                ${type === 'email' ? 'selected' : ''}
                            >
                                Email
                            </option>

                            <option
                                value="select"
                                ${type === 'select' ? 'selected' : ''}
                            >
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


                    <div class="col-12 col-md-1">

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


                    <div class="col-12 col-md-1 d-flex align-items-end">

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


        playerFieldsContainer{{ $game->id }}
            .appendChild(wrapper);

    });


    fieldIndex{{ $game->id }} =
        fields.length;
}


if (loadMooGoldFields{{ $game->id }}) {

    loadMooGoldFields{{ $game->id }}
        .addEventListener(
            'click',
            async function () {

                const selectedOption =
                    moogoldItem{{ $game->id }}
                        ?.options[
                            moogoldItem{{ $game->id }}
                                .selectedIndex
                        ];

                const productId =
                    selectedOption
                        ?.dataset
                        ?.productId;


                if (!productId) {

                    if (window.Swal) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Item',
                            text:
                                'Pilih item MooGold sumber terlebih dahulu.'
                        });

                    } else {

                        alert(
                            'Pilih item MooGold sumber terlebih dahulu.'
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


                    const data =
                        await response.json();


                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        throw new Error(
                            data.message ||
                            'Gagal mengambil data product MooGold.'
                        );

                    }


                    const fields =
                        data?.data?.fields ?? [];


                    renderMooGoldFields{{ $game->id }}(
                        fields
                    );


                    if (window.Swal) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Field Berhasil Diambil',
                            text:
                                `${fields.length} field ditemukan dari MooGold.`,
                            timer: 1500,
                            showConfirmButton: false
                        });

                    }


                } catch (error) {

                    console.error(
                        '[MooGold Fields] Error:',
                        error
                    );


                    if (window.Swal) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengambil Field',
                            text:
                                error.message ||
                                'Terjadi kesalahan.'
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

}

/*
|--------------------------------------------------------------------------
| ELEMENT CHECK
|--------------------------------------------------------------------------
*/

console.log(
    '[MooGold Server] Init Game ID:',
    {{ $game->id }}
);

console.log(
    '[MooGold Server] Button:',
    loadMooGoldServers{{ $game->id }}
);

console.log(
    '[MooGold Server] Item Select:',
    moogoldItem{{ $game->id }}
);

console.log(
    '[MooGold Server] Server Select:',
    moogoldServer{{ $game->id }}
);


if (
    loadMooGoldServers{{ $game->id }} &&
    moogoldItem{{ $game->id }} &&
    moogoldServer{{ $game->id }}
) {

    loadMooGoldServers{{ $game->id }}
        .addEventListener(
            'click',
            async function () {

                console.log(
                    '[MooGold Server] Tombol diklik.'
                );


                const itemId =
                    moogoldItem{{ $game->id }}.value;


                const selectedOption =
                    moogoldItem{{ $game->id }}
                        .options[
                            moogoldItem{{ $game->id }}
                                .selectedIndex
                        ];


                const productId =
                    selectedOption
                        ? selectedOption.dataset.productId
                        : null;


                console.log(
                    '[MooGold Server] Item ID:',
                    itemId
                );

                console.log(
                    '[MooGold Server] Product ID dari HTML:',
                    productId
                );


                /*
                |--------------------------------------------------------------------------
                | VALIDASI ITEM
                |--------------------------------------------------------------------------
                */

                if (!itemId) {

                    console.warn(
                        '[MooGold Server] Item belum dipilih.'
                    );


                    if (window.Swal) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Item',
                            text:
                                'Pilih item MooGold terlebih dahulu.'
                        });

                    } else {

                        alert(
                            'Pilih item MooGold terlebih dahulu.'
                        );

                    }

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | BUTTON LOADING
                |--------------------------------------------------------------------------
                */

                const originalHtml =
                    this.innerHTML;


                this.disabled = true;


                this.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true">
                    </span>

                    Mengambil Server...
                `;


                /*
                |--------------------------------------------------------------------------
                | URL
                |--------------------------------------------------------------------------
                */

                const url =
                    `{{ route(
                        'admin.game.moogold-servers',
                        [
                            'game' => $game->id,
                            'item' => '__ITEM__'
                        ]
                    ) }}`
                    .replace(
                        '__ITEM__',
                        itemId
                    );


                console.log(
                    '[MooGold Server] Request URL:',
                    url
                );


                try {

                    /*
                    |--------------------------------------------------------------------------
                    | FETCH
                    |--------------------------------------------------------------------------
                    */

                    const response =
                        await fetch(
                            url,
                            {
                                method: 'GET',

                                headers: {
                                    'Accept':
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest'
                                },

                                credentials: 'same-origin'
                            }
                        );


                    console.log(
                        '[MooGold Server] HTTP status:',
                        response.status
                    );

                    console.log(
                        '[MooGold Server] HTTP ok:',
                        response.ok
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | BACA RESPONSE
                    |--------------------------------------------------------------------------
                    */

                    const responseText =
                        await response.text();


                    console.log(
                        '[MooGold Server] Raw response:',
                        responseText
                    );


                    let data = null;


                    try {

                        data =
                            JSON.parse(
                                responseText
                            );

                    } catch (jsonError) {

                        console.error(
                            '[MooGold Server] Response bukan JSON.',
                            jsonError
                        );

                        throw new Error(
                            `Server mengembalikan response tidak valid. HTTP ${response.status}.`
                        );

                    }


                    console.log(
                        '[MooGold Server] Parsed response:',
                        data
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | HTTP ERROR
                    |--------------------------------------------------------------------------
                    */

                    if (!response.ok) {

                        const message =
                            data?.message ||
                            `Request gagal dengan HTTP ${response.status}.`;

                        console.error(
                            '[MooGold Server] HTTP ERROR:',
                            {
                                status: response.status,
                                message: message,
                                data: data
                            }
                        );

                        throw new Error(
                            message
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | APPLICATION ERROR
                    |--------------------------------------------------------------------------
                    */

                    if (!data.success) {

                        console.error(
                            '[MooGold Server] API ERROR:',
                            data
                        );


                        throw new Error(
                            data.message ||
                            'Gagal mengambil server MooGold.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SERVER DATA
                    |--------------------------------------------------------------------------
                    */

                    console.log(
                        '[MooGold Server] Jumlah server:',
                        Array.isArray(data.servers)
                            ? data.servers.length
                            : 'INVALID'
                    );


                    if (
                        !Array.isArray(
                            data.servers
                        )
                    ) {

                        console.error(
                            '[MooGold Server] data.servers bukan array:',
                            data.servers
                        );


                        throw new Error(
                            'Format daftar server dari server tidak valid.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RESET DROPDOWN
                    |--------------------------------------------------------------------------
                    */

                    moogoldServer{{ $game->id }}
                        .innerHTML = '';


                    /*
                    |--------------------------------------------------------------------------
                    | PLACEHOLDER
                    |--------------------------------------------------------------------------
                    */

                    const placeholder =
                        document.createElement(
                            'option'
                        );


                    placeholder.value = '';

                    placeholder.textContent =
                        'Pilih Server...';


                    moogoldServer{{ $game->id }}
                        .appendChild(
                            placeholder
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | EXISTING SERVER
                    |--------------------------------------------------------------------------
                    */

                    const currentServerId =
                        String(
                            '{{ $game->moogold_server_id ?? '' }}'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | POPULATE SERVER
                    |--------------------------------------------------------------------------
                    */

                    data.servers.forEach(
                        function (server) {

                            console.log(
                                '[MooGold Server] Server:',
                                server
                            );


                            const option =
                                document.createElement(
                                    'option'
                                );


                            option.value =
                                server.id;


                            option.textContent =
                                `${server.name} (${server.id})`;


                            option.dataset.serverName =
                                server.name;


                            if (
                                currentServerId !== '' &&
                                String(server.id) ===
                                currentServerId
                            ) {

                                option.selected = true;

                                moogoldServerName{{ $game->id }}
                                    .value =
                                    server.name;

                            }


                            moogoldServer{{ $game->id }}
                                .appendChild(
                                    option
                                );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | EMPTY SERVER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        data.servers.length === 0
                    ) {

                        console.warn(
                            '[MooGold Server] MooGold mengembalikan 0 server.'
                        );


                        moogoldServer{{ $game->id }}
                            .innerHTML = `
                                <option value="">
                                    Tidak ada server dari MooGold
                                </option>
                            `;


                        moogoldServerName{{ $game->id }}
                            .value = '';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    console.log(
                        '[MooGold Server] Server berhasil dimuat.'
                    );


                    if (window.Swal) {

                        Swal.fire({
                            icon: 'success',
                            title:
                                'Server Berhasil Diambil',
                            text:
                                `${data.servers.length} server ditemukan.`,
                            timer: 1500,
                            showConfirmButton: false
                        });

                    }


                } catch (error) {

                    /*
                    |--------------------------------------------------------------------------
                    | ERROR CALLBACK
                    |--------------------------------------------------------------------------
                    */

                    console.error(
                        '[MooGold Server] REQUEST ERROR:',
                        error
                    );


                    console.error(
                        '[MooGold Server] Error message:',
                        error.message
                    );


                    const message =
                        error?.message ||
                        'Terjadi kesalahan saat mengambil server MooGold.';


                    if (window.Swal) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengambil Server',
                            html: `
                                <div class="text-start">

                                    <div class="mb-2">
                                        <strong>Pesan:</strong>
                                        <br>
                                        ${message}
                                    </div>

                                    <div class="mb-2">
                                        <strong>Item ID:</strong>
                                        <br>
                                        ${itemId}
                                    </div>

                                    <div class="mb-0">
                                        <strong>Product ID:</strong>
                                        <br>
                                        ${productId ?? '-'}
                                    </div>

                                </div>
                            `
                        });

                    } else {

                        alert(
                            `Gagal mengambil server MooGold.\n\n` +
                            `Pesan: ${message}\n` +
                            `Item ID: ${itemId}\n` +
                            `Product ID: ${productId ?? '-'}`
                        );

                    }


                } finally {

                    /*
                    |--------------------------------------------------------------------------
                    | RESTORE BUTTON
                    |--------------------------------------------------------------------------
                    */

                    this.disabled = false;

                    this.innerHTML =
                        originalHtml;


                    console.log(
                        '[MooGold Server] Request selesai.'
                    );

                }

            }
        );


    /*
    |--------------------------------------------------------------------------
    | SERVER CHANGE
    |--------------------------------------------------------------------------
    */

    moogoldServer{{ $game->id }}
        .addEventListener(
            'change',
            function () {

                const selectedOption =
                    this.options[
                        this.selectedIndex
                    ];


                if (
                    !selectedOption ||
                    !selectedOption.value
                ) {

                    moogoldServerName{{ $game->id }}
                        .value = '';

                    return;

                }


                const serverName =
                    selectedOption
                        .dataset
                        .serverName || '';


                moogoldServerName{{ $game->id }}
                    .value =
                    serverName;


                console.log(
                    '[MooGold Server] Server dipilih:',
                    {
                        id:
                            selectedOption.value,

                        name:
                            serverName
                    }
                );

            }
        );


} else {

    console.error(
        '[MooGold Server] Element HTML tidak ditemukan.',
        {
            button:
                loadMooGoldServers{{ $game->id }},

            item:
                moogoldItem{{ $game->id }},

            server:
                moogoldServer{{ $game->id }}
        }
    );

}
</script>