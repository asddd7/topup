<div class="modal fade" id="adminProfileModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow border-0 rounded-4">


            <div class="modal-header bg-dark text-white">

                <h5 class="modal-title">

                    <i class="fa-solid fa-user-shield me-2"></i>

                    Profil Admin

                </h5>


                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                </button>

            </div>



            <div class="modal-body">


                <div class="text-center mb-4">


                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=212529&color=fff&size=120"
                         class="rounded-circle shadow"
                         width="110">


                    <h5 class="mt-3 mb-0 fw-bold">

                        {{ Auth::user()->name }}

                    </h5>


                    <span class="badge bg-danger mt-2">

                        <i class="fa-solid fa-shield-halved me-1"></i>

                        Administrator

                    </span>


                </div>




                <table class="table table-borderless">


                    <tr>

                        <th width="40%">

                            Nama

                        </th>

                        <td>

                            {{ Auth::user()->name }}

                        </td>

                    </tr>



                    <tr>

                        <th>

                            Email

                        </th>

                        <td>

                            {{ Auth::user()->email }}

                        </td>

                    </tr>



                    <tr>

                        <th>

                            Role

                        </th>


                        <td>

                            @if(Auth::user()->role)

                                {{ Auth::user()->role->role_name }}

                            @else

                                Admin

                            @endif

                        </td>

                    </tr>




                    <tr>

                        <th>

                            Bergabung

                        </th>


                        <td>

                            {{ Auth::user()->created_at->format('d M Y') }}

                        </td>

                    </tr>




                    <tr>

                        <th>

                            Status Email

                        </th>


                        <td>


                            @if(Auth::user()->email_verified_at)


                                <span class="badge bg-success">

                                    <i class="fa-solid fa-check me-1"></i>

                                    Verified

                                </span>


                            @else


                                <span class="badge bg-warning text-dark">

                                    <i class="fa-solid fa-clock me-1"></i>

                                    Pending

                                </span>


                            @endif


                        </td>


                    </tr>



                    <tr>

                        <th>

                            Login Terakhir

                        </th>


                        <td>

                            {{ Auth::user()->updated_at->format('d M Y H:i') }}

                        </td>


                    </tr>



                </table>



            </div>




            <div class="modal-footer">


                <a href="{{ route('admin.dashboard') }}"
                   class="btn btn-primary">


                    <i class="fa-solid fa-gauge me-1"></i>

                    Dashboard


                </a>



                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">


                    Tutup


                </button>



                <form action="{{ route('logout') }}"
                      method="POST"
                      class="d-inline">


                    @csrf


                    <button class="btn btn-danger">


                        <i class="fa-solid fa-right-from-bracket me-1"></i>

                        Logout


                    </button>


                </form>



            </div>



        </div>

    </div>

</div>