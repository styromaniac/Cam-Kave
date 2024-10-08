const lightboxElem = document.getElementById("lightbox");
const contentElem = document.getElementById("content");
let player;
let activeMediaElement = null;
let lightboxWrapper = null;
const elements = document.querySelectorAll("[data-media]");
let playingVideos = [];

// Cache name
const CACHE_NAME = 'media-cache-v1';

// Function to cache media
async function cacheMedia(url) {
    const cache = await caches.open(CACHE_NAME);
    const response = await fetch(url);
    await cache.put(url, response);
}

// Function to get media from cache or network
async function getMedia(url) {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(url);
    if (cachedResponse) {
        return cachedResponse;
    }
    const networkResponse = await fetch(url);
    await cache.put(url, networkResponse.clone());
    return networkResponse;
}

// Preload and cache all media
elements.forEach(element => {
    const mediaUrl = element.src || element.querySelector('source').src;
    cacheMedia(mediaUrl);
});

for (let i = 0; i < elements.length; i++) {
    elements[i].onclick = function(e) {
        activeMediaElement = e.target;
        openLightbox();
    };
}

function updateLightboxPosition() {
    if (!lightboxWrapper || !activeMediaElement) return;

    const rect = activeMediaElement.getBoundingClientRect();
    const updatedRect = {
        top: rect.top,
        left: rect.left,
        width: rect.width,
        height: rect.height
    };

    lightboxWrapper.style.top = '0';
    lightboxWrapper.style.left = '0';
    lightboxWrapper.style.width = '100%';
    lightboxWrapper.style.height = 'calc(100% - 56px)';

    lightboxWrapper.dataset.originalRect = JSON.stringify(updatedRect);
}

async function openLightbox() {
    lightboxElem.style.display = 'block';
    lightboxElem.offsetHeight; // Force reflow
    lightboxElem.classList.add('active');

    pauseAllVideos();

    const rect = activeMediaElement.getBoundingClientRect();
    const originalRect = {
        top: rect.top,
        left: rect.left,
        width: rect.width,
        height: rect.height
    };

    lightboxWrapper = document.createElement('div');
    lightboxWrapper.className = 'lightbox-wrapper';
    lightboxWrapper.style.position = 'fixed';
    lightboxWrapper.style.top = `${originalRect.top}px`;
    lightboxWrapper.style.left = `${originalRect.left}px`;
    lightboxWrapper.style.width = `${originalRect.width}px`;
    lightboxWrapper.style.height = `${originalRect.height}px`;
    lightboxWrapper.style.transition = 'all 0.5s ease';

    const clonedMedia = activeMediaElement.cloneNode(false);
    clonedMedia.style.width = '100%';
    clonedMedia.style.height = '100%';
    clonedMedia.style.objectFit = 'contain';

    // Get media from cache or network
    const mediaUrl = activeMediaElement.src || activeMediaElement.querySelector('source').src;
    const mediaResponse = await getMedia(mediaUrl);
    const mediaBlob = await mediaResponse.blob();
    const mediaBlobUrl = URL.createObjectURL(mediaBlob);

    clonedMedia.src = mediaBlobUrl;

    lightboxWrapper.appendChild(clonedMedia);
    lightboxElem.insertBefore(lightboxWrapper, lightboxElem.firstChild);

    lightboxWrapper.offsetHeight; // Force reflow

    updateLightboxPosition();

    if (clonedMedia.tagName.toLowerCase() === 'video') {
        player = new Plyr(clonedMedia);
        clonedMedia.controls = true;
        clonedMedia.play();
        
        setTimeout(() => {
            if (clonedMedia.videoHeight > clonedMedia.videoWidth) {
                lightboxWrapper.classList.add('plyr--tall');
            }
        }, 100);
    }

    lightboxWrapper.dataset.originalRect = JSON.stringify(originalRect);
}

function closeLightbox(event) {
    if (event) {
        event.preventDefault();
    }
    
    if (player) {
        player.destroy();
        player = null;
    }

    if (!lightboxWrapper) return;

    const currentRect = activeMediaElement.getBoundingClientRect();

    lightboxElem.classList.remove('active');

    lightboxWrapper.style.top = `${currentRect.top}px`;
    lightboxWrapper.style.left = `${currentRect.left}px`;
    lightboxWrapper.style.width = `${currentRect.width}px`;
    lightboxWrapper.style.height = `${currentRect.height}px`;

    setTimeout(() => {
        lightboxElem.removeChild(lightboxWrapper);
        lightboxElem.style.display = 'none';
        lightboxWrapper = null;
        resumeVideos();
    }, 500);
}

function pauseAllVideos() {
    playingVideos = [];
    document.querySelectorAll('video').forEach(video => {
        if (!video.paused) {
            playingVideos.push(video);
            video.pause();
        }
    });
}

function resumeVideos() {
    playingVideos.forEach(video => {
        video.play();
    });
    playingVideos = [];
}

// Add event listener to close lightbox when clicking the bat button
const lightboxBat = lightboxElem.querySelector('.bat');
if (lightboxBat) {
    lightboxBat.addEventListener('click', closeLightbox);
}

// Add event listener to close lightbox when clicking outside the media
lightboxElem.addEventListener('click', function(e) {
    if (e.target === lightboxElem) {
        closeLightbox();
    }
});

// Add event listeners for orientation change and resize
window.addEventListener('orientationchange', updateLightboxPosition);
window.addEventListener('resize', updateLightboxPosition);