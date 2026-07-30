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

<div class="mb-3">

<label class="form-label">

Jenis Input Player

</label>


<select name="player_input_type"
class="form-select" value="{{ $game->player_input_type}}">


<option value="uid">

UID Saja

</option>


<option value="uid_server">

UID + Server

</option>


<option value="riot_id">

Riot ID

</option>


<option value="email">

Email

</option>


<option value="none">

Tidak Ada

</option>


</select>

</div>


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