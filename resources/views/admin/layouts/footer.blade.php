<footer class="admin-footer">

    <div class="admin-footer-container">

        {{-- =====================================================
             LEFT
        ====================================================== --}}

        <div class="admin-footer-brand">

            <div class="admin-footer-logo">

                @if(setting('app_logo'))

                    <img
                        src="{{ asset('storage/' . setting('app_logo')) }}"
                        alt="{{ setting('app_name', 'TOPUP') }}"
                    >

                @else

                    <div class="admin-footer-logo-icon">

                        <i class="fa-solid fa-gamepad"></i>

                    </div>

                @endif

            </div>


            <div>

                <strong class="admin-footer-name">

                    {{ setting('app_name', 'TOPUP') }}

                </strong>

                <span class="admin-footer-label">

                    Admin Panel

                </span>

            </div>

        </div>


        {{-- =====================================================
             CENTER / COPYRIGHT
        ====================================================== --}}

        <p class="admin-footer-copyright">

            &copy; {{ date('Y') }}

            {{ setting('app_name', 'TOPUP') }}.

            All rights reserved.

        </p>


        {{-- =====================================================
             RIGHT / STATUS
        ====================================================== --}}

        <div class="admin-footer-status">

            <span class="admin-footer-status-dot"></span>

            <span>
                System Online
            </span>

        </div>

    </div>

</footer>