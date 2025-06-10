// Fungsi untuk memuat chat dari session
function loadChatHistory() {
    fetch("../config/process_chat.php")
        .then((response) => response.json())
        .then((data) => {
            const chatBox = document.querySelector(".chat-box");
            if (chatBox && data.history && data.history.length > 0) {
                data.history.forEach((chat) => {
                    // Tambahkan pesan user
                    const userMessage = document.createElement("div");
                    userMessage.className = "chat-message user-message";
                    userMessage.textContent = chat.pesan_pengguna;
                    chatBox.appendChild(userMessage);

                    // Tambahkan respons bot
                    const botMessage = document.createElement("div");
                    botMessage.className = "chat-message bot-message";
                    botMessage.innerHTML = chat.respons_jawaban;
                    chatBox.appendChild(botMessage);
                });
                // Scroll ke pesan terakhir
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        })
        .catch((error) => console.error("Error:", error));
}

// Fungsi toggle chat - tersedia secara global
function toggleChat() {
    const chatContainer = document.querySelector(".chat-container");

    if (chatContainer) {
        chatContainer.classList.toggle("active");
        // Chat toggle button stays visible and serves as both open/close button
    }
}

// Fungsi send message - tersedia secara global
function sendMessage() {
    const input = document.getElementById("chat-input");
    if (!input) return;

    const message = input.value.trim();
    if (!message) return;

    const chatBox =
        document.querySelector(".chat-box") ||
        document.getElementById("chatMessages");
    if (!chatBox) return;

    // Tambahkan pesan pengguna dengan animasi
    const userMessage = document.createElement("div");
    userMessage.className = "chat-message user-message";
    userMessage.textContent = message;
    chatBox.appendChild(userMessage);

    // Scroll ke bawah dengan animasi smooth
    chatBox.scrollTo({
        top: chatBox.scrollHeight,
        behavior: "smooth",
    });

    // Reset input
    input.value = "";

    // Kirim pesan ke server
    fetch("../config/process_chat.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "message=" + encodeURIComponent(message),
    })
        .then((response) => response.json())
        .then((data) => {
            // Tambahkan respons bot dengan animasi
            const botMessage = document.createElement("div");
            botMessage.className = "chat-message bot-message";
            botMessage.innerHTML = data.response;

            // Tambahkan efek ketikan
            setTimeout(() => {
                chatBox.appendChild(botMessage);
                chatBox.scrollTo({
                    top: chatBox.scrollHeight,
                    behavior: "smooth",
                });
            }, 500);
        })
        .catch((error) => {
            console.error("Error:", error);
            const errorMessage = document.createElement("div");
            errorMessage.className = "chat-message bot-message";
            errorMessage.textContent =
                "Maaf, terjadi kesalahan. Silakan coba lagi.";
            chatBox.appendChild(errorMessage);
        });
}

// Panggil fungsi saat chatbot dibuka
document.addEventListener("DOMContentLoaded", function () {
    loadChatHistory();

    // Setup event listeners
    const chatToggle = document.querySelector(".chat-toggle");

    if (chatToggle) {
        chatToggle.addEventListener("click", function () {
            toggleChat();
        });
    }

    // Setup input listener
    const chatInput = document.getElementById("chat-input");
    if (chatInput) {
        chatInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                sendMessage();
            }
        });
    }
});
