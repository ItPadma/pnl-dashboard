<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title')</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="{{ asset('assets/img/favicon.png') }}"
      type="image/x-icon"
    />
    @yield('style')
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["{{ asset('assets/css/fonts.min.css')}}"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>
    <script>
    </script>
    <script src="{{ asset('assets/js/plugin/pusher/pusher.min.js')}}"></script>
    <script src="{{ asset('assets/js/plugin/laravel-echo/echo.iife.js')}}"></script>
    <script>
        window.userID = @json(auth()->check() ? auth()->user()->id : null);
        // Initialize Pusher first
        window.Pusher = Pusher;

        // Initialize Echo for Reverb
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: "{{ config('broadcasting.connections.reverb.key') }}",
            wsHost: "{{ env('REVERB_HOST', 'localhost') }}",
            wsPort: "{{ env('REVERB_PORT', 6001) }}",
            wssPort: "{{ env('REVERB_PORT', 6001) }}",
            forceTLS: {{ config('broadcasting.connections.reverb.scheme') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['ws', 'wss']
        });

        document.addEventListener('DOMContentLoaded', function () {
            const userID = window.userID;

            if (!userID) {
                console.error('User ID is not set');
                return;
            }

            // Debug dengan cara yang lebih aman
            console.log('Echo initialized:', typeof window.Echo !== 'undefined');

            try {
                const channel = window.Echo.private(`App.User.${userID}`);
                console.log('Channel created successfully');
                channel.listen('.user.notification', (response) => {
                    // convert data to javascript object
                    switch (response.ntype) {
                        case 'info':
                            toastr.info(response.message, response.title);
                            break;
                        case 'success':
                            toastr.success(response.message, response.title);
                            break;
                        case 'warning':
                            toastr.warning(response.message, response.title);
                            break;
                        case 'error':
                            toastr.error(response.message, response.title);
                            break;
                        default:
                            console.warn('Unknown notification type:', response.type);
                    }
                });

                @yield('echo-script')

                console.log('Listener attached successfully');
            } catch (error) {
                console.error('Error setting up channel:', error);
                console.log('Available Echo methods:', Object.getOwnPropertyNames(window.Echo));
            }
        });
    </script>




    {{-- include style --}}
    @include('layouts.style')
  </head>
  <body>
    <div class="wrapper">
      @include('layouts.sidebar')
      <div class="main-panel">
        @include('layouts.navbar')
        @yield('content')
        @include('layouts.footer')
      </div>
    </div>
    @include('layouts.script')
  </body>
</html>
