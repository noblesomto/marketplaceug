@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('dashboard.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2 flex justify-between">
        <span>Feedbacks</span>
        <div class="space-x-4">
        	<a title="Logout" href="/user/logout" ><i class="bi bi-box-arrow-right text-lg lg:text-3xl"></i></a>
            <a title="Feedbacks" href="/user/feedbacks" ><i class="bi bi-chat-right-dots text-lg lg:text-3xl"></i></a>
        	<a title="Settings" href="/user/settings"><i class="bi bi-gear text-lg lg:text-3xl"></i></a>
        </div>
    </div>

    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
    	<div class="w-full bg-white shadow p-3">
			<div class="flex flex-col">
                @forelse($feedbacks as $row)

                @empty
                  <div class="flex flex-col mb-4 items-center bg-white">
                        <span>
                            <img width="100" height="100" src="{{ asset('frontend/images/no-feedback.png') }}" alt="No Feed backs"/>
                        </span>
                        <span class="text-center">
                            There are no Feedbacks yet.<br>
                            Ask your customers to leave a feedbaack about you <br>
                            Copy the link and send to to them
                        </span>
                    </div>
                @endforelse
                <button class="bg-dark_green px-3 py-2 inline-block text-white font-semibold rounded">Copy my Link</button>

                <div class="mt-6 px-2">
                  {{ $feedbacks->links('pagination::tailwind') }}
                </div>
		    </div>
		</div>
    </div>

    
</section>



@include('dashboard.layouts.footer')
