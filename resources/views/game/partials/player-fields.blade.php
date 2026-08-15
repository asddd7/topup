<div class="player-fields">

    <div class="checkout-section-title">

        <div class="checkout-section-icon">

            <i class="fa-solid fa-user"></i>

        </div>

        <div>

            <h3>
                Data Player
            </h3>

            <p>
                Masukkan data akun game kamu.
            </p>

        </div>

    </div>


    @foreach($game->player_fields ?? [] as $field)

        <div class="player-field">

            <label
                for="player_{{ $field['name'] }}"
            >

                {{ $field['label'] }}

                @if(!empty($field['required']))
                    <span>*</span>
                @endif

            </label>


            @if(($field['type'] ?? '') === 'select')

                <select
                    id="player_{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    class="player-input"
                    data-type="select"

                    @if(!empty($field['required']))
                        required
                    @endif
                >

                    <option value="">
                        Pilih {{ $field['label'] }}
                    </option>


                    @foreach(
                        explode(',', $field['options'] ?? '')
                        as $option
                    )

                        <option value="{{ trim($option) }}">

                            {{ trim($option) }}

                        </option>

                    @endforeach

                </select>

            @else

                <input
                    type="{{ $field['type'] ?? 'text' }}"
                    id="player_{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    class="player-input"
                    data-type="{{ $field['type'] ?? 'text' }}"
                    placeholder="{{ $field['placeholder'] ?? '' }}"

                    @if(!empty($field['required']))
                        required
                    @endif
                >

            @endif

        </div>

    @endforeach

</div>