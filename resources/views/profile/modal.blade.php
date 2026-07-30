<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-4">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-user-circle me-2"></i>
                    Profil Saya
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <div class="text-center mb-4">

                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D6EFD&color=fff&size=120"
                         class="rounded-circle shadow"
                         width="110">

                    <h5 class="mt-3 mb-0">
                        {{ Auth::user()->name }}
                    </h5>

                    <small class="text-muted">
                        Member
                    </small>

                </div>

                <table class="table table-borderless">

                    <tr>
                        <th width="35%">Nama</th>
                        <td>{{ Auth::user()->name }}</td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td>{{ Auth::user()->email }}</td>
                    </tr>

                    <tr>
                        <th>Bergabung</th>
                        <td>{{ Auth::user()->created_at->format('d M Y') }}</td>
                    </tr>

                    <tr>
                        <th>Email Verified</th>
                        <td>

                            @if(Auth::user()->email_verified_at)

                                <span class="badge bg-success">
                                    Verified
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Belum Verified
                                </span>

                            @endif

                        </td>
                    </tr>

                </table>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Tutup
                </button>

                <a href="{{ route('logout') }}"
                   class="btn btn-danger"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">

                    Logout

                </a>

                <form id="logout-form"
                      action="{{ route('logout') }}"
                      method="POST"
                      class="d-none">

                    @csrf

                </form>

            </div>

        </div>
    </div>
</div>