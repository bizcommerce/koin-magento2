const koin = {
    copyCode: function(button, linkClass, onlyNumbers) {
        let str = document.querySelector(linkClass).innerText;
        if (onlyNumbers) {
            str = str.replace(/[^0-9]+/g, "");
        }
        const originalText = button.innerText;
        const el = document.createElement('textarea');
        el.value = str;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        button.innerText = button.getAttribute('data-text');
        setTimeout(() => {
            button.innerText = originalText;
        }, 5000);
    },

    countdown: function(secondsInFuture, orderUrl) {
        if (secondsInFuture > 0) {
            document.querySelector('.simple-countdown').style.display = 'block';

            const startDate = new Date();
            const futureDate = new Date(startDate.getTime() + secondsInFuture * 1000);

            // Update the countdown every second
            const interval = setInterval(() => {
                const now = new Date();
                const timeLeft = futureDate - now;

                if (timeLeft <= 0) {
                    clearInterval(interval);
                    window.location.href = orderUrl;
                    return;
                }

                // Calculate hours, minutes, and seconds left
                const days = Math.floor((timeLeft / (1000 * 60 * 60 * 24)));
                const hours = Math.floor((timeLeft / (1000 * 60 * 60)) % 24);
                const minutes = Math.floor((timeLeft / (1000 * 60)) % 60);
                const seconds = Math.floor((timeLeft / 1000) % 60);

                if (days == 0) {
                    document.querySelector('.countdown-days').style.display = 'none';
                }
                document.querySelector('.countdown-days').innerText = String(days).padStart(2, '0');
                document.querySelector('.countdown-hour').innerText = String(hours).padStart(2, '0');
                document.querySelector('.countdown-min').innerText = String(minutes).padStart(2, '0');
                document.querySelector('.countdown-sec').innerText = String(seconds).padStart(2, '0');
            }, 1000);
        }
    }
};
