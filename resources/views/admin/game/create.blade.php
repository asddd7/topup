<div class="modal fade"
     id="createGameModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-gamepad me-2"></i>
                    Tambah Game
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('admin.game.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-body">

                    {{-- Nama Game --}}
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
                            required>

                        @error('game_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Publisher --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Publisher
                        </label>

                        <input
                            type="text"
                            name="publisher"
                            class="form-control @error('publisher') is-invalid @enderror"
                            value="{{ old('publisher') }}"
                            placeholder="Contoh: Moonton">

                    </div>

                    
                    <hr>

                    <h6 class="fw-bold mb-3">

                    <i class="fa-solid fa-id-card me-2"></i>

                    Field Input Player

                    </h6>


                    <div id="playerFieldsCreate">


                    </div>


                    <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="addPlayerFieldCreate()">

                    <i class="fa fa-plus"></i>

                    Tambah Field

                    </button>


                    {{-- Logo --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Logo Game
                        </label>

                        <input
                            type="file"
                            name="game_logo"
                            class="form-control"
                            accept="image/*">

                        <small class="text-muted">
                            JPG, PNG, WEBP • Maksimal 2MB
                        </small>

                    </div>


                    {{-- Status --}}
                    <input
                        type="hidden"
                        name="is_active"
                        value="0">

                    <div class="form-check">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            id="is_active"
                            checked>

                        <label
                            class="form-check-label"
                            for="is_active">

                            Game Aktif

                        </label>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>
                        Simpan Game

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

let fieldIndexCreate = 0;


function addPlayerFieldCreate(){

let i = fieldIndexCreate++;


document
.getElementById('playerFieldsCreate')
.insertAdjacentHTML(
'beforeend',

`

<div class="card mb-3 player-field">

<div class="card-body">


<div class="row">


<div class="col-md-3">


<label>

Nama Field

</label>


<input

type="text"

class="form-control"

name="player_fields[${i}][name]"

placeholder="uid"

>

</div>




<div class="col-md-3">


<label>

Label

</label>


<input

type="text"

class="form-control"

name="player_fields[${i}][label]"

placeholder="UID Player"

>

</div>




<div class="col-md-3">


<label>

Placeholder

</label>


<input

type="text"

class="form-control"

name="player_fields[${i}][placeholder]"

placeholder="Masukkan UID"

>

</div>





<div class="col-md-2">


<label>

Tipe

</label>


<select

class="form-select"

name="player_fields[${i}][type]">


<option value="text">

Text

</option>


<option value="number">

Number

</option>


<option value="email">

Email

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

value="1"

checked>


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


</script>