let desktopPage = 2; // first page already rendered
let mobilePage = 2; // first page already rendered
let desktopLoading = false;
let mobileLoading = false;

// Desktop Part
const loadMoreAdsDesktop = () => {
    if (desktopLoading) return;
    desktopLoading = true;
    document.getElementById('loading').classList.remove('hidden');
    fetch(`/listings/fetchDesktop?page=${desktopPage}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('loading').classList.add('hidden');
            if (data.html.trim()) {
                document.getElementById('listings-container').insertAdjacentHTML('beforeend', data.html);
                if (data.next_page) {
                    desktopPage = data.next_page;
                    desktopLoading = false;
                } else {
                    document.getElementById('load-more-trigger')?.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error loading desktop listings:', error);
            document.getElementById('loading').classList.add('hidden');
            desktopLoading = false;
        });
};

const desktopObserver = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
        loadMoreAdsDesktop();
    }
});

const desktopTrigger = document.getElementById('load-more-trigger');
if (desktopTrigger) desktopObserver.observe(desktopTrigger);


// Mobile Part
const loadMoreAdsMobile = () => {
    if (mobileLoading) return;
    mobileLoading = true;
    document.getElementById('loading-mobile').classList.remove('hidden');
    fetch(`/listings/fetchMobile?page=${mobilePage}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('loading-mobile').classList.add('hidden');
            if (data.html.trim()) {
                document.getElementById('listings-container-mobile').insertAdjacentHTML('beforeend', data.html);
                if (data.next_page) {
                    mobilePage = data.next_page;
                    mobileLoading = false;
                } else {
                    document.getElementById('load-more-trigger-mobile')?.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error loading mobile listings:', error);
            document.getElementById('loading-mobile').classList.add('hidden');
            mobileLoading = false;
        });
};

const mobileObserver = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
        loadMoreAdsMobile();
    }
});

const mobileTrigger = document.getElementById('load-more-trigger-mobile');
if (mobileTrigger) mobileObserver.observe(mobileTrigger);
