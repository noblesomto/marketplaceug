<div class="p-1 space-y-2">
   @foreach(getAdverts() as $advert)
      <a href="{{ $advert->url }}" title="{{ $advert->company }}" target="_blank" rel="noopener noreferrer">
         <div class="w-full aspect-[3/1] md:aspect-[4/1] bg-white">
            <img class="object-contain w-full h-full"
                 src="{{ asset('uploads/advertising/'.$advert->image) }}"
                 alt="{{ $advert->company }} banner"
                 loading="lazy">
         </div>
      </a>
   @endforeach
</div>
