<footer class="bg-dark text-white py-4">

<div class="container">

<div class="row">


<div class="col-md-6">

<h5 class="fw-bold">

{{ setting('app_name','TOPUP') }}

</h5>

</div>



<div class="col-md-6">


<h5>

Hubungi Kami

</h5>



@if(setting('whatsapp'))

<a href="https://wa.me/{{ setting('whatsapp') }}"
target="_blank"
class="btn btn-success btn-sm me-2">

<i class="fa-brands fa-whatsapp"></i>

WhatsApp

</a>

@endif





@if(setting('facebook'))

<a href="{{ setting('facebook') }}"
target="_blank"
class="btn btn-primary btn-sm me-2">

<i class="fa-brands fa-facebook"></i>

Facebook

</a>

@endif





@if(setting('instagram'))

<a href="{{ setting('instagram') }}"
target="_blank"
class="btn btn-danger btn-sm me-2">

<i class="fa-brands fa-instagram"></i>

Instagram

</a>

@endif





@if(setting('youtube'))

<a href="{{ setting('youtube') }}"
target="_blank"
class="btn btn-secondary btn-sm">

<i class="fa-brands fa-youtube"></i>

Youtube

</a>

@endif



</div>


</div>


<hr>


<div class="text-center">

&copy;

{{ date('Y') }}

{{ setting('app_name','TOPUP') }}

</div>


</div>

</footer>