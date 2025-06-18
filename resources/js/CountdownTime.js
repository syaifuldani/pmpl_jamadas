class CountdownManager {
    constructor() {
        this.intervals = new Map();
        this.init();
    }

    init() {
        this.updateCountdowns();
        this.startGlobalInterval();
        this.bindEvents();
    }

    updateCountdowns() {
        const countdownElements = document.querySelectorAll(
            ".countdown-timer[data-expire-time]"
        );

        countdownElements.forEach((element) => {
            const expireTime = parseInt(
                element.getAttribute("data-expire-time")
            );
            const orderId = element.getAttribute("data-order-id");

            // Clear existing interval untuk element ini
            if (this.intervals.has(orderId)) {
                clearInterval(this.intervals.get(orderId));
            }

            this.updateSingleCountdown(element, expireTime, orderId);
        });
    }

    updateSingleCountdown(element, expireTime, orderId) {
        const countdownText = element.querySelector(".countdown-text");
        const countdownLabel = element.querySelector(".countdown-label");

        const updateTimer = () => {
            // Menggunakan waktu server yang sudah diset ke Asia/Jakarta
            const currentTime = Math.floor(Date.now() / 1000);
            const timeRemaining = expireTime - currentTime;

            // Remove existing classes
            element.classList.remove("expired", "warning");

            if (timeRemaining <= 0) {
                // Transaksi sudah expired
                countdownLabel.textContent = "MENUNGGU PEMBAYARAN";
                countdownText.textContent = "00:00:00";
                countdownText.classList.add("expired-text");
                element.classList.add("expired");

                // Tambahkan status expired
                let statusDiv = element.querySelector(".countdown-status");
                if (!statusDiv) {
                    statusDiv = document.createElement("div");
                    statusDiv.className = "countdown-status";
                    element.appendChild(statusDiv);
                }
                statusDiv.textContent = "Waktu Habis";

                // Clear interval untuk countdown ini
                if (this.intervals.has(orderId)) {
                    clearInterval(this.intervals.get(orderId));
                    this.intervals.delete(orderId);
                }

                // Auto reload setelah 3 detik untuk update status
                setTimeout(() => {
                    if (document.visibilityState === "visible") {
                        location.reload();
                    }
                }, 3000);
            } else {
                // Transaksi masih aktif
                countdownLabel.textContent = "MENUNGGU PEMBAYARAN";
                countdownText.classList.remove("expired-text");

                // Add warning class jika kurang dari 1 jam
                if (timeRemaining <= 3600) {
                    element.classList.add("warning");
                }

                // Format waktu: HH:MM:SS
                const hours = Math.floor(timeRemaining / 3600);
                const minutes = Math.floor((timeRemaining % 3600) / 60);
                const seconds = timeRemaining % 60;

                const formattedTime =
                    String(hours).padStart(2, "0") +
                    ":" +
                    String(minutes).padStart(2, "0") +
                    ":" +
                    String(seconds).padStart(2, "0");

                countdownText.textContent = formattedTime;

                // Remove countdown-status jika ada
                const statusDiv = element.querySelector(".countdown-status");
                if (statusDiv) {
                    statusDiv.remove();
                }
            }
        };

        // Update immediately
        updateTimer();

        // Set interval untuk update setiap detik
        const intervalId = setInterval(updateTimer, 1000);
        this.intervals.set(orderId, intervalId);
    }

    startGlobalInterval() {
        // Backup interval untuk memastikan semua countdown ter-update
        setInterval(() => {
            if (document.visibilityState === "visible") {
                this.updateCountdowns();
            }
        }, 30000); // Setiap 30 detik
    }

    bindEvents() {
        // Handle visibility change
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible") {
                this.updateCountdowns();
            }
        });

        // Handle page unload
        window.addEventListener("beforeunload", () => {
            this.cleanup();
        });
    }

    cleanup() {
        this.intervals.forEach((intervalId) => {
            clearInterval(intervalId);
        });
        this.intervals.clear();
    }
}

// Initialize countdown manager when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    window.countdownManager = new CountdownManager();
});

// Fallback initialization jika DOMContentLoaded sudah lewat
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
        if (!window.countdownManager) {
            window.countdownManager = new CountdownManager();
        }
    });
} else {
    window.countdownManager = new CountdownManager();
}
