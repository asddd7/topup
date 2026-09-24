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

        if ($fieldName === '') {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | MOO GOLD FIELD
        |--------------------------------------------------------------------------
        */

        $moogoldField = trim(
            (string) ($field['moogold_field'] ?? '')
        );


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE FIELD NAME
        |--------------------------------------------------------------------------
        */

        $fieldNameLower = strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                $fieldName
            )
        );


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE MOO GOLD FIELD
        |--------------------------------------------------------------------------
        */

        $moogoldFieldLower = strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                $moogoldField
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
            )
            ||
            in_array(
                $moogoldFieldLower,
                [
                    'uid',
                    'userid',
                    'user',
                    'roleid',
                    'accountid',
                    'playerid',
                    'player',
                    'gameid',
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
            )
            ||
            in_array(
                $moogoldFieldLower,
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

                @if($moogoldField !== '')
                    data-moogold-source="moogold"
                    data-moogold-key="{{ $moogoldField }}"
                @endif

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

                @if($moogoldField !== '')
                    data-moogold-source="moogold"
                    data-moogold-key="{{ $moogoldField }}"
                @endif

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
