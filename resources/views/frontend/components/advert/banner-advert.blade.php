<div class="p-1">
   @foreach(getAdverts() as $advert)
      <a href="{{ $advert->url }}" title="{{ $advert->company }}" target="_blank"><img class="object-cover w-full h-28 md:h-64" src="{{ asset('uploads/advertising/'.$advert->image) }}"></a>
    @endforeach
</div>