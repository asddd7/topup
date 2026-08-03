<div
class="offcanvas-lg offcanvas-start bg-white shadow"

tabindex="-1"

id="sidebar"

style="width:260px;">

<div class="offcanvas-header">

    <h4 class="fw-bold text-primary">

        {{ setting('app_name') }}

    </h4>

</div>



<div class="offcanvas-body p-0">


<div class="list-group list-group-flush">



<a href="{{ route('dashboard') }}"
class="list-group-item">


<i class="fa-solid fa-house me-2"></i>


Dashboard


</a>





@auth


<a href="#"
class="list-group-item"
data-bs-toggle="modal"
data-bs-target="#profileModal">


<i class="fa-solid fa-user me-2"></i>


Profil


</a>





<a href="{{ route('order.index') }}"
class="list-group-item">


<i class="fa-solid fa-cart-shopping me-2"></i>


Pesanan Saya


</a>



@endauth






@guest


<a href="{{ route('login') }}"
class="list-group-item">


<i class="fa-solid fa-right-to-bracket me-2"></i>


Login


</a>


@endguest






@auth


<form
action="{{ route('logout') }}"
method="POST">


@csrf


<button

class="list-group-item 
list-group-item-action 
text-danger 
border-0 
w-100 
text-start">


<i class="fa-solid fa-right-from-bracket me-2"></i>


Logout


</button>


</form>


@endauth



</div>


</div>


</div>