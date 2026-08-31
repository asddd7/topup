@extends('admin.layouts.app')

@section('title', 'MooGold Product Mapping')

@section('content')

<div class="container-fluid py-4">

{{-- =========================================================
     PAGE HEADER
========================================================== --}}

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="fas fa-project-diagram text-primary me-2"></i>
            MooGold Product Mapping
        </h3>

        <p class="text-muted mb-0">
            Hubungkan product MooGold dengan Game dan Category lokal.
        </p>
    </div>

</div>


{{-- =========================================================
     FILTER / CONTROL
========================================================== --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <div class="row g-3 align-items-end">

            {{-- MooGold Category --}}

            <div class="col-lg-2 col-md-4">

                <label
                    for="moogoldCategoryId"
                    class="form-label fw-semibold"
                >
                    MooGold Category
                </label>

                <input
                    type="text"
                    id="moogoldCategoryId"
                    class="form-control"
                    value="50"
                    placeholder="Contoh: 50"
                >

            </div>


            {{-- Search --}}

            <div class="col-lg-3 col-md-8">

                <label
                    for="searchProduct"
                    class="form-label fw-semibold"
                >
                    Cari Product
                </label>

                <div class="input-group">

                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>

                    <input
                        type="text"
                        id="searchProduct"
                        class="form-control"
                        placeholder="Nama product / MooGold ID..."
                    >

                </div>

            </div>


            {{-- Mapping Filter --}}

            <div class="col-lg-2 col-md-4">

                <label
                    for="mappingFilter"
                    class="form-label fw-semibold"
                >
                    Status Mapping
                </label>

                <select
                    id="mappingFilter"
                    class="form-select"
                >

                    <option value="">
                        Semua Product
                    </option>

                    <option value="1">
                        Sudah Mapping
                    </option>

                    <option value="0">
                        Belum Mapping
                    </option>

                </select>

            </div>


            {{-- Refresh --}}

            <div class="col-lg-2 col-md-4">

                <button
                    type="button"
                    id="btnRefresh"
                    class="btn btn-outline-primary w-100"
                >

                    <i class="fas fa-search me-1"></i>

                    Tampilkan

                </button>

            </div>


            {{-- Sync --}}

            <div class="col-lg-3 col-md-4">

                <button
                    type="button"
                    id="btnSync"
                    class="btn btn-primary w-100"
                >

                    <i class="fas fa-sync-alt me-1"></i>

                    Sync Category MooGold

                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     STATISTICS
========================================================== --}}

<div class="row g-3 mb-4">

    {{-- Total --}}

    <div class="col-xl-4 col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body d-flex align-items-center">

                <div
                    class="rounded-circle bg-primary bg-opacity-10
                           text-primary d-flex align-items-center
                           justify-content-center me-3"
                    style="width:50px;height:50px;"
                >

                    <i class="fas fa-boxes fa-lg"></i>

                </div>

                <div>

                    <div class="text-muted small">
                        Total Product
                    </div>

                    <div
                        id="totalProducts"
                        class="fs-3 fw-bold"
                    >
                        0
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Mapped --}}

    <div class="col-xl-4 col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body d-flex align-items-center">

                <div
                    class="rounded-circle bg-success bg-opacity-10
                           text-success d-flex align-items-center
                           justify-content-center me-3"
                    style="width:50px;height:50px;"
                >

                    <i class="fas fa-check-circle fa-lg"></i>

                </div>

                <div>

                    <div class="text-muted small">
                        Sudah Mapping
                    </div>

                    <div
                        id="mappedProducts"
                        class="fs-3 fw-bold"
                    >
                        0
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Unmapped --}}

    <div class="col-xl-4 col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body d-flex align-items-center">

                <div
                    class="rounded-circle bg-warning bg-opacity-10
                           text-warning d-flex align-items-center
                           justify-content-center me-3"
                    style="width:50px;height:50px;"
                >

                    <i class="fas fa-exclamation-circle fa-lg"></i>

                </div>

                <div>

                    <div class="text-muted small">
                        Belum Mapping
                    </div>

                    <div
                        id="unmappedProducts"
                        class="fs-3 fw-bold"
                    >
                        0
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     TABLE
========================================================== --}}

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0 py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">
                    Product MooGold
                </h5>

                <small class="text-muted">
                    Tentukan Game dan Category lokal untuk setiap product.
                </small>

            </div>

            <div id="pageInfo" class="text-muted small"></div>

        </div>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th
                            class="ps-3"
                            style="width:60px;"
                        >
                            #
                        </th>

                        <th>
                            Product MooGold
                        </th>

                        <th style="width:130px;">
                            Product ID
                        </th>

                        <th style="width:260px;">
                            Game Lokal
                        </th>

                        <th style="width:260px;">
                            Category Lokal
                        </th>

                        <th style="width:120px;">
                            Status
                        </th>

                        <th
                            class="text-center"
                            style="width:90px;"
                        >
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody id="mappingTable">

                    <tr>

                        <td
                            colspan="7"
                            class="text-center py-5"
                        >

                            <div class="spinner-border text-primary"></div>

                            <div class="mt-2 text-muted">
                                Memuat product...
                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>


    {{-- =====================================================
         FOOTER / PAGINATION
    ====================================================== --}}

    <div class="card-footer bg-white">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div
                id="paginationInfo"
                class="text-muted small"
            >
            </div>

            <div id="pagination"></div>

        </div>

    </div>

</div>

</div>

{{-- =========================================================
SWEETALERT
========================================================== --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- =========================================================
JAVASCRIPT
========================================================== --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */

    const URLs = {
        data: @json(route('admin.moogold.product-mapping.data', [], false)),
        games: @json(route('admin.moogold.product-mapping.games', [], false)),
        categories: @json(route('admin.moogold.product-mapping.categories', [], false)),
        sync: @json(route('admin.moogold.product-mapping.sync', [], false)),
        update: @json(url('/admin/moogold/product-mapping', [], false)),
    };

    console.log('URLs:', URLs);
    console.log('Games URL:', URLs.games);
    console.log('Data URL:', URLs.data);
    console.log('Categories URL:', URLs.categories);


    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    let games = [];

    /*
     * Cache category berdasarkan game_id.
     *
     * Contoh:
     *
     * categoryCache = {
     *     1: [...],
     *     2: [...]
     * }
     */
    const categoryCache = {};

    let currentPage = 1;

    let currentMappings = [];


    /*
    |--------------------------------------------------------------------------
    | DOM
    |--------------------------------------------------------------------------
    */

    const categoryInput =
        document.getElementById('moogoldCategoryId');

    const searchInput =
        document.getElementById('searchProduct');

    const mappingFilter =
        document.getElementById('mappingFilter');

    const mappingTable =
        document.getElementById('mappingTable');

    const totalProducts =
        document.getElementById('totalProducts');

    const mappedProducts =
        document.getElementById('mappedProducts');

    const unmappedProducts =
        document.getElementById('unmappedProducts');

    const pagination =
        document.getElementById('pagination');

    const paginationInfo =
        document.getElementById('paginationInfo');

    const pageInfo =
        document.getElementById('pageInfo');

    const btnRefresh =
        document.getElementById('btnRefresh');

    const btnSync =
        document.getElementById('btnSync');


    /*
    |--------------------------------------------------------------------------
    | SweetAlert Helper
    |--------------------------------------------------------------------------
    */

    function showError(message)
    {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: message,
            confirmButtonText: 'OK'
        });
    }


    function showSuccess(message)
    {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: message,
            timer: 1800,
            showConfirmButton: false
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value)
    {
        if (
            value === null ||
            value === undefined
        ) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    /*
    |--------------------------------------------------------------------------
    | Load Games
    |--------------------------------------------------------------------------
    */

async function loadGames()
{
    try {

        console.log('Request Games:', URLs.games);

        const response = await fetch(
            URLs.games,
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        const result = await response.json();

        console.log('Games Response:', result);

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Gagal mengambil Game lokal.'
            );
        }

        games = result.data || [];

        console.log('Games loaded:', games);

    } catch (error) {

        console.error('loadGames:', error);

        showError(error.message);

        throw error;
    }
}

    /*
    |--------------------------------------------------------------------------
    | Game Options
    |--------------------------------------------------------------------------
    */

    function buildGameOptions(
        selectedGameId = null
    )
    {
        let html = `
            <option value="">
                -- Pilih Game --
            </option>
        `;


        games.forEach(game => {

            const selected =
                String(game.id) ===
                String(selectedGameId)
                    ? 'selected'
                    : '';


            html += `
                <option
                    value="${escapeHtml(game.id)}"
                    ${selected}
                >
                    ${escapeHtml(game.game_name)}
                </option>
            `;

        });


        return html;
    }


    /*
    |--------------------------------------------------------------------------
    | Load Categories
    |--------------------------------------------------------------------------
    */

async function loadCategories(gameId)
{
    if (!gameId) {
        return [];
    }

    if (
        Object.prototype.hasOwnProperty.call(
            categoryCache,
            gameId
        )
    ) {
        return categoryCache[gameId];
    }

    try {

        const url =
            `${URLs.categories}?game_id=${encodeURIComponent(gameId)}`;

        const response = await fetch(
            url,
            {
                method: 'GET',

                credentials: 'same-origin',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {

            throw new Error(
                result.message ||
                'Gagal mengambil Category.'
            );
        }

        const categories =
            result.data || [];

        categoryCache[gameId] =
            categories;

        return categories;

    } catch (error) {

        console.error(
            'loadCategories:',
            error
        );

        throw error;
    }
}


    /*
    |--------------------------------------------------------------------------
    | Category Options
    |--------------------------------------------------------------------------
    */

    function buildCategoryOptions(
        categories,
        selectedCategoryId = null
    )
    {
        let html = `
            <option value="">
                -- Pilih Category --
            </option>
        `;


        categories.forEach(category => {

            const selected =
                String(category.id) ===
                String(selectedCategoryId)
                    ? 'selected'
                    : '';


            html += `
                <option
                    value="${escapeHtml(category.id)}"
                    ${selected}
                >
                    ${escapeHtml(
                        category.category_name
                    )}
                </option>
            `;

        });


        return html;
    }


    /*
    |--------------------------------------------------------------------------
    | Load Mappings
    |--------------------------------------------------------------------------
    */

    async function loadMappings(
        page = 1
    )
    {
        currentPage = page;


        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        mappingTable.innerHTML = `
            <tr>

                <td
                    colspan="7"
                    class="text-center py-5"
                >

                    <div
                        class="spinner-border text-primary"
                    ></div>

                    <div class="mt-2 text-muted">
                        Memuat product...
                    </div>

                </td>

            </tr>
        `;


        const moogoldCategoryId =
            categoryInput.value.trim();


        if (!moogoldCategoryId) {

            mappingTable.innerHTML = `
                <tr>

                    <td
                        colspan="7"
                        class="text-center py-5 text-muted"
                    >

                        Masukkan MooGold Category ID.

                    </td>

                </tr>
            `;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Query Params
        |--------------------------------------------------------------------------
        */

        const params =
            new URLSearchParams();


        params.set(
            'category_id',
            moogoldCategoryId
        );


        params.set(
            'page',
            page
        );


        params.set(
            'per_page',
            50
        );


        if (
            searchInput.value.trim()
        ) {

            params.set(
                'search',
                searchInput.value.trim()
            );
        }


        if (
            mappingFilter.value !== ''
        ) {

            params.set(
                'mapped',
                mappingFilter.value
            );
        }


        try {

const response =
    await fetch(
        `${URLs.data}?${params.toString()}`,
        {
            method: 'GET',

            credentials: 'same-origin',

            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    );


            const result =
                await response.json();


            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil product mapping.'
                );
            }


            const data =
                result.data;


            currentMappings =
                data.data || [];


            renderStatistics(
                data
            );


            renderTable(
                currentMappings,
                data
            );


            renderPagination(
                data
            );


        } catch (error) {

            console.error(
                'loadMappings:',
                error
            );


            mappingTable.innerHTML = `
                <tr>

                    <td
                        colspan="7"
                        class="text-center py-5 text-danger"
                    >

                        <i
                            class="fas fa-exclamation-triangle fa-2x mb-2"
                        ></i>

                        <div>
                            ${escapeHtml(error.message)}
                        </div>

                    </td>

                </tr>
            `;

            pagination.innerHTML = '';

            paginationInfo.innerText = '';

            pageInfo.innerText = '';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    function renderStatistics(data)
    {
        const total =
            Number(data.total || 0);


        /*
        |--------------------------------------------------------------------------
        | Saat filter mapped/unmapped aktif,
        | data.total adalah total hasil filter.
        |--------------------------------------------------------------------------
        */

        if (
            mappingFilter.value === ''
        ) {

            totalProducts.innerText =
                total;

        } else {

            totalProducts.innerText =
                total;
        }


        /*
        |--------------------------------------------------------------------------
        | Untuk sementara mapped/unmapped
        | dihitung dari seluruh data yang sudah
        | dikirim API.
        |
        | Nanti endpoint bisa kita tingkatkan
        | agar mengirim statistik global.
        |--------------------------------------------------------------------------
        */

        const mapped =
            currentMappings.filter(
                mapping =>
                    mapping.game_id !== null
            ).length;


        const unmapped =
            currentMappings.length -
            mapped;


        mappedProducts.innerText =
            mapped;


        unmappedProducts.innerText =
            unmapped;
    }


    /*
    |--------------------------------------------------------------------------
    | Render Table
    |--------------------------------------------------------------------------
    */

    function renderTable(
        rows,
        paginationData
    )
    {
        if (!rows.length) {

            mappingTable.innerHTML = `
                <tr>

                    <td
                        colspan="7"
                        class="text-center py-5"
                    >

                        <div class="text-muted">

                            <i
                                class="fas fa-box-open fa-2x mb-3"
                            ></i>

                            <div>
                                Tidak ada product ditemukan.
                            </div>

                        </div>

                    </td>

                </tr>
            `;

            return;
        }


        mappingTable.innerHTML =
            rows.map(
                (mapping, index) => {

                    return renderRow(
                        mapping,
                        index,
                        paginationData
                    );

                }
            ).join('');


        attachRowEvents(
            rows
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render Row
    |--------------------------------------------------------------------------
    */

    function renderRow(
        mapping,
        index,
        paginationData
    )
    {
        const perPage =
            Number(
                paginationData.per_page || 50
            );


        const number =
            (
                (
                    Number(
                        paginationData.current_page || 1
                    ) - 1
                ) * perPage
            ) +
            index +
            1;


        const isMapped =
            mapping.game_id !== null;


        const status =
            isMapped

                ? `
                    <span
                        class="badge bg-success"
                    >
                        <i
                            class="fas fa-check me-1"
                        ></i>
                        Mapped
                    </span>
                `

                : `
                    <span
                        class="badge bg-warning text-dark"
                    >
                        <i
                            class="fas fa-clock me-1"
                        ></i>
                        Belum
                    </span>
                `;


        return `
            <tr
                data-mapping-id="${escapeHtml(mapping.id)}"
            >

                {{-- Number --}}

                <td class="ps-3">
                    ${number}
                </td>


                {{-- Product --}}

                <td>

                    <div
                        class="fw-semibold text-dark"
                        style="max-width:420px;"
                    >
                        ${escapeHtml(
                            mapping.product_name
                        )}
                    </div>

                    ${
                        mapping.product_data
                            ? `
                                <small class="text-muted">
                                    MooGold Product
                                </small>
                              `
                            : ''
                    }

                </td>


                {{-- Product ID --}}

                <td>

                    <code>
                        ${escapeHtml(
                            mapping.moogold_product_id
                        )}
                    </code>

                </td>


                {{-- Game --}}

                <td>

                    <select
                        id="game-${mapping.id}"
                        class="form-select form-select-sm mapping-game"
                        data-id="${mapping.id}"
                    >

                        ${buildGameOptions(
                            mapping.game_id
                        )}

                    </select>

                </td>


                {{-- Category --}}

                <td>

                    <select
                        id="category-${mapping.id}"
                        class="form-select form-select-sm mapping-category"
                        data-id="${mapping.id}"
                        ${mapping.game_id ? '' : 'disabled'}
                    >

                        ${
                            mapping.game_id
                                ? `
                                    <option value="">
                                        Memuat Category...
                                    </option>
                                  `
                                : `
                                    <option value="">
                                        -- Pilih Game dahulu --
                                    </option>
                                  `
                        }

                    </select>

                </td>


                {{-- Status --}}

                <td>

                    <span
                        id="status-${mapping.id}"
                    >
                        ${status}
                    </span>

                </td>


                {{-- Action --}}

                <td class="text-center">

                    <button
                        type="button"
                        id="save-${mapping.id}"
                        class="btn btn-sm btn-primary"
                        title="Simpan Mapping"
                    >

                        <i class="fas fa-save"></i>

                    </button>

                </td>

            </tr>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | Attach Row Events
    |--------------------------------------------------------------------------
    */

    function attachRowEvents(
        rows
    )
    {
        rows.forEach(mapping => {

            const gameSelect =
                document.getElementById(
                    `game-${mapping.id}`
                );


            const categorySelect =
                document.getElementById(
                    `category-${mapping.id}`
                );


            const saveButton =
                document.getElementById(
                    `save-${mapping.id}`
                );


            /*
            |--------------------------------------------------------------------------
            | Jika sudah ada Game,
            | load category otomatis.
            |--------------------------------------------------------------------------
            */

            if (mapping.game_id) {

                loadCategories(
                    mapping.game_id
                )
                .then(categories => {

                    categorySelect.innerHTML =
                        buildCategoryOptions(
                            categories,
                            mapping.category_id
                        );

                    categorySelect.disabled =
                        false;

                })
                .catch(error => {

                    categorySelect.innerHTML = `
                        <option value="">
                            Gagal mengambil Category
                        </option>
                    `;

                    console.error(error);
                });
            }


            /*
            |--------------------------------------------------------------------------
            | Game berubah
            |--------------------------------------------------------------------------
            */

            gameSelect.addEventListener(
                'change',
                async function () {

                    const gameId =
                        this.value;


                    categorySelect.innerHTML = `
                        <option value="">
                            Memuat Category...
                        </option>
                    `;


                    categorySelect.disabled =
                        true;


                    if (!gameId) {

                        categorySelect.innerHTML = `
                            <option value="">
                                -- Pilih Game dahulu --
                            </option>
                        `;

                        return;
                    }


                    try {

                        const categories =
                            await loadCategories(
                                gameId
                            );


                        categorySelect.innerHTML =
                            buildCategoryOptions(
                                categories
                            );


                        categorySelect.disabled =
                            false;


                    } catch (error) {

                        categorySelect.innerHTML = `
                            <option value="">
                                Gagal mengambil Category
                            </option>
                        `;

                        showError(
                            error.message
                        );
                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Save
            |--------------------------------------------------------------------------
            */

            saveButton.addEventListener(
                'click',
                function () {

                    saveMapping(
                        mapping.id
                    );

                }
            );

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Save Mapping
    |--------------------------------------------------------------------------
    */

    async function saveMapping(
        mappingId
    )
    {
        const gameSelect =
            document.getElementById(
                `game-${mappingId}`
            );


        const categorySelect =
            document.getElementById(
                `category-${mappingId}`
            );


        const saveButton =
            document.getElementById(
                `save-${mappingId}`
            );


        const gameId =
            gameSelect.value;


        const categoryId =
            categorySelect.value;


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        if (!gameId) {

            showError(
                'Silakan pilih Game lokal terlebih dahulu.'
            );

            gameSelect.focus();

            return;
        }


        if (!categoryId) {

            showError(
                'Silakan pilih Category lokal terlebih dahulu.'
            );

            categorySelect.focus();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Loading button
        |--------------------------------------------------------------------------
        */

        const originalHtml =
            saveButton.innerHTML;


        saveButton.disabled =
            true;


        saveButton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm"
            ></span>
        `;


        try {

const response =
    await fetch(
        `${URLs.update}/${mappingId}`,
        {
            method: 'PUT',

            credentials: 'same-origin',

            headers: {

                'Content-Type':
                    'application/json',

                'Accept':
                    'application/json',

                'X-Requested-With':
                    'XMLHttpRequest',

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'
            },

            body: JSON.stringify({

                game_id:
                    Number(gameId),

                category_id:
                    Number(categoryId),

                is_active:
                    true

            })
        }
    );


            const result =
                await response.json();


            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal menyimpan mapping.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            showSuccess(
                'Mapping berhasil disimpan.'
            );


            /*
            |--------------------------------------------------------------------------
            | Update status tanpa reload
            |--------------------------------------------------------------------------
            */

            const statusElement =
                document.getElementById(
                    `status-${mappingId}`
                );


            if (statusElement) {

                statusElement.innerHTML = `
                    <span
                        class="badge bg-success"
                    >
                        <i
                            class="fas fa-check me-1"
                        ></i>
                        Mapped
                    </span>
                `;
            }


        } catch (error) {

            console.error(
                'saveMapping:',
                error
            );


            showError(
                error.message
            );


        } finally {

            saveButton.disabled =
                false;

            saveButton.innerHTML =
                originalHtml;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    function renderPagination(
        data
    )
    {
        const current =
            Number(
                data.current_page || 1
            );


        const last =
            Number(
                data.last_page || 1
            );


        const from =
            Number(
                data.from || 0
            );


        const to =
            Number(
                data.to || 0
            );


        const total =
            Number(
                data.total || 0
            );


        pageInfo.innerText =
            `Halaman ${current} dari ${last}`;


        if (total > 0) {

            paginationInfo.innerText =
                `Menampilkan ${from}–${to} dari ${total} product`;

        } else {

            paginationInfo.innerText =
                'Tidak ada product';
        }


        if (last <= 1) {

            pagination.innerHTML =
                '';

            return;
        }


        let html = `
            <nav>
                <ul class="pagination pagination-sm mb-0">
        `;


        /*
        |--------------------------------------------------------------------------
        | Previous
        |--------------------------------------------------------------------------
        */

        html += `
            <li
                class="page-item ${
                    current <= 1
                        ? 'disabled'
                        : ''
                }"
            >

                <button
                    class="page-link"
                    ${
                        current <= 1
                            ? 'disabled'
                            : ''
                    }
                    onclick="loadMappingPage(${current - 1})"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>

            </li>
        `;


        /*
        |--------------------------------------------------------------------------
        | Page Numbers
        |--------------------------------------------------------------------------
        */

        const pages =
            getPaginationPages(
                current,
                last
            );


        pages.forEach(page => {

            if (page === '...') {

                html += `
                    <li
                        class="page-item disabled"
                    >
                        <span class="page-link">
                            ...
                        </span>
                    </li>
                `;

                return;
            }


            html += `
                <li
                    class="page-item ${
                        page === current
                            ? 'active'
                            : ''
                    }"
                >

                    <button
                        class="page-link"
                        onclick="loadMappingPage(${page})"
                    >
                        ${page}
                    </button>

                </li>
            `;

        });


        /*
        |--------------------------------------------------------------------------
        | Next
        |--------------------------------------------------------------------------
        */

        html += `
            <li
                class="page-item ${
                    current >= last
                        ? 'disabled'
                        : ''
                }"
            >

                <button
                    class="page-link"
                    ${
                        current >= last
                            ? 'disabled'
                            : ''
                    }
                    onclick="loadMappingPage(${current + 1})"
                >
                    <i class="fas fa-chevron-right"></i>
                </button>

            </li>
        `;


        html += `
                </ul>
            </nav>
        `;


        pagination.innerHTML =
            html;
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination Pages
    |--------------------------------------------------------------------------
    */

    function getPaginationPages(
        current,
        last
    )
    {
        if (last <= 7) {

            return Array.from(
                {
                    length: last
                },
                (_, index) => index + 1
            );
        }


        const pages = [];


        pages.push(1);


        if (current > 4) {

            pages.push('...');
        }


        const start =
            Math.max(
                2,
                current - 1
            );


        const end =
            Math.min(
                last - 1,
                current + 1
            );


        for (
            let i = start;
            i <= end;
            i++
        ) {

            pages.push(i);
        }


        if (current < last - 3) {

            pages.push('...');
        }


        pages.push(last);


        return pages;
    }


    /*
    |--------------------------------------------------------------------------
    | Global Pagination Function
    |--------------------------------------------------------------------------
    */

    window.loadMappingPage =
        function(page)
        {
            loadMappings(page);
        };


    /*
    |--------------------------------------------------------------------------
    | Sync Category
    |--------------------------------------------------------------------------
    */

    btnSync.addEventListener(
        'click',
        async function ()
        {
            const categoryId =
                categoryInput.value.trim();


            if (!categoryId) {

                showError(
                    'Masukkan MooGold Category ID terlebih dahulu.'
                );

                categoryInput.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Confirmation
            |--------------------------------------------------------------------------
            */

            const confirmation =
                await Swal.fire({

                    icon: 'question',

                    title:
                        'Sync MooGold Category?',

                    html:
                        `
                        Semua product dari
                        <strong>
                            MooGold Category ${escapeHtml(categoryId)}
                        </strong>
                        akan diambil dan disimpan.
                        <br><br>
                        Mapping Game/Category lokal yang sudah ada
                        <strong>tidak akan dihapus</strong>.
                        `,

                    showCancelButton: true,

                    confirmButtonText:
                        'Ya, Sync',

                    cancelButtonText:
                        'Batal'
                });


            if (!confirmation.isConfirmed) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Loading
            |--------------------------------------------------------------------------
            */

            const originalHtml =
                btnSync.innerHTML;


            btnSync.disabled =
                true;


            btnSync.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                ></span>

                Syncing...
            `;


            try {

console.log('SYNC URL:', URLs.sync);
console.log('CURRENT URL:', window.location.href);
console.log('CSRF:', '{{ csrf_token() }}');

const response =
    await fetch(
        URLs.sync,
        {
            method: 'POST',

            credentials: 'same-origin',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            body: JSON.stringify({
                category_id: categoryId
            })
        }
    );


                const result =
                    await response.json();


                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message ||
                        'Sync category gagal.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Clear category cache
                |--------------------------------------------------------------------------
                */

                Object.keys(
                    categoryCache
                ).forEach(key => {

                    delete categoryCache[key];

                });


                /*
                |--------------------------------------------------------------------------
                | Show Result
                |--------------------------------------------------------------------------
                */

                const syncData =
                    result.data || {};


                await Swal.fire({

                    icon: 'success',

                    title:
                        'Sync Berhasil',

                    html:
                        `
                        <div class="text-start">

                            <div class="mb-2">
                                <strong>Category:</strong>
                                ${escapeHtml(
                                    syncData.category_id || categoryId
                                )}
                            </div>

                            <div class="mb-2">
                                <strong>Total Product:</strong>
                                ${Number(
                                    syncData.total_products || 0
                                )}
                            </div>

                            <div class="mb-2">
                                <strong>Created:</strong>
                                ${Number(
                                    syncData.created || 0
                                )}
                            </div>

                            <div>
                                <strong>Updated:</strong>
                                ${Number(
                                    syncData.updated || 0
                                )}
                            </div>

                        </div>
                        `,

                    confirmButtonText:
                        'OK'
                });


                /*
                |--------------------------------------------------------------------------
                | Reload
                |--------------------------------------------------------------------------
                */

                await loadMappings(
                    1
                );


            } catch (error) {

                console.error(
                    'syncCategory:',
                    error
                );


                showError(
                    error.message
                );


            } finally {

                btnSync.disabled =
                    false;

                btnSync.innerHTML =
                    originalHtml;
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    */

    btnRefresh.addEventListener(
        'click',
        function ()
        {
            loadMappings(1);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Filter Change
    |--------------------------------------------------------------------------
    */

    mappingFilter.addEventListener(
        'change',
        function ()
        {
            loadMappings(1);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    let searchTimer = null;


    searchInput.addEventListener(
        'input',
        function ()
        {
            clearTimeout(
                searchTimer
            );


            searchTimer =
                setTimeout(
                    function () {

                        loadMappings(1);

                    },
                    400
                );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Enter Search
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener(
        'keydown',
        function(event)
        {

            if (
                event.key ===
                'Enter'
            ) {

                event.preventDefault();

                clearTimeout(
                    searchTimer
                );

                loadMappings(1);
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Category ID Enter
    |--------------------------------------------------------------------------
    */

    categoryInput.addEventListener(
        'keydown',
        function(event)
        {

            if (
                event.key ===
                'Enter'
            ) {

                event.preventDefault();

                loadMappings(1);
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    async function init()
    {
        try {

            await loadGames();

            await loadMappings(
                1
            );

        } catch (error) {

            console.error(
                'init:',
                error
            );
        }
    }


    init();

});

</script>

@endsection
