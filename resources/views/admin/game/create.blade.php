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


                    {{-- Input Player --}}
                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Jenis Input Player
                        </label>

                        <select
                            name="player_input_type"
                            class="form-select">

                            <option value="uid"
                                {{ old('player_input_type')=='uid' ? 'selected' : '' }}>
                                UID
                            </option>

                            <option value="uid_server"
                                {{ old('player_input_type')=='uid_server' ? 'selected' : '' }}>
                                UID + Server
                            </option>

                            <option value="riot_id"
                                {{ old('player_input_type')=='riot_id' ? 'selected' : '' }}>
                                Riot ID + Tag
                            </option>

                            <option value="email"
                                {{ old('player_input_type')=='email' ? 'selected' : '' }}>
                                Email
                            </option>

                            <option value="login"
                                {{ old('player_input_type')=='login' ? 'selected' : '' }}>
                                Login ID
                            </option>

                            <option value="none"
                                {{ old('player_input_type')=='none' ? 'selected' : '' }}>
                                Tidak Memerlukan Input
                            </option>

                        </select>

                    </div>


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