
<!-- Cookie Consent Popup -->
<div id="cookieConsent"
     class="hidden fixed bottom-16 inset-x-0 z-[9999] bg-white/95 border-t border-gray-200 shadow-lg animate-slideUp">

    <div class="max-w-7xl mx-auto px-2 py-2 grid grid-cols-12 gap-2 items-center">

        <!-- Text -->
        <p class="col-span-10 text-xs text-gray-700 leading-relaxed">
            We use cookies to personalize content, analyze performance, enhance security, and deliver ads relevant to your interests.
            By continuing to use this website, you consent to our use of cookies as described in our
            <a href="{{ route('cookie.policy') }}" class="underline font-medium text-secondary_dark hover:text-gray-900">
                Cookies Policy
            </a>.
        </p>

        <!-- Button -->
        <div class="col-span-2 flex md:justify-end justify-center">
            <button id="acceptCookies"
                    class="bg-secondary_dark hover:bg-dark_green text-white text-sm font-semibold px-4 py-2 rounded-md uppercase transition">
                OK
            </button>
        </div>

    </div>
</div>


<style>
/* Custom animations - Tailwind doesn't have these specific keyframes */
@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideDown {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(100%);
        opacity: 0;
    }
}

.animate-slide-up {
    animation: slideUp 0.3s ease-out;
}

.animate-slide-down {
    animation: slideDown 0.3s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cookieConsent = document.getElementById('cookieConsent');
    const acceptButton = document.getElementById('acceptCookies');

    // Check if user has already accepted cookies
    const hasAcceptedCookies = localStorage.getItem('cookieConsent');

    // Show popup if user hasn't accepted yet
    if (!hasAcceptedCookies) {
        cookieConsent.classList.remove('hidden');
    }

    // Handle accept button click
    acceptButton.addEventListener('click', function() {
        // Store consent in localStorage
        localStorage.setItem('cookieConsent', 'accepted');
        localStorage.setItem('cookieConsentDate', new Date().toISOString());

        // Add slide down animation
        cookieConsent.classList.remove('animate-slide-up');
        cookieConsent.classList.add('animate-slide-down');

        // Hide the popup after animation completes
        setTimeout(() => {
            cookieConsent.classList.add('hidden');
        }, 300);
    });
});
</script>
