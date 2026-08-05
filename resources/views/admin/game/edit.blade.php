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

<hr>

<h6 class="fw-bold mb-3">

<i class="fa-solid fa-id-card me-2"></i>

Field Input Player

</h6>

<div id="playerFields{{ $game->id }}">

@php

$fields = $game->player_fields ?? [];

@endphp

@foreach($fields as $i => $field)

<div class="card mb-3 player-field">

<div class="card-body">

<div class="row">

<div class="col-md-3">

<label>Nama Field</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][name]"
value="{{ $field['name'] ?? '' }}"
placeholder="uid">

</div>

<div class="col-md-3">

<label>Label</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][label]"
value="{{ $field['label'] ?? '' }}"
placeholder="UID Player">

</div>

<div class="col-md-3">

<label>Placeholder</label>

<input
type="text"
class="form-control"
name="player_fields[{{ $i }}][placeholder]"
value="{{ $field['placeholder'] ?? '' }}"
placeholder="Masukkan UID">

</div>

<div class="col-md-2">

<label>Tipe</label>

<select
class="form-select"
name="player_fields[{{ $i }}][type]">

<option value="text"
{{ ($field['type'] ?? '')=='text'?'selected':'' }}>
Text
</option>

<option value="number"
{{ ($field['type'] ?? '')=='number'?'selected':'' }}>
Number
</option>

<option value="email"
{{ ($field['type'] ?? '')=='email'?'selected':'' }}>
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
name="player_fields[{{ $i }}][required]"
value="1"
{{ !empty($field['required']) ? 'checked' : '' }}>

<label class="form-check-label">

Wajib Diisi

</label>

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

</script>