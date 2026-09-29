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
    <th>Nama pengguna</th>
    <td>
        <div class="d-flex align-items-center gap-2">
            <span>{{ Auth::user()->name }}</span>
            <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="collapse" data-bs-target="#adminProfileNameEditor" aria-label="Edit nama pengguna" title="Edit nama pengguna">
                <i class="fa-solid fa-pen"></i>
            </button>
        </div>
    </td>
</tr>



                    <tr>
    <th>Email</th>
    <td>
        <div class="d-flex align-items-center gap-2">
            <span>{{ Auth::user()->email }}</span>
            <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="collapse" data-bs-target="#adminProfileEmailEditor" aria-label="Edit email" title="Edit email">
                <i class="fa-solid fa-pen"></i>
            </button>
        </div>
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
                <div id="adminProfileNameEditor" class="collapse px-3 pt-3 @if($errors->has('name') || session('status') === 'profile-name-updated') show @endif">
                    <form method="POST" action="{{ route('profile.name.update') }}" class="pb-3">
                        @if(session('status') === 'profile-name-updated')
                            <div class="alert alert-success py-2" role="status">Nama pengguna berhasil diperbarui.</div>
                        @endif
                        @csrf
                        @method('PATCH')
                        <label for="admin-profile-name" class="form-label">Nama pengguna</label>
                        <div class="input-group">
                            <input id="admin-profile-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', Auth::user()->name) }}" autocomplete="name" maxlength="255" required>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </form>
                </div>

                <div id="adminProfileEmailEditor" class="collapse px-3 @if($errors->has('email') || session('status') === 'profile-email-unchanged' || session('status') === 'verification-link-sent') show @endif">
                    <form method="POST" action="{{ route('profile.email.update') }}" class="pb-3">
                        @csrf
                        @method('PATCH')
                        <label for="admin-profile-email" class="form-label">Email</label>
                        <div class="input-group">
                            <input id="admin-profile-email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', Auth::user()->email) }}" autocomplete="email" required>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @if(session('status') === 'profile-email-unchanged')
                            <div class="form-text">Email yang dimasukkan sama dengan email saat ini.</div>
                        @endif
                    </form>
                </div>




            </div>




            @if(session('status') === 'verification-link-sent')
                <div class="alert alert-success mx-3 mt-3 mb-0" role="status">
                    Tautan verifikasi baru sudah dikirim ke email Anda.
                </div>
            @endif

            <div class="d-flex flex-wrap gap-2 px-3 pt-3">
                @unless(Auth::user()->hasVerifiedEmail())
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-envelope me-1"></i> Kirim ulang verifikasi
                        </button>
                    </form>
                @endunless

                <a href="{{ route('password.request') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-key me-1"></i> Lupa password?
                </a>
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
@if(session('status') === 'verification-link-sent' || session('status') === 'profile-email-unchanged' || session('status') === 'profile-name-updated' || $errors->has('email') || $errors->has('name'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('adminProfileModal');
                if (modal && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endpush
@endif