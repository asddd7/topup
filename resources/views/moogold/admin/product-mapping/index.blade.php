@extends('admin.layouts.app')

@section('title', 'MooGold Product Mapping')

@section('content')

<div class="container-fluid py-4">


{{-- =========================================================
     HEADER
========================================================== --}}

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="fas fa-project-diagram text-primary me-2"></i>
            MooGold Product Mapping
        </h3>

        <p class="text-muted mb-0">
            Hubungkan product MooGold dengan Game lokal,
            lalu tentukan Category untuk setiap variation.
        </p>
    </div>

</div>


{{-- =========================================================
     FILTER
========================================================== --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <div class="row g-3 align-items-end">

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


            <div class="col-lg-3 col-md-4">

                <button
                    type="button"
                    id="btnSync"
                    class="btn btn-primary w-100"
                >
                    <i class="fas fa-cloud-download-alt me-1"></i>
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
     PRODUCT TABLE
========================================================== --}}

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0 py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">
                    Product MooGold
                </h5>

                <small class="text-muted">
                    Pilih Game lokal, sync variation,
                    lalu mapping Category per variation.
                </small>

            </div>

            <div
                id="pageInfo"
                class="text-muted small"
            ></div>

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

                        <th style="width:280px;">
                            Game Lokal
                        </th>

                        <th style="width:150px;">
                            Status
                        </th>

                        <th
                            class="text-center"
                            style="width:230px;"
                        >
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody id="mappingTable">

                    <tr>

                        <td
                            colspan="6"
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


    <div class="card-footer bg-white">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div
                id="paginationInfo"
                class="text-muted small"
            ></div>

            <div id="pagination"></div>

        </div>

    </div>

</div>


</div>

{{-- =========================================================
SWEETALERT
========================================================== --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

    const URLs = {

        data: @json(
            route('admin.moogold.product-mapping.data')
        ),

        games: @json(
            route('admin.moogold.product-mapping.games')
        ),

        categories: @json(
            route('admin.moogold.product-mapping.categories')
        ),

        update: @json(
            url('/admin/moogold/product-mapping')
        ),

        sync: @json(
            route('admin.moogold.product-mapping.sync-category')
        ),

        variations: @json(
            url('/admin/moogold/product-mapping')
        ),

        syncVariations: @json(
            url('/admin/moogold/product-mapping')
        ),

        variationUpdate: @json(
            url('/admin/moogold/product-mapping')
        ),

        syncItems: @json(
            url('/admin/moogold/product-mapping')
        ),

    };


    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    let games = [];

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
    | ALERT
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
    | ESCAPE
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
    | LOAD GAMES
    |--------------------------------------------------------------------------
    */

    async function loadGames()
    {
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

        if (!response.ok || !result.success) {

            throw new Error(
                result.message ||
                'Gagal mengambil Game lokal.'
            );
        }

        games = result.data || [];
    }


    /*
    |--------------------------------------------------------------------------
    | GAME OPTIONS
    |--------------------------------------------------------------------------
    */

    function buildGameOptions(selectedGameId = null)
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
    | LOAD CATEGORIES
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

        categoryCache[gameId] =
            result.data || [];

        return categoryCache[gameId];
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORY OPTIONS
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
                    ${escapeHtml(category.category_name)}
                </option>
            `;

        });

        return html;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD MAPPINGS
    |--------------------------------------------------------------------------
    */

    async function loadMappings(page = 1)
    {
        currentPage = page;

        mappingTable.innerHTML = `
            <tr>
                <td
                    colspan="6"
                    class="text-center py-5"
                >
                    <div class="spinner-border text-primary"></div>

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
                        colspan="6"
                        class="text-center py-5 text-muted"
                    >
                        Masukkan MooGold Category ID.
                    </td>
                </tr>
            `;

            return;
        }

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

        if (searchInput.value.trim()) {

            params.set(
                'search',
                searchInput.value.trim()
            );
        }

        if (mappingFilter.value !== '') {

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

            renderStatistics(data);

            renderTable(
                currentMappings,
                data
            );

            renderPagination(data);

        } catch (error) {

            console.error(
                'loadMappings:',
                error
            );

            mappingTable.innerHTML = `
                <tr>
                    <td
                        colspan="6"
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
    | STATISTICS
    |--------------------------------------------------------------------------
    */

    function renderStatistics(data)
    {
        const total =
            Number(data.total || 0);

        totalProducts.innerText =
            total;

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
    | RENDER TABLE
    |--------------------------------------------------------------------------
    */

    function renderTable(rows, paginationData)
    {
        if (!rows.length) {

            mappingTable.innerHTML = `
                <tr>
                    <td
                        colspan="6"
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
                (mapping, index) =>
                    renderRow(
                        mapping,
                        index,
                        paginationData
                    )
            ).join('');

        attachRowEvents(rows);
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER PRODUCT ROW
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
                    <span class="badge bg-success">
                        <i class="fas fa-check me-1"></i>
                        Game Mapped
                    </span>
                  `

                : `
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-clock me-1"></i>
                        Belum Mapping
                    </span>
                  `;

        return `
            <tr
                data-mapping-id="${escapeHtml(mapping.id)}"
            >

                <td class="ps-3">
                    ${number}
                </td>


                <td>

                    <div
                        class="fw-semibold text-dark"
                        style="max-width:420px;"
                    >
                        ${escapeHtml(mapping.product_name)}
                    </div>

                    <small class="text-muted">
                        MooGold Product
                    </small>

                </td>


                <td>

                    <code>
                        ${escapeHtml(mapping.moogold_product_id)}
                    </code>

                </td>


                <td>

                    <select
                        id="game-${mapping.id}"
                        class="form-select form-select-sm mapping-game"
                    >
                        ${buildGameOptions(mapping.game_id)}
                    </select>

                </td>


                <td>

                    <span id="status-${mapping.id}">
                        ${status}
                    </span>

                </td>


                <td class="text-center">

                    <div
                        class="btn-group btn-group-sm"
                        role="group"
                    >

                        <button
                            type="button"
                            id="save-${mapping.id}"
                            class="btn btn-primary"
                            title="Simpan Game"
                        >
                            <i class="fas fa-save"></i>
                        </button>


                        <button
                            type="button"
                            id="variation-${mapping.id}"
                            class="btn btn-info text-white"
                            title="Lihat Variation"
                            ${!mapping.game_id ? 'disabled' : ''}
                        >
                            <i class="fas fa-list-ul"></i>
                            <span class="d-none d-xl-inline ms-1">
                                Variations
                            </span>
                        </button>


                        <button
                            type="button"
                            id="sync-variation-${mapping.id}"
                            class="btn btn-warning"
                            title="Sync Variation dari MooGold"
                            ${!mapping.game_id ? 'disabled' : ''}
                        >
                            <i class="fas fa-sync-alt"></i>
                        </button>


                        <button
                            type="button"
                            id="sync-${mapping.id}"
                            class="btn btn-success"
                            title="Sync Variation ke Item"
                            ${!mapping.game_id ? 'disabled' : ''}
                        >
                            <i class="fas fa-box"></i>
                        </button>

                    </div>

                </td>

            </tr>

            <tr
                id="variation-row-${mapping.id}"
                style="display:none;"
            >

                <td colspan="6" class="p-0 bg-light">

                    <div
                        id="variation-container-${mapping.id}"
                        class="p-3"
                    ></div>

                </td>

            </tr>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | ATTACH PRODUCT EVENTS
    |--------------------------------------------------------------------------
    */

    function attachRowEvents(rows)
    {
        rows.forEach(mapping => {

            const gameSelect =
                document.getElementById(
                    `game-${mapping.id}`
                );

            const saveButton =
                document.getElementById(
                    `save-${mapping.id}`
                );

            const variationButton =
                document.getElementById(
                    `variation-${mapping.id}`
                );

            const syncVariationButton =
                document.getElementById(
                    `sync-variation-${mapping.id}`
                );

            const syncItemsButton =
                document.getElementById(
                    `sync-${mapping.id}`
                );


            /*
            |--------------------------------------------------------------------------
            | GAME CHANGE
            |--------------------------------------------------------------------------
            */

            gameSelect.addEventListener(
                'change',
                function () {

                    const hasGame =
                        !!this.value;

                    variationButton.disabled =
                        !hasGame;

                    syncVariationButton.disabled =
                        !hasGame;

                    syncItemsButton.disabled =
                        !hasGame;

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SAVE GAME
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


            /*
            |--------------------------------------------------------------------------
            | SHOW VARIATIONS
            |--------------------------------------------------------------------------
            */

            variationButton.addEventListener(
                'click',
                function () {

                    toggleVariations(
                        mapping
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SYNC VARIATIONS
            |--------------------------------------------------------------------------
            */

            syncVariationButton.addEventListener(
                'click',
                function () {

                    syncMappingVariations(
                        mapping
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | SYNC ITEMS
            |--------------------------------------------------------------------------
            */

            syncItemsButton.addEventListener(
                'click',
                function () {

                    syncMappingItems(
                        mapping
                    );

                }
            );

        });
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE PRODUCT GAME
    |--------------------------------------------------------------------------
    */

    async function saveMapping(mappingId)
    {
        const gameSelect =
            document.getElementById(
                `game-${mappingId}`
            );

        const saveButton =
            document.getElementById(
                `save-${mappingId}`
            );

        const gameId =
            gameSelect.value;

        if (!gameId) {

            showError(
                'Silakan pilih Game lokal terlebih dahulu.'
            );

            gameSelect.focus();

            return;
        }

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

                            /*
                             * Category Product sengaja NULL.
                             * Category authoritative berada
                             * pada setiap variation.
                             */
                            category_id:
                                null,

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
                    'Gagal menyimpan Game mapping.'
                );
            }

            showSuccess(
                'Game berhasil disimpan.'
            );

            const statusElement =
                document.getElementById(
                    `status-${mappingId}`
                );

            if (statusElement) {

                statusElement.innerHTML = `
                    <span class="badge bg-success">
                        <i class="fas fa-check me-1"></i>
                        Game Mapped
                    </span>
                `;
            }

            /*
             * Aktifkan tombol
             */

            document.getElementById(
                `variation-${mappingId}`
            ).disabled = false;

            document.getElementById(
                `sync-variation-${mappingId}`
            ).disabled = false;

            document.getElementById(
                `sync-${mappingId}`
            ).disabled = false;

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
    | LOAD VARIATIONS
    |--------------------------------------------------------------------------
    */

    async function loadVariations(mapping)
    {
        const container =
            document.getElementById(
                `variation-container-${mapping.id}`
            );

        if (!container) {
            return;
        }

        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <div class="text-muted mt-2">
                    Memuat variation...
                </div>
            </div>
        `;

        try {

            const response =
                await fetch(
                    `${URLs.variations}/${mapping.id}/variations`,
                    {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );

            const result =
                await response.json();

            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil variation.'
                );
            }

            const variations =
                result.data || [];

            renderVariations(
                mapping,
                variations
            );

        } catch (error) {

            console.error(
                'loadVariations:',
                error
            );

            container.innerHTML = `
                <div class="alert alert-danger mb-0">
                    ${escapeHtml(error.message)}
                </div>
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE VARIATIONS
    |--------------------------------------------------------------------------
    */

    async function toggleVariations(mapping)
    {
        const row =
            document.getElementById(
                `variation-row-${mapping.id}`
            );

        const button =
            document.getElementById(
                `variation-${mapping.id}`
            );

        if (!row) {
            return;
        }

        const isHidden =
            row.style.display === 'none';

        if (isHidden) {

            row.style.display =
                'table-row';

            button.innerHTML = `
                <i class="fas fa-chevron-up"></i>
                <span class="d-none d-xl-inline ms-1">
                    Hide
                </span>
            `;

            await loadVariations(mapping);

        } else {

            row.style.display =
                'none';

            button.innerHTML = `
                <i class="fas fa-list-ul"></i>
                <span class="d-none d-xl-inline ms-1">
                    Variations
                </span>
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER VARIATIONS
    |--------------------------------------------------------------------------
    */

    function renderVariations(
        mapping,
        variations
    )
    {
        const container =
            document.getElementById(
                `variation-container-${mapping.id}`
            );

        if (!container) {
            return;
        }

        if (!variations.length) {

            container.innerHTML = `
                <div class="alert alert-warning mb-0">

                    <i class="fas fa-info-circle me-1"></i>

                    Belum ada variation.
                    Klik <strong>Sync Variation</strong>
                    terlebih dahulu.

                </div>
            `;

            return;
        }


        container.innerHTML = `

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-2 mb-3">

                <div>

                    <h6 class="fw-bold mb-1">
                        <i class="fas fa-layer-group text-primary me-1"></i>
                        Variations
                    </h6>

                    <small class="text-muted">
                        ${escapeHtml(mapping.product_name)}
                        · ${variations.length} variation
                    </small>

                </div>

                <div>

                    <span class="badge bg-success me-1">
                        ${variations.filter(
                            variation =>
                                variation.category_id !== null
                        ).length}
                        Mapped
                    </span>

                    <span class="badge bg-secondary">
                        ${variations.filter(
                            variation =>
                                variation.category_id === null
                        ).length}
                        Belum Mapping
                    </span>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-sm table-bordered
                              align-middle bg-white mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>
                                Variation
                            </th>

                            <th style="width:130px;">
                                MooGold ID
                            </th>

                            <th style="width:140px;">
                                Harga
                            </th>

                            <th style="width:130px;">
                                Stock
                            </th>

                            <th style="width:260px;">
                                Category Lokal
                            </th>

                            <th style="width:100px;">
                                Status
                            </th>

                            <th
                                class="text-center"
                                style="width:80px;"
                            >
                                Save
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        ${variations.map(
                            variation =>
                                renderVariationRow(
                                    mapping,
                                    variation
                                )
                        ).join('')}

                    </tbody>

                </table>

            </div>

        `;

        attachVariationEvents(
            mapping,
            variations
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER VARIATION ROW
    |--------------------------------------------------------------------------
    */

    function renderVariationRow(
        mapping,
        variation
    )
    {
        const stock =
            String(
                variation.stock_status || ''
            ).toLowerCase();

        const inStock =
            stock === 'instock';

        const stockBadge =
            inStock

                ? `
                    <span class="badge bg-success">
                        In Stock
                    </span>
                  `

                : `
                    <span class="badge bg-secondary">
                        ${escapeHtml(
                            variation.stock_status || 'Unknown'
                        )}
                    </span>
                  `;

        const active =
            variation.is_active
                ? 'checked'
                : '';

        return `
            <tr
                id="variation-item-${variation.id}"
            >

                <td>

                    <div class="fw-semibold">
                        ${escapeHtml(
                            variation.variation_name
                        )}
                    </div>

                </td>


                <td>

                    <code>
                        ${escapeHtml(
                            variation.moogold_variation_id
                        )}
                    </code>

                </td>


                <td>

                    <span class="fw-semibold">
                        ${formatMoney(
                            variation.variation_price
                        )}
                    </span>

                </td>


                <td>

                    ${stockBadge}

                </td>


                <td>

                    <select
                        id="variation-category-${variation.id}"
                        class="form-select form-select-sm variation-category"
                        data-variation-id="${variation.id}"
                    >

                        <option value="">
                            Memuat Category...
                        </option>

                    </select>

                </td>


                <td>

                    <div class="form-check form-switch">

                        <input
                            class="form-check-input variation-active"
                            type="checkbox"
                            id="variation-active-${variation.id}"
                            data-variation-id="${variation.id}"
                            ${active}
                        >

                        <label
                            class="form-check-label small"
                            for="variation-active-${variation.id}"
                        >
                            Aktif
                        </label>

                    </div>

                </td>


                <td class="text-center">

                    <button
                        type="button"
                        class="btn btn-primary btn-sm
                               save-variation"
                        data-variation-id="${variation.id}"
                        title="Simpan Mapping Variation"
                    >

                        <i class="fas fa-save"></i>

                    </button>

                </td>

            </tr>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | ATTACH VARIATION EVENTS
    |--------------------------------------------------------------------------
    */

    async function attachVariationEvents(
        mapping,
        variations
    )
    {
        let categories = [];

        try {

            categories =
                await loadCategories(
                    mapping.game_id
                );

        } catch (error) {

            console.error(
                'load variation categories:',
                error
            );

            categories = [];
        }


        variations.forEach(variation => {

            const categorySelect =
                document.getElementById(
                    `variation-category-${variation.id}`
                );

            const activeCheckbox =
                document.getElementById(
                    `variation-active-${variation.id}`
                );

            const saveButton =
                document.querySelector(
                    `.save-variation[data-variation-id="${variation.id}"]`
                );


            if (categorySelect) {

                categorySelect.innerHTML =
                    buildCategoryOptions(
                        categories,
                        variation.category_id
                    );

                categorySelect.disabled =
                    categories.length === 0;
            }


            if (saveButton) {

                saveButton.addEventListener(
                    'click',
                    function () {

                        saveVariation(
                            mapping,
                            variation.id
                        );

                    }
                );
            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE VARIATION
    |--------------------------------------------------------------------------
    */

async function saveVariation(
    mapping,
    variationId
)
{
    const categorySelect =
        document.getElementById(
            `variation-category-${variationId}`
        );

    const activeCheckbox =
        document.getElementById(
            `variation-active-${variationId}`
        );

    const saveButton =
        document.querySelector(
            `.save-variation[data-variation-id="${variationId}"]`
        );


    /*
    |--------------------------------------------------------------------------
    | Validasi DOM
    |--------------------------------------------------------------------------
    */

    if (!categorySelect) {

        showError(
            'Category variation tidak ditemukan.'
        );

        return;
    }

    if (!activeCheckbox) {

        showError(
            'Status variation tidak ditemukan.'
        );

        return;
    }

    if (!saveButton) {

        showError(
            'Tombol Save variation tidak ditemukan.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil Value
    |--------------------------------------------------------------------------
    */

    const categoryId =
        categorySelect.value;

    const isActive =
        activeCheckbox.checked;


    /*
    |--------------------------------------------------------------------------
    | Validasi Category
    |--------------------------------------------------------------------------
    */

    if (!categoryId) {

        showError(
            'Silakan pilih Category lokal untuk variation ini.'
        );

        categorySelect.focus();

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Validasi Mapping
    |--------------------------------------------------------------------------
    */

    if (!mapping || !mapping.id) {

        showError(
            'Product Mapping tidak ditemukan.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | PENTING:
    |
    | Route:
    |
    | /{mapping}/variation/{variation}
    |
    | Contoh:
    |
    | /15/variation/22
    |
    */

    const url =
        `${URLs.variationUpdate}/${mapping.id}/variation/${variationId}`;


    console.log(
        'saveVariation URL:',
        url
    );


    /*
    |--------------------------------------------------------------------------
    | Loading
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


    /*
    |--------------------------------------------------------------------------
    | Request
    |--------------------------------------------------------------------------
    */

    try {

        const response =
            await fetch(
                url,
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

                        category_id:
                            Number(categoryId),

                        is_active:
                            isActive

                    })

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Ambil Response sebagai TEXT dahulu
        |--------------------------------------------------------------------------
        |
        | Ini penting supaya kalau Laravel mengembalikan
        | HTML error, kita bisa melihat response sebenarnya.
        |
        */

        const responseText =
            await response.text();


        let result;

        try {

            result =
                JSON.parse(
                    responseText
                );

        } catch (jsonError) {

            console.error(
                'saveVariation response:',
                responseText
            );

            throw new Error(
                `Server mengembalikan response non-JSON. HTTP ${response.status}.`
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validasi Response
        |--------------------------------------------------------------------------
        */

        if (
            !response.ok ||
            !result.success
        ) {

            throw new Error(
                result.message ||
                'Gagal menyimpan mapping variation.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Berhasil
        |--------------------------------------------------------------------------
        */

        showSuccess(
            'Mapping variation berhasil disimpan.'
        );


        /*
        |--------------------------------------------------------------------------
        | Update tampilan row
        |--------------------------------------------------------------------------
        */

        const variationRow =
            document.getElementById(
                `variation-item-${variationId}`
            );

        if (variationRow) {

            variationRow.classList.add(
                'table-success'
            );

            setTimeout(
                function () {

                    variationRow.classList.remove(
                        'table-success'
                    );

                },
                1000
            );
        }


    } catch (error) {

        console.error(
            'saveVariation:',
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
    | SYNC VARIATIONS
    |--------------------------------------------------------------------------
    */

    async function syncMappingVariations(mapping)
    {
        if (!mapping.game_id) {

            showError(
                'Pilih dan simpan Game terlebih dahulu.'
            );

            return;
        }


        const confirmation =
            await Swal.fire({

                icon: 'question',

                title: 'Sync Variation?',

                html: `
                    <div class="text-start">

                        <div class="mb-2">
                            <strong>Product:</strong>
                            ${escapeHtml(
                                mapping.product_name
                            )}
                        </div>

                        <div>
                            Semua variation terbaru
                            dari MooGold akan diambil.
                        </div>

                        <small class="text-muted d-block mt-2">
                            Mapping Category yang sudah dibuat
                            tidak akan dihapus.
                        </small>

                    </div>
                `,

                showCancelButton: true,

                confirmButtonText:
                    'Ya, Sync Variation',

                cancelButtonText:
                    'Batal'

            });


        if (!confirmation.isConfirmed) {
            return;
        }


        const button =
            document.getElementById(
                `sync-variation-${mapping.id}`
            );

        if (!button) {
            return;
        }


        const originalHtml =
            button.innerHTML;

        button.disabled =
            true;

        button.innerHTML = `
            <span
                class="spinner-border spinner-border-sm"
            ></span>
        `;


        try {

            const response =
                await fetch(
                    `${URLs.syncVariations}/${mapping.id}/sync-variations`,
                    {
                        method: 'POST',

                        credentials: 'same-origin',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'X-CSRF-TOKEN':
                                '{{ csrf_token() }}'
                        }
                    }
                );


            const result =
                await response.json();


            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal sync variation.'
                );
            }


            const data =
                result.data || {};


            await Swal.fire({

                icon: 'success',

                title: 'Sync Variation Berhasil',

                html: `
                    <div class="text-start">

                        <div class="mb-2">
                            <strong>Product ID:</strong>
                            ${escapeHtml(
                                data.product_id ||
                                mapping.moogold_product_id
                            )}
                        </div>

                        <div class="mb-2">
                            <strong>Total Variation:</strong>
                            ${Number(
                                data.total_variations || 0
                            )}
                        </div>

                        <div class="mb-2 text-success">
                            <strong>Created:</strong>
                            ${Number(
                                data.created || 0
                            )}
                        </div>

                        <div>
                            <strong>Updated:</strong>
                            ${Number(
                                data.updated || 0
                            )}
                        </div>

                    </div>
                `,

                confirmButtonText:
                    'OK'

            });


            /*
             * Buka / refresh variation
             */

            const row =
                document.getElementById(
                    `variation-row-${mapping.id}`
                );

            if (row) {

                row.style.display =
                    'table-row';

            }

            await loadVariations(
                mapping
            );


        } catch (error) {

            console.error(
                'syncMappingVariations:',
                error
            );

            showError(
                error.message
            );

        } finally {

            button.disabled =
                false;

            button.innerHTML =
                originalHtml;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SYNC ITEMS
    |--------------------------------------------------------------------------
    */

    async function syncMappingItems(mapping)
    {
        if (!mapping.game_id) {

            showError(
                'Pilih dan simpan Game terlebih dahulu.'
            );

            return;
        }


        const confirmation =
            await Swal.fire({

                icon: 'question',

                title: 'Sync Variation ke Item?',

                html: `
                    <div class="text-start">

                        <div class="mb-2">
                            <strong>Product:</strong>
                            ${escapeHtml(
                                mapping.product_name
                            )}
                        </div>

                        <div>
                            Hanya variation yang sudah
                            memiliki Category lokal
                            yang akan dibuat atau diperbarui
                            menjadi Item.
                        </div>

                    </div>
                `,

                showCancelButton: true,

                confirmButtonText:
                    'Ya, Sync Item',

                cancelButtonText:
                    'Batal'

            });


        if (!confirmation.isConfirmed) {
            return;
        }


        const button =
            document.getElementById(
                `sync-${mapping.id}`
            );

        if (!button) {
            return;
        }


        const originalHtml =
            button.innerHTML;

        button.disabled =
            true;

        button.innerHTML = `
            <span
                class="spinner-border spinner-border-sm"
            ></span>
        `;


        try {

            const response =
                await fetch(
                    `${URLs.syncItems}/${mapping.id}/sync-items`,
                    {
                        method: 'POST',

                        credentials: 'same-origin',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'X-CSRF-TOKEN':
                                '{{ csrf_token() }}'
                        }
                    }
                );


            const responseText =
                await response.text();


            let result;

            try {

                result =
                    JSON.parse(
                        responseText
                    );

            } catch (error) {

                console.error(
                    'Sync Items response:',
                    responseText
                );

                throw new Error(
                    `Server mengembalikan response non-JSON. HTTP ${response.status}.`
                );
            }


            if (!response.ok || !result.success) {

                throw new Error(
                    result.message ||
                    'Gagal sync Item.'
                );
            }


            const data =
                result.data || {};


            await Swal.fire({

                icon: 'success',

                title: 'Sync Item Berhasil',

                html: `
                    <div class="text-start">

                        <div class="mb-2">
                            <strong>Total Variation Mapped:</strong>
                            ${Number(
                                data.total_mapped_variations || 0
                            )}
                        </div>

                        <div class="mb-2 text-success">
                            <strong>Created:</strong>
                            ${Number(
                                data.created || 0
                            )}
                        </div>

                        <div>
                            <strong>Updated:</strong>
                            ${Number(
                                data.updated || 0
                            )}
                        </div>

                    </div>
                `,

                confirmButtonText:
                    'OK'

            });


        } catch (error) {

            console.error(
                'syncMappingItems:',
                error
            );

            showError(
                error.message
            );

        } finally {

            button.disabled =
                false;

            button.innerHTML =
                originalHtml;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT MONEY
    |--------------------------------------------------------------------------
    */

    function formatMoney(value)
    {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '-';
        }

        const number =
            Number(value);

        if (Number.isNaN(number)) {
            return '-';
        }

        return new Intl.NumberFormat(
            'id-ID',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        ).format(number);
    }


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    function renderPagination(data)
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


        getPaginationPages(
            current,
            last
        ).forEach(page => {

            if (page === '...') {

                html += `
                    <li class="page-item disabled">
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
                (_, index) =>
                    index + 1
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


    window.loadMappingPage =
        function(page)
        {
            loadMappings(page);
        };


    /*
    |--------------------------------------------------------------------------
    | SYNC CATEGORY
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
                        Mapping Game lokal yang sudah ada
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

                const response =
                    await fetch(
                        URLs.sync,
                        {
                            method: 'POST',

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

                                category_id:
                                    categoryId

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


                Object.keys(
                    categoryCache
                ).forEach(key => {

                    delete categoryCache[key];

                });


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
                                    syncData.category_id ||
                                    categoryId
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


                await loadMappings(1);


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
    | REFRESH
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
    | FILTER
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
    | SEARCH
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


    searchInput.addEventListener(
        'keydown',
        function(event)
        {
            if (
                event.key === 'Enter'
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
    | CATEGORY ENTER
    |--------------------------------------------------------------------------
    */

    categoryInput.addEventListener(
        'keydown',
        function(event)
        {
            if (
                event.key === 'Enter'
            ) {

                event.preventDefault();

                loadMappings(1);
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */

    async function init()
    {
        try {

            await loadGames();

            await loadMappings(1);

        } catch (error) {

            console.error(
                'init:',
                error
            );

            showError(
                error.message
            );
        }
    }


    init();

});

</script>

@endsection
