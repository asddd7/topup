@extends('admin.layouts.app')

@section('title', 'MooGold Product Mapping')

@section('content')

<div class="container-fluid py-4">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                MooGold Product Mapping
            </h3>

            <p class="text-muted mb-0">
                Hubungkan product MooGold dengan Game lokal.
            </p>
        </div>

    </div>


    {{-- =========================================================
         FILTER CARD
    ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3 align-items-end">

                {{-- MooGold Category --}}

                <div class="col-md-3">

                    <label class="form-label fw-semibold">
                        MooGold Category ID
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

                <div class="col-md-3">

                    <label class="form-label fw-semibold">
                        Cari Product
                    </label>

                    <input
                        type="text"
                        id="searchProduct"
                        class="form-control"
                        placeholder="Nama product / ID..."
                    >

                </div>


                {{-- Mapping Filter --}}

                <div class="col-md-2">

                    <label class="form-label fw-semibold">
                        Status Mapping
                    </label>

                    <select
                        id="mappingFilter"
                        class="form-select"
                    >
                        <option value="">
                            Semua
                        </option>

                        <option value="1">
                            Sudah Mapping
                        </option>

                        <option value="0">
                            Belum Mapping
                        </option>
                    </select>

                </div>


                {{-- Sync --}}

                <div class="col-md-2">

                    <button
                        type="button"
                        id="btnSync"
                        class="btn btn-primary w-100"
                    >
                        <i class="fas fa-sync-alt me-1"></i>
                        Sync Category
                    </button>

                </div>


                {{-- Refresh --}}

                <div class="col-md-2">

                    <button
                        type="button"
                        id="btnRefresh"
                        class="btn btn-outline-secondary w-100"
                    >
                        <i class="fas fa-search me-1"></i>
                        Tampilkan
                    </button>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         SUMMARY
    ========================================================== --}}

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

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


        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

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


        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

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


    {{-- =========================================================
         TABLE
    ========================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="60">
                                #
                            </th>

                            <th>
                                MooGold Product
                            </th>

                            <th width="120">
                                MooGold ID
                            </th>

                            <th width="260">
                                Game Lokal
                            </th>

                            <th width="260">
                                Category Lokal
                            </th>

                            <th width="120">
                                Status
                            </th>

                            <th width="100">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody id="mappingTable">

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5 text-muted"
                            >
                                Loading...
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}

        <div class="card-footer bg-white">

            <div
                id="pagination"
                class="d-flex justify-content-center"
            ></div>

        </div>

    </div>

</div>


{{-- =========================================================
     SCRIPT
========================================================== --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    let games = [];

    let currentPage = 1;


    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const categoryInput =
        document.getElementById('moogoldCategoryId');

    const searchInput =
        document.getElementById('searchProduct');

    const mappingFilter =
        document.getElementById('mappingFilter');

    const table =
        document.getElementById('mappingTable');


    /*
    |--------------------------------------------------------------------------
    | Load Games
    |--------------------------------------------------------------------------
    */

    async function loadGames()
    {
        try {

            const response = await fetch(
                '{{ url("/api/v1/admin/moogold/product-mapping/games") }}',
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const result = await response.json();

            if (!result.success) {
                throw new Error(
                    result.message || 'Gagal mengambil game.'
                );
            }

            games = result.data || [];

        } catch (error) {

            console.error(error);

            alert(
                'Gagal mengambil daftar Game lokal.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Load Mapping
    |--------------------------------------------------------------------------
    */

    async function loadMappings(page = 1)
    {
        currentPage = page;

        table.innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="text-center py-5"
                >
                    <div class="spinner-border"></div>

                    <div class="mt-2 text-muted">
                        Memuat product...
                    </div>
                </td>
            </tr>
        `;


        const categoryId =
            categoryInput.value.trim();

        if (!categoryId) {

            table.innerHTML = `
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


        const params = new URLSearchParams();

        params.set(
            'category_id',
            categoryId
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

            const response = await fetch(
                '{{ url("/api/v1/admin/moogold/product-mapping") }}?' +
                params.toString(),
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const result = await response.json();

            if (!result.success) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil mapping.'
                );
            }

            renderMappings(
                result.data
            );

        } catch (error) {

            console.error(error);

            table.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="text-center py-5 text-danger"
                    >
                        ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    function renderMappings(data)
    {
        const rows = data.data || [];

        document.getElementById(
            'totalProducts'
        ).innerText = data.total || 0;


        /*
        |--------------------------------------------------------------------------
        | Hitung mapped pada hasil sekarang
        |--------------------------------------------------------------------------
        */

        const mapped = rows.filter(
            row => row.game_id !== null
        ).length;

        document.getElementById(
            'mappedProducts'
        ).innerText = mapped;


        document.getElementById(
            'unmappedProducts'
        ).innerText =
            rows.length - mapped;


        if (!rows.length) {

            table.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="text-center py-5 text-muted"
                    >
                        Tidak ada product.
                    </td>
                </tr>
            `;

            renderPagination(data);

            return;
        }


        table.innerHTML = rows.map(
            (mapping, index) => {

                return renderRow(
                    mapping,
                    index
                );

            }
        ).join('');


        renderPagination(data);


        /*
        |--------------------------------------------------------------------------
        | Attach events
        |--------------------------------------------------------------------------
        */

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


            gameSelect.addEventListener(
                'change',
                function () {

                    loadCategories(
                        mapping.id,
                        this.value
                    );

                }
            );


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
    | Render Row
    |--------------------------------------------------------------------------
    */

    function renderRow(mapping, index)
    {
        const number =
            ((currentPage - 1) * 50) +
            index +
            1;


        const gameOptions = [
            `<option value="">-- Pilih Game --</option>`,

            ...games.map(game => {

                const selected =
                    mapping.game_id == game.id
                        ? 'selected'
                        : '';

                return `
                    <option
                        value="${game.id}"
                        ${selected}
                    >
                        ${escapeHtml(game.game_name)}
                    </option>
                `;
            })
        ].join('');


        const status = mapping.game_id
            ? `
                <span class="badge bg-success">
                    Mapped
                </span>
            `
            : `
                <span class="badge bg-warning text-dark">
                    Belum
                </span>
            `;


        return `
            <tr>

                <td>
                    ${number}
                </td>

                <td>

                    <div class="fw-semibold">
                        ${escapeHtml(
                            mapping.product_name
                        )}
                    </div>

                </td>

                <td>

                    <code>
                        ${escapeHtml(
                            mapping.moogold_product_id
                        )}
                    </code>

                </td>

                <td>

                    <select
                        id="game-${mapping.id}"
                        class="form-select form-select-sm"
                    >

                        ${gameOptions}

                    </select>

                </td>

                <td>

                    <select
                        id="category-${mapping.id}"
                        class="form-select form-select-sm"
                        ${mapping.game_id ? '' : 'disabled'}
                    >

                        <option value="">
                            -- Pilih Category --
                        </option>

                        ${
                            mapping.category
                                ? `
                                    <option
                                        value="${mapping.category.id}"
                                        selected
                                    >
                                        ${escapeHtml(
                                            mapping.category.category_name
                                        )}
                                    </option>
                                `
                                : ''
                        }

                    </select>

                </td>

                <td>

                    <span id="status-${mapping.id}">
                        ${status}
                    </span>

                </td>

                <td>

                    <button
                        type="button"
                        id="save-${mapping.id}"
                        class="btn btn-sm btn-primary"
                    >
                        <i class="fas fa-save"></i>
                    </button>

                </td>

            </tr>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | Load Categories
    |--------------------------------------------------------------------------
    */

    async function loadCategories(
        mappingId,
        gameId
    )
    {
        const select =
            document.getElementById(
                `category-${mappingId}`
            );


        select.innerHTML = `
            <option value="">
                Memuat...
            </option>
        `;


        select.disabled = true;


        if (!gameId) {

            select.innerHTML = `
                <option value="">
                    -- Pilih Category --
                </option>
            `;

            return;
        }


        try {

            const response = await fetch(
                '{{ url("/api/v1/admin/moogold/product-mapping/categories") }}?' +
                new URLSearchParams({
                    game_id: gameId
                }),
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );


            const result =
                await response.json();


            if (!result.success) {

                throw new Error(
                    result.message ||
                    'Gagal mengambil category.'
                );
            }


            select.innerHTML = `
                <option value="">
                    -- Pilih Category --
                </option>

                ${
                    result.data.map(category => `
                        <option
                            value="${category.id}"
                        >
                        ${escapeHtml(
                            category.category_name
                        )}
                        </option>
                    `).join('')
                }
            `;


            select.disabled = false;

        } catch (error) {

            console.error(error);

            select.innerHTML = `
                <option value="">
                    Gagal mengambil category
                </option>
            `;
        }
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
        const gameId =
            document.getElementById(
                `game-${mappingId}`
            ).value;


        const categoryId =
            document.getElementById(
                `category-${mappingId}`
            ).value;


        if (!gameId) {

            alert(
                'Pilih Game lokal terlebih dahulu.'
            );

            return;
        }


        if (!categoryId) {

            alert(
                'Pilih Category lokal terlebih dahulu.'
            );

            return;
        }


        try {

            const response = await fetch(
                '{{ url("/api/v1/admin/moogold/product-mapping") }}/' +
                mappingId,
                {
                    method: 'PUT',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            '{{ csrf_token() }}'
                    },

                    body: JSON.stringify({

                        game_id:
                            parseInt(gameId),

                        category_id:
                            parseInt(categoryId),

                        is_active:
                            true
                    })
                }
            );


            const result =
                await response.json();


            if (!response.ok) {

                throw new Error(
                    result.message ||
                    'Gagal menyimpan mapping.'
                );
            }


            alert(
                'Mapping berhasil disimpan.'
            );


            loadMappings(
                currentPage
            );

        } catch (error) {

            console.error(error);

            alert(
                error.message
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    function renderPagination(data)
    {
        const pagination =
            document.getElementById(
                'pagination'
            );


        if (data.last_page <= 1) {

            pagination.innerHTML = '';

            return;
        }


        let html = `
            <div class="btn-group">
        `;


        if (data.current_page > 1) {

            html += `
                <button
                    class="btn btn-outline-secondary"
                    onclick="loadMappingPage(${data.current_page - 1})"
                >
                    Previous
                </button>
            `;
        }


        html += `
            <button
                class="btn btn-outline-secondary"
                disabled
            >
                ${data.current_page}
                /
                ${data.last_page}
            </button>
        `;


        if (
            data.current_page <
            data.last_page
        ) {

            html += `
                <button
                    class="btn btn-outline-secondary"
                    onclick="loadMappingPage(${data.current_page + 1})"
                >
                    Next
                </button>
            `;
        }


        html += `
            </div>
        `;


        pagination.innerHTML = html;
    }


    /*
    |--------------------------------------------------------------------------
    | Global pagination
    |--------------------------------------------------------------------------
    */

    window.loadMappingPage =
        function(page)
        {
            loadMappings(page);
        };


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value)
    {
        if (value === null ||
            value === undefined) {

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
    | Buttons
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'btnRefresh'
    ).addEventListener(
        'click',
        function () {

            loadMappings(1);

        }
    );


    document.getElementById(
        'btnSync'
    ).addEventListener(
        'click',
        async function () {

            const categoryId =
                categoryInput.value.trim();


            if (!categoryId) {

                alert(
                    'Masukkan MooGold Category ID.'
                );

                return;
            }


            const button = this;


            button.disabled = true;

            button.innerHTML = `
                <span
                    class="spinner-border spinner-border-sm me-1"
                ></span>

                Syncing...
            `;


            try {

                const response =
                    await fetch(
                        '{{ url("/api/v1/admin/moogold/product-mapping/sync-category") }}',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

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


                if (!response.ok) {

                    throw new Error(
                        result.message ||
                        'Sync gagal.'
                    );
                }


                alert(
                    `Sync berhasil.\n\n` +
                    `Total: ${
                        result.data.total_products
                    }\n` +
                    `Created: ${
                        result.data.created
                    }\n` +
                    `Updated: ${
                        result.data.updated
                    }`
                );


                loadMappings(1);

            } catch (error) {

                console.error(error);

                alert(
                    error.message
                );

            } finally {

                button.disabled = false;

                button.innerHTML = `
                    <i class="fas fa-sync-alt me-1"></i>
                    Sync Category
                `;
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Search dengan Enter
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener(
        'keydown',
        function(event) {

            if (event.key === 'Enter') {

                loadMappings(1);

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Init
    |--------------------------------------------------------------------------
    */

    async function init()
    {
        await loadGames();

        await loadMappings(1);
    }


    init();

});

</script>

@endsection