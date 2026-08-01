<div class="modal fade"
     id="logModal{{ $log->id }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    Detail Activity Log
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="row">

                    <div class="col-md-6">

                        <table class="table table-bordered">

                            <tr>
                                <th width="180">Tanggal</th>
                                <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            </tr>

                            <tr>
                                <th>User</th>
                                <td>{{ $log->user->name ?? 'Guest' }}</td>
                            </tr>

                            <tr>
                                <th>Module</th>
                                <td>{{ $log->module }}</td>
                            </tr>

                            <tr>
                                <th>Action</th>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $log->action }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Activity</th>
                                <td>{{ $log->activity }}</td>
                            </tr>

                            <tr>
                                <th>Target ID</th>
                                <td>{{ $log->target_id ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Target</th>
                                <td>{{ $log->target_name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>IP Address</th>
                                <td>{{ $log->ip_address }}</td>
                            </tr>

                        </table>

                    </div>

                    <div class="col-md-6">

                        <table class="table table-bordered">

                            <tr>
                                <th>User Agent</th>
                            </tr>

                            <tr>
                                <td style="word-break:break-word;">
                                    {{ $log->user_agent }}
                                </td>
                            </tr>

                        </table>

                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-6">

                        <div class="card border-danger">

                            <div class="card-header bg-danger text-white">

                                <i class="fa-solid fa-trash me-2"></i>

                                Data Sebelum

                            </div>

                            <div class="card-body">

                                @if($log->old_data)

<pre class="mb-0">{{ json_encode($log->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

                                @else

                                    <span class="text-muted">
                                        Tidak ada data.
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="card border-success">

                            <div class="card-header bg-success text-white">

                                <i class="fa-solid fa-check me-2"></i>

                                Data Sesudah

                            </div>

                            <div class="card-body">

                                @if($log->new_data)

<pre class="mb-0">{{ json_encode($log->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

                                @else

                                    <span class="text-muted">
                                        Tidak ada data.
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Tutup

                </button>

            </div>

        </div>

    </div>

</div>