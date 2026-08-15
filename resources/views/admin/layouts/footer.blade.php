<footer class="topup-footer">

    <div class="topup-footer-container">

        {{-- ============================= --}}
        {{-- FOOTER TOP --}}
        {{-- ============================= --}}
        <div class="topup-footer-top">

            {{-- BRAND --}}
            <div class="topup-footer-brand">

                @if(setting('app_logo'))

                    <img
                        src="{{ asset('storage/' . setting('app_logo')) }}"
                        alt="{{ setting('app_name', 'TOPUP') }}"
                        class="topup-footer-logo"
                    >

                @else

                    <div class="topup-footer-logo-text">
                        {{ setting('app_name', 'TOPUP') }}
                    </div>

                @endif

            </div>


            {{-- SOCIAL MEDIA --}}
            <ul class="topup-footer-social">

                @if(setting('facebook'))
                    <li>
                        <a
                            href="{{ setting('facebook') }}"
                            target="_blank"
                            rel="noreferrer"
                            aria-label="Facebook"
                        >
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                    </li>
                @endif


                @if(setting('instagram'))
                    <li>
                        <a
                            href="{{ setting('instagram') }}"
                            target="_blank"
                            rel="noreferrer"
                            aria-label="Instagram"
                        >
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                    </li>
                @endif


                @if(setting('youtube'))
                    <li>
                        <a
                            href="{{ setting('youtube') }}"
                            target="_blank"
                            rel="noreferrer"
                            aria-label="YouTube"
                        >
                            <i class="fa-brands fa-youtube"></i>
                        </a>
                    </li>
                @endif


                @if(setting('whatsapp'))
                    <li>
                        <a
                            href="https://wa.me/{{ setting('whatsapp') }}"
                            target="_blank"
                            rel="noreferrer"
                            aria-label="WhatsApp"
                        >
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </li>
                @endif

            </ul>

        </div>


        {{-- ============================= --}}
        {{-- FOOTER BOTTOM --}}
        {{-- ============================= --}}
        <div class="topup-footer-bottom">

            <p>
                &copy; {{ date('Y') }}
                {{ setting('app_name', 'TOPUP') }}.
                All rights reserved.
            </p>

            <div class="topup-footer-contact">

                @if(setting('whatsapp'))

                    <a
                        href="https://wa.me/{{ setting('whatsapp') }}"
                        target="_blank"
                    >
                        <i class="fa-brands fa-whatsapp"></i>
                        WhatsApp
                    </a>

                @endif

            </div>

        </div>

    </div>

</footer>