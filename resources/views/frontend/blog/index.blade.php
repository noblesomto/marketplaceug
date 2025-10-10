@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')

<div class="min-h-screen bg-gray-50 pt-8 pb-20 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <section class="mb-10 sm:mb-16">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4 sm:mb-6 text-center">Our Blog</h1>
                <p class="text-base sm:text-lg text-gray-600 leading-relaxed">
                    Explore insights, tips, and updates from Marketplace Naija — your trusted source for e-commerce trends, business growth strategies, and digital innovation in Nigeria. Stay informed, get inspired, and discover how to make the most of the online marketplace economy.
                </p>

            </div>
        </section>

        <!-- Blog Posts Grid -->
        <div class="space-y-6 sm:space-y-8">
            @forelse($blogs as $blog)
            <!-- Blog Post Card -->
            <article class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                <div class="flex flex-row">
                    <!-- Image Section -->
                    <div class="w-2/5 sm:w-2/5 lg:w-1/3 flex-shrink-0">
                        <div class="relative h-full min-h-[180px] sm:min-h-[200px]">
                            <img
                                class="absolute inset-0 w-full h-full object-cover"
                                src="{{ $blog->featured_image_thumb }}"
                                alt="{{ $blog->title }}"
                            >
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="w-3/5 sm:w-3/5 lg:w-2/3 p-3 sm:p-6 lg:p-8 flex flex-col justify-between">
                        <div>
                            <!-- Category Badge (Optional) -->
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-dark_green bg-blue-100 rounded-full mb-2">
                                {{ Str::of($blog->category)->replace('-', ' ')->title() }}
                            </span>

                            <!-- Title -->
                            <h2 class="text-sm sm:text-2xl lg:text-3xl font-bold text-dark_green mb-2 sm:mb-4 hover:text-primary transition-colors duration-200 line-clamp-2">
                                <a href="/blog/{{ $blog->slug }}/{{ $blog->id }}">{{ Str::limit($blog->title, 50) }}</a>
                            </h2>

                            <!-- Excerpt -->
                            <span class="text-xs sm:text-base text-gray-600 leading-relaxed mb-3 sm:mb-4 line-clamp-2 sm:line-clamp-4">
                                <p
                                  class="blog-preview"
                                  data-full-text="{{ strip_tags($blog->content) }}"
                                >
                                  {!! Str::limit(strip_tags($blog->content), 150) !!}
                                </p>
                            </span>
                        </div>

                        <!-- Meta Info & Read More -->
                        <div class="flex flex-row items-center justify-between pt-3 sm:pt-4 border-t border-gray-100 space-y-2 sm:space-y-0">
                            <div class="flex items-center text-xs text-gray-500 space-x-2 sm:space-x-4">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="hidden sm:inline">Oct 9, 2025</span>
                                    <span class="sm:hidden">Oct 9</span>
                                </span>

                            </div>

                            <a
                                href="/blog/{{ $blog->slug }}/{{ $blog->id }}"
                                class="inline-flex items-center text-xs sm:text-base font-semibold text-dark_green-600 hover:text-primary transition-colors duration-200"
                            >
                                Read More
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            @empty

            @endforelse

        </div>

        <!-- Pagination (Optional) -->
        <div class="">
            <div class="mt-6 px-2">
              {{ $blogs->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const previews = document.querySelectorAll('.blog-preview');
    previews.forEach(el => {
      const fullText = el.dataset.fullText;
      const limit = window.innerWidth <= 640 ? 90 : 150; // sm breakpoint
      const truncated = fullText.length > limit ? fullText.substring(0, limit) + '…' : fullText;
      el.textContent = truncated;
    });
  });
</script>
@include('frontend.layouts.footer')
