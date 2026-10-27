document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('videoModal');
    var modalVideo = document.getElementById('modalVideo');
    var videoCard = document.getElementById('videoCard');

    if (!modal || !videoCard || !modalVideo) {
        return;
    }

    if (videoCard.classList.contains('video-card-bottom-right') && document.body) {
        document.body.classList.add('bis-has-video-card-bottom-right');
    }

    function openModal() {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        try {
            modalVideo.currentTime = 0;
            modalVideo.play();
        } catch (e) {}
    }

    function closeModal() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        try {
            modalVideo.pause();
        } catch (e) {}
    }

    videoCard.addEventListener('click', openModal);

    var closeBtn = modal.querySelector('.video-modal-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            if (modal.style.display !== 'none' && getComputedStyle(modal).display !== 'none') {
                closeModal();
            }
        }
    });

    document.querySelectorAll('[data-video-card-contact]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal();
            var callbackOverlay = document.getElementById('callbackOverlay');
            if (callbackOverlay) {
                callbackOverlay.classList.add('active');
            }
        });
    });

    setTimeout(function () {
        videoCard.style.opacity = '1';
    }, 500);
});
