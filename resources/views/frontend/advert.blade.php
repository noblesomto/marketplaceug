@include('frontend.layouts.header-adverts')
@include('frontend.layouts.nav')
@include('frontend.layouts.product-nav')
@include('frontend.layouts.search')


<section class="w-full lg:w-4/6 mx-auto mb-20">
  <div class=" my-5 hidden lg:block">
   @include('frontend.components.advert.banner-advert')
  </div>


  <div class="grid grid-cols-6 gap-3">
        
        <div class="col-span-6 lg:col-span-4">
            <div class="hidden lg:block">
                <div class="my-2 flex justify-start gap-4 font-semibold ml-1 ">
                <span class="flex items-center space-x-2">
                    <a href="/">Home</a>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
                <span class="flex items-center space-x-2">
                    <a href="{{ url('/category/'.$cat->category_slug) }}">{{ $cat->category }}</a>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
                <span class="flex items-center space-x-2">
                    <a href="{{ url('/category/' . $cat->category_slug . '/' . $sub_cat->sub_cat_slug) }}">{{ $sub_cat->sub_category }}</a>
                </span>
            </div>
            </div>
            <div class="bg-white p-2">@include('frontend.components.advert.slider')</div>
            <div>@include('frontend.components.advert.ad-body')</div>
        </div>
        <div class="col-span-6 lg:col-span-2">
          <div class="px-2 lg:px-1">@include('frontend.components.advert.sidebar')</div>
        </div>
  </div>

  <div class="max-w-4xl">
    <div>@include('frontend.components.advert.similar-ad')</div>
  </div>
</section>




<script>
document.getElementById('shareBtn').addEventListener('click', async () => {

    const shareTitle = {!! json_encode($ad->ad_title ?? '') !!};
    const shareText = {!! json_encode($ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160)) !!};
    const shareUrl = window.location.href;

    if (navigator.share) {
        try {
            await navigator.share({
                title: shareTitle,
                text: shareText,
                url: shareUrl
            });
        } catch (err) {
            console.log('Share cancelled', err);
        }
    } else {
        alert("Sharing is not supported on this device.");
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-toggle').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const adId = this.dataset.adId;
            const svg = this.querySelector('svg');

            fetch(`/user/add-wishlist/${adId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle icon
                    if (data.in_wishlist) {
                        svg.classList.add('fill-red-500', 'text-red-500');
                        svg.classList.remove('fill-none');
                        this.title = 'Remove from Wishlist';

                        // SweetAlert for added to wishlist
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Wishlist!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).then(() => {
                            location.reload(); // Refresh page after alert
                        });
                    } else {
                        svg.classList.remove('fill-red-500', 'text-red-500');
                        svg.classList.add('fill-none');
                        this.title = 'Add to Wishlist';

                        // SweetAlert for removed from wishlist
                        Swal.fire({
                            icon: 'info',
                            title: 'Removed from Wishlist',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).then(() => {
                            location.reload(); // Refresh page after alert
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Error SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong! Please try again.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
            });
        });
    });
});
</script>

@include('frontend.layouts.footer')


