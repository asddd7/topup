<div class="modal fade" 
     id="createGameModal" 
     tabindex="-1">


    <div class="modal-dialog modal-dialog-centered">


        <div class="modal-content shadow border-0 rounded-4">


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



            <form action="{{route('admin.game.store')}}"
                  method="POST"
                  enctype="multipart/form-data">


                @csrf


                <div class="modal-body">


                    <div class="mb-3">


                        <label class="form-label">

                            Nama Game

                        </label>


                        <input type="text"
                               name="game_name"
                               class="form-control"
                               placeholder="Contoh: Mobile Legends"
                               required>


                    </div>



                    <div class="mb-3">


                        <label class="form-label">

                            Publisher

                        </label>


                        <input type="text"
                               name="publisher"
                               class="form-control"
                               placeholder="Contoh: Moonton">


                    </div>

                    <div class="mb-3">

                    <label class="form-label">

                    Jenis Input Player

                    </label>


                    <select name="player_input_type"
                    class="form-select">


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

                    <option value="email">

                    Login

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


                        <input type="file"
                               name="game_logo"
                               class="form-control">


                        <small class="text-muted">

                            Format: JPG, PNG maksimal 2MB

                        </small>


                    </div>



                    <div class="form-check">


                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               class="form-check-input"
                               checked>


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
                            class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>

                        Simpan

                    </button>


                </div>



            </form>


        </div>

    </div>

</div>