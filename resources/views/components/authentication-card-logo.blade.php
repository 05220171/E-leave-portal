{{-- resources/views/components/authentication-card-logo.blade.php --}}
<div class="flex flex-col items-center"> {{-- Flex container to center heading and logo --}}

    {{-- E-Leave Portal Heading - Placed ABOVE the logo --}}
    <h1 class="portal-heading text-3xl sm:text-4xl font-bold text-gray-800 dark:text-gray-200 mb-4 text-center">
        {{-- text-3xl: Large text, sm:text-4xl: Even larger on small screens and up --}}
        {{-- font-bold: Bold text --}}
        {{-- text-gray-800 dark:text-gray-200: Darker text color, with dark mode variant --}}
        {{-- mb-4: Margin below the heading (1rem) to space it from the logo --}}
        {{-- text-center: Ensure heading text is centered --}}
        E-Leave Portal
    </h1>

    {{-- Logo Link and Image --}}
    <a href="/">
        <div class="login-logo-circle-container"> {{-- Removed mx-auto and mb-X from here, parent div handles centering and spacing is on heading --}}
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }} Logo" class="login-page-logo-circle-img">
        </div>
    </a>
</div>