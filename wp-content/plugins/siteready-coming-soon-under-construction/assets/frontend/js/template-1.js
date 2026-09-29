(function () {
    function updateCountdown(element) {
        var endTime = parseInt(element.dataset.timestrampTotal) * 1000; // convert seconds to ms

        function tick() {
            var now = new Date().getTime();
            var distance = endTime - now;

            if (distance <= 0) {
                element.querySelector("#days-num").textContent = "00";
                element.querySelector("#hours-num").textContent = "00";
                element.querySelector("#minutes-num").textContent = "00";
                element.querySelector("#seconds-num").textContent = "00";
                clearInterval(timerInterval);
                return;
            }

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            element.querySelector("#days-num").textContent = String(days).padStart(2, '0');
            element.querySelector("#hours-num").textContent = String(hours).padStart(2, '0');
            element.querySelector("#minutes-num").textContent = String(minutes).padStart(2, '0');
            element.querySelector("#seconds-num").textContent = String(seconds).padStart(2, '0');
        }

        var timerInterval = setInterval(tick, 1000);
        tick();
    }

    document.addEventListener("DOMContentLoaded", function() {
        var countdownElements = document.querySelectorAll(".coming-soon-countdown");
        countdownElements.forEach(function(el){
            updateCountdown(el);
        });
    });
})();
