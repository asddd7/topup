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
            | FIELD NAME
            |--------------------------------------------------------------------------
            */

            $fieldName = trim(
                (string) ($field['name'] ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | SKIP EMPTY FIELD
            |--------------------------------------------------------------------------
            */

            if ($fieldName === '') {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE FIELD NAME
            |--------------------------------------------------------------------------
            |
            | Contoh:
            |
            | UID
            | user_id
            | user-id
            | User ID
            |
            | menjadi:
            |
            | uid
            | userid
            |
            */

            $fieldNameLower =
                strtolower(
                    preg_replace(
                        '/[^a-zA-Z0-9]/',
                        '',
                        $fieldName
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | USER ID DETECTION
            |--------------------------------------------------------------------------
            */

            $isUserId =
                in_array(
                    $fieldNameLower,
                    [
                        'uid',
                        'userid',
                        'user',
                        'useraccount',
                        'accountid',
                        'playerid',
                        'player',
                        'gameid',
                        'id',
                    ],
                    true
                );


            /*
            |--------------------------------------------------------------------------
            | SERVER ID DETECTION
            |--------------------------------------------------------------------------
            */

            $isServerId =
                in_array(
                    $fieldNameLower,
                    [
                        'server',
                        'serverid',
                        'zone',
                        'zoneid',
                        'region',
                        'regionid',
                        'world',
                        'worldid',
                    ],
                    true
                );

        @endphp


        <div class="player-field">

            <label
                for="player_{{ $fieldName }}"
            >

                {{ $field['label'] ?? $fieldName }}

                @if(!empty($field['required']))
                    <span>*</span>
                @endif

            </label>


            {{-- =================================================
                 SELECT
            ================================================== --}}

            @if(($field['type'] ?? '') === 'select')

                <select
                    id="player_{{ $fieldName }}"
                    name="{{ $fieldName }}"
                    class="
                        player-input
                        {{ $isUserId ? 'moogold-user-id' : '' }}
                        {{ $isServerId ? 'moogold-server-id' : '' }}
                    "
                    data-type="select"
                    data-field-name="{{ $fieldName }}"

                    @if($isUserId)
                        data-moogold-field="user-id"
                    @endif

                    @if($isServerId)
                        data-moogold-field="server-id"
                    @endif

                    @if(!empty($field['required']))
                        required
                    @endif
                >

                    <option value="">
                        Pilih {{ $field['label'] ?? $fieldName }}
                    </option>


                    @foreach(
                        array_filter(
                            array_map(
                                'trim',
                                explode(
                                    ',',
                                    $field['options'] ?? ''
                                )
                            )
                        )
                        as $option
                    )

                        <option value="{{ $option }}">
                            {{ $option }}
                        </option>

                    @endforeach

                </select>


            {{-- =================================================
                 INPUT
            ================================================== --}}

            @else

                <input
                    type="{{ $field['type'] ?? 'text' }}"
                    id="player_{{ $fieldName }}"
                    name="{{ $fieldName }}"
                    class="
                        player-input
                        {{ $isUserId ? 'moogold-user-id' : '' }}
                        {{ $isServerId ? 'moogold-server-id' : '' }}
                    "
                    data-type="{{ $field['type'] ?? 'text' }}"
                    data-field-name="{{ $fieldName }}"

                    @if($isUserId)
                        data-moogold-field="user-id"
                    @endif

                    @if($isServerId)
                        data-moogold-field="server-id"
                    @endif

                    placeholder="{{ $field['placeholder'] ?? '' }}"

                    @if(!empty($field['required']))
                        required
                    @endif
                >

            @endif

        </div>

    @endforeach

</div>
