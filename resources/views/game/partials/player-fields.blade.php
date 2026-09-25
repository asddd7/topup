<div class="player-fields">

    <div class="checkout-section-title">

        <div class="checkout-section-icon">
            <i class="fa-solid fa-user"></i>
        </div>

        <div>
            <h3>Data Player</h3>

            <p>
                Masukkan data akun game kamu.
            </p>
        </div>

    </div>


    @foreach($game->player_fields ?? [] as $field)

        @php

            /*
            |--------------------------------------------------------------------------
            | BASIC FIELD DATA
            |--------------------------------------------------------------------------
            */

            $fieldName =
                trim(
                    (string) ($field['name'] ?? '')
                );

            $fieldLabel =
                $field['label']
                ?? $fieldName;

            $fieldType =
                strtolower(
                    trim(
                        (string) (
                            $field['type']
                            ?? 'text'
                        )
                    )
                );

            $fieldSource =
                $field['source']
                ?? 'manual';

            $inputMode =
                strtolower(
                    trim(
                        (string) (
                            $field['input_mode']
                            ?? 'input'
                        )
                    )
                );

            $moogoldField =
                trim(
                    (string) (
                        $field['moogold_field']
                        ?? ''
                    )
                );

            $placeholder =
                $field['placeholder']
                ?? '';

            $required =
                !empty(
                    $field['required']
                );

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE INTERNAL FIELD NAME
            |--------------------------------------------------------------------------
            */

            $normalizedName =
                strtolower(
                    trim(
                        preg_replace(
                            '/[^a-z0-9]+/',
                            '_',
                            $fieldName
                        ),
                        '_'
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | NORMALIZE MOO GOLD FIELD
            |--------------------------------------------------------------------------
            */

            $normalizedMooGoldField =
                strtolower(
                    trim(
                        preg_replace(
                            '/[^a-z0-9]+/',
                            '_',
                            $moogoldField
                        ),
                        '_'
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | USER ID DETECTION
            |--------------------------------------------------------------------------
            */

            $isUserId =
                in_array(
                    $normalizedName,
                    [
                        'uid',
                        'user_id',
                        'userid',
                        'role_id',
                        'player_id',
                        'account_id',
                    ],
                    true
                )
                ||
                in_array(
                    $normalizedMooGoldField,
                    [
                        'uid',
                        'user_id',
                        'userid',
                        'role_id',
                        'player_id',
                        'account_id',
                    ],
                    true
                );

            /*
            |--------------------------------------------------------------------------
            | SERVER DETECTION
            |--------------------------------------------------------------------------
            */

            $isServer =
                $fieldType === 'server'
                ||
                in_array(
                    $normalizedMooGoldField,
                    [
                        'server',
                        'server_id',
                        'region',
                        'region_id',
                    ],
                    true
                );

            /*
            |--------------------------------------------------------------------------
            | USER ID IS ALWAYS INPUT
            |--------------------------------------------------------------------------
            */

            if ($isUserId) {
                $inputMode = 'input';
            }

            /*
            |--------------------------------------------------------------------------
            | ID / DATA ATTRIBUTES
            |--------------------------------------------------------------------------
            */

            $inputId =
                'player_' . $fieldName;

        @endphp


        @if($fieldName !== '')

            <div class="player-field">

                <label
                    for="{{ $inputId }}"
                >

                    {{ $fieldLabel }}

                    @if($required)
                        <span>*</span>
                    @endif

                </label>


                {{-- ============================================================
                    SERVER + READ ONLY
                ============================================================ --}}

                @if(
                    $isServer &&
                    $inputMode === 'readonly'
                )

                    <div class="input-group">

                        <input
                            type="text"
                            id="{{ $inputId }}"
                            class="form-control"
                            value="{{ $game->moogold_server_name ?? 'Server belum dikonfigurasi' }}"
                            readonly
                        >

                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                    </div>


                    <input
                        type="hidden"
                        name="{{ $fieldName }}"
                        value="{{ $game->moogold_server_id ?? '' }}"
                        class="player-input moogold-server-id"
                        data-field-name="{{ $fieldName }}"
                        data-moogold-key="{{ $moogoldField }}"
                        data-moogold-source="moogold_server_list"
                        data-type="server"
                        data-input-mode="readonly"
                        data-moogold-field="server-id"

                        @if($required)
                            required
                        @endif
                    >


                {{-- ============================================================
                    SERVER + CAN INPUT
                ============================================================ --}}

                @elseif($isServer)

                    <input
                        type="text"
                        id="{{ $inputId }}"
                        name="{{ $fieldName }}"
                        class="form-control player-input moogold-server-id"
                        value="{{ old($fieldName) }}"
                        placeholder="{{ $placeholder }}"
                        data-field-name="{{ $fieldName }}"
                        data-moogold-key="{{ $moogoldField }}"
                        data-moogold-source="manual"
                        data-type="server"
                        data-input-mode="input"
                        data-moogold-field="server-id"

                        @if($required)
                            required
                        @endif
                    >


                {{-- ============================================================
                    SELECT + READ ONLY
                ============================================================ --}}

                @elseif(
                    $fieldType === 'select' &&
                    $inputMode === 'readonly'
                )

                    @php

                        $readonlyValue =
                            old(
                                $fieldName,
                                $field['value']
                                ?? ''
                            );

                    @endphp


                    <div class="input-group">

                        <input
                            type="text"
                            id="{{ $inputId }}"
                            class="form-control"
                            value="{{ $readonlyValue }}"
                            readonly
                        >

                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                    </div>


                    <input
                        type="hidden"
                        name="{{ $fieldName }}"
                        value="{{ $readonlyValue }}"
                        class="player-input"
                        data-field-name="{{ $fieldName }}"
                        data-moogold-key="{{ $moogoldField }}"
                        data-moogold-source="{{ $fieldSource }}"
                        data-type="select"
                        data-input-mode="readonly"
                    >


                {{-- ============================================================
                    SELECT + CAN INPUT
                ============================================================ --}}

                @elseif($fieldType === 'select')

                    <select
                        id="{{ $inputId }}"
                        name="{{ $fieldName }}"
                        class="player-input"
                        data-field-name="{{ $fieldName }}"
                        data-moogold-key="{{ $moogoldField }}"
                        data-moogold-source="{{ $fieldSource }}"
                        data-type="select"
                        data-input-mode="input"

                        @if($required)
                            required
                        @endif
                    >

                        <option value="">
                            Pilih {{ $fieldLabel }}
                        </option>


                        @foreach(
                            array_filter(
                                array_map(
                                    'trim',
                                    explode(
                                        ',',
                                        $field['options']
                                        ?? ''
                                    )
                                )
                            ) as $option
                        )

                            <option
                                value="{{ $option }}"
                                {{ old($fieldName) === $option ? 'selected' : '' }}
                            >
                                {{ $option }}
                            </option>

                        @endforeach

                    </select>


                {{-- ============================================================
                    TEXT / NUMBER / EMAIL + READ ONLY
                ============================================================ --}}

                @elseif($inputMode === 'readonly')

                    @php

                        $readonlyValue =
                            old(
                                $fieldName,
                                $field['value']
                                ?? ''
                            );

                    @endphp


                    <div class="input-group">

                        <input
                            type="{{ $fieldType }}"
                            id="{{ $inputId }}"
                            class="form-control"
                            value="{{ $readonlyValue }}"
                            placeholder="{{ $placeholder }}"
                            readonly
                        >

                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                    </div>


                    <input
                        type="hidden"
                        name="{{ $fieldName }}"
                        value="{{ $readonlyValue }}"
                        class="player-input"
                        data-field-name="{{ $fieldName }}"
                        data-moogold-key="{{ $moogoldField }}"
                        data-moogold-source="{{ $fieldSource }}"
                        data-type="{{ $fieldType }}"
                        data-input-mode="readonly"
                    >


                {{-- ============================================================
                    NORMAL INPUT
                ============================================================ --}}

                @else

                    <input
                        type="{{ $fieldType }}"
                        id="{{ $inputId }}"
                        name="{{ $fieldName }}"
                        class="form-control player-input"

                        value="{{ old($fieldName) }}"

                        data-field-name="{{ $fieldName }}"
                        data-moogold-key="{{ $moogoldField }}"
                        data-moogold-source="{{ $fieldSource }}"
                        data-type="{{ $fieldType }}"
                        data-input-mode="input"

                        @if($isUserId)
                            data-moogold-field="user-id"
                        @elseif($isServer)
                            data-moogold-field="server-id"
                        @endif

                        placeholder="{{ $placeholder }}"

                        @if($required)
                            required
                        @endif
                    >

                @endif

            </div>

        @endif

    @endforeach

</div>