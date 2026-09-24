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

        $fieldName = trim(
            (string) ($field['name'] ?? '')
        );

        if ($fieldName === '') {
            continue;
        }

        $moogoldField = trim(
            (string) ($field['moogold_field'] ?? '')
        );

        $fieldType = $field['type'] ?? 'text';

        $fieldNameLower = strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                $fieldName
            )
        );

        $moogoldFieldLower = strtolower(
            preg_replace(
                '/[^a-zA-Z0-9]/',
                '',
                $moogoldField
            )
        );

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

        $isServerId =
            $fieldType === 'server'
            ||
            in_array(
                $fieldNameLower,
                [
                    'server',
                    'serverid',
                ],
                true
            )
            ||
            in_array(
                $moogoldFieldLower,
                [
                    'server',
                    'serverid',
                ],
                true
            );

    @endphp


    <div class="player-field">

        <label for="player_{{ $fieldName }}">

            {{ $field['label'] ?? $fieldName }}

            @if(!empty($field['required']))
                <span>*</span>
            @endif

        </label>


        {{-- =================================================
             SERVER
        ================================================== --}}

        @if($fieldType === 'server')

            <div class="input-group">

                <input
                    type="text"
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
                data-type="server"
                data-field-name="{{ $fieldName }}"
                data-moogold-source="moogold"
                data-moogold-key="{{ $moogoldField }}"
                data-moogold-field="server-id"
            >


        {{-- =================================================
             SELECT
        ================================================== --}}

        @elseif($fieldType === 'select')

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
                type="{{ $fieldType }}"
                id="player_{{ $fieldName }}"
                name="{{ $fieldName }}"
                class="
                    player-input
                    {{ $isUserId ? 'moogold-user-id' : '' }}
                    {{ $isServerId ? 'moogold-server-id' : '' }}
                "
                data-type="{{ $fieldType }}"
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
