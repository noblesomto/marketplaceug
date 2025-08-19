
<section class="px-2 pt-5  hidden lg:block border-t-2 border-t-gray-300">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-base">
            <div class="flex flex-col">
                <h4 class="font-bold">Marketplace Naija</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="/about-us" class="hover:text-secondary-200">About Us</a></li>
                    <li><a href="/career" class="hover:text-secondary-200">Career</a></li>
                    <li><a href="/privacy-policy" class="hover:text-secondary-200">Privacy Policy</a></li>
                    <li><a href="/cookie-policy" class="hover:text-secondary-200">Cookies Policy</a></li>
                    <li><a href="/billing-policy" class="hover:text-secondary-200">Billing Policy</a></li>
                    <li><a href="/copyright-policy" class="hover:text-secondary-200">Copyright Policy</a></li>
                </ul>
            </div>

            <div class="flex flex-col">
                <h4 class="font-bold">Help & Support</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="/safety-tips" class="hover:text-secondary-200">Tips for your safety</a></li>
                    <li><a href="/our-terms" class="hover:text-secondary-200">Terms of Use</a></li>
                    <li><a href="/contact-us" class="hover:text-secondary-200">Contact Us</a></li>
                    <li><a href="/payments-refunds" class="hover:text-secondary-200">Payment & Refund</a></li>
                    <li><a href="/how-it-works" class="hover:text-secondary-200">How It Works</a></li>
                    <li><a href="/faq" class="hover:text-secondary-200">FAQ</a></li>
                </ul>
            </div>

            <div class="flex flex-col">
                <h4 class="font-bold">Our Resources</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="#" class="hover:text-secondary-200">Blog</a></li>
                    <li><a href="#" class="hover:text-secondary-200">Mobile Apps</a></li>
                </ul>
                <div class="flex flex-col gap-2 mt-1">
                    <img class="w-24" src="{{ asset('frontend/images/app-store.svg') }}">
                    <img class="w-24" src="{{ asset('frontend/images/play-store.svg') }}">
                </div>
            </div>

            <div class="flex flex-col">
                <h4 class="font-bold">Social Media</h4>
                <ul class="flex flex-col space-y-1 mt-2">
                    <li><a href="https://www.facebook.com/MarketplaceNaijaOnline/" target="_blank" class="hover:text-secondary-200">Facebook</a></li>
                    <li><a href="https://www.instagram.com/marketplacenaija/" target="_blank" class="hover:text-secondary-200">Instagram</a></li>
                    <li><a href="https://www.tiktok.com/@marketplace.naija" target="_blank" class="hover:text-secondary-200">TikTok</a></li>
                    <li><a href="https://www.youtube.com/@marketplacenaija" target="_blank" class="hover:text-secondary-200">YouTube</a></li>
                    <li><a href="https://www.pinterest.com/marketplacenaija/" target="_blank" class="hover:text-secondary-200">Pinterest</a></li>
                </ul>
            </div>

        </div>
        

    <!--Copy Rights -->
    <div class="flex justify-center gap-2 mt-5 pb-10 text-sm text-center border-t-2 border-t-gray-300 pt-4">
        <div class=" ">
            <span class="font-semibold px-2">{{ config('global.site_name') }}. {{ date('Y ') }}</span>
        </div>
        <div class="">
            All rights reserved
        </div>
     
    </div>
   
    </div>
</section>

