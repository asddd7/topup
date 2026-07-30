<div class="modal fade"
     id="editItemModal{{$item->id}}"
     tabindex="-1">


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content shadow border-0 rounded-4">


<div class="modal-header bg-warning">


<h5 class="modal-title">

<i class="fa-solid fa-box me-2"></i>

Edit Item

</h5>


<button type="button"
        class="btn-close"
        data-bs-dismiss="modal">

</button>


</div>




<form action="{{ route('admin.game.items.update',[$game->id,$item->id]) }}"
      method="POST"
      enctype="multipart/form-data">


@csrf

@method('PUT')



<div class="modal-body">





<div class="mb-3">

<label class="form-label">
Game
</label>


<input type="text"
class="form-control"
value="{{ $game->game_name }}"
readonly>


<input type="hidden"
name="game_id"
value="{{ $game->id }}">

</div>


<div class="mb-3">


<label>
Kategori
</label>


<select name="category_id"
        class="form-select">


@foreach($categories as $category)


<option value="{{$category->id}}"


@if($item->category_id == $category->id)

selected

@endif


>


{{$category->category_name}}


</option>


@endforeach


</select>


</div>





<div class="mb-3">


<label>
Nama Item
</label>


<input type="text"
       name="item_name"
       class="form-control"
       value="{{$item->item_name}}"
       required>


</div>





<div class="row">


<div class="col-md-6">


<label>
Qty
</label>


<input type="number"
       name="qty"
       class="form-control"
       value="{{$item->qty}}">


</div>



<div class="col-md-6">


<label>
Stock
</label>


<input type="number"
       name="stock"
       class="form-control"
       value="{{$item->stock}}">


</div>


</div>





<div class="mb-3 mt-3">


<label>
Harga
</label>


<input type="number"
       name="price"
       class="form-control"
       value="{{$item->price}}">


</div>





<div class="mb-3">


<label>
Deskripsi
</label>


<textarea name="description"
          class="form-control"
          rows="3">{{$item->description}}</textarea>


</div>

<div class="form-check mt-3">

    <input
        type="checkbox"
        class="form-check-input"
        name="top_seller"
        value="1"
        {{ old('top_seller',$item->top_seller ?? false) ? 'checked' : '' }}>

    <label class="form-check-label">

        Jadikan Top Seller

    </label>

</div>



<div class="mb-3">


<label>
Gambar Saat Ini
</label>


<br>


@if($item->image)


<img src="{{asset('storage/'.$item->image)}}"
     width="80"
     class="rounded mb-2">


@endif



<input type="file"
       name="image"
       class="form-control">


<small class="text-muted">

Kosongkan jika tidak mengganti gambar

</small>


</div>





<div class="form-check">


<input type="checkbox"
       name="is_active"
       value="1"
       class="form-check-input"


{{$item->is_active ? 'checked':''}}


>


<label>

Item Aktif

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