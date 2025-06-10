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
    console.log("toggleChat function called");
    const chatContainer = document.querySelector(".chat-container");

    if (chatContainer) {
        console.log("Chat container found, toggling active class");
        chatContainer.classList.toggle("active");

        // Log current state
        const isActive = chatContainer.classList.contains("active");
        console.log("Chat container is now:", isActive ? "active" : "inactive");

        // Chat toggle button stays visible and serves as both open/close button
    } else {
        console.error("Chat container not found in DOM");
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
    console.log("Chat.js DOM loaded - Setting up chat functionality");

    // Tunggu sebentar untuk memastikan semua elemen DOM ter-render
    setTimeout(function () {
        initializeChatFunctionality();
    }, 100);
});

// Function to initialize chat functionality
function initializeChatFunctionality() {
    console.log("Initializing chat functionality...");

    loadChatHistory();

    // Setup event listeners dengan multiple selectors
    const chatToggle =
        document.querySelector(".chat-toggle") ||
        document.getElementById("chatToggleBtn");

    if (chatToggle) {
        console.log("Chat toggle found, adding event listeners");

        // Remove any existing event listeners to prevent duplicates
        chatToggle.removeAttribute("onclick");
        const newChatToggle = chatToggle.cloneNode(true);
        chatToggle.parentNode.replaceChild(newChatToggle, chatToggle);

        // Add multiple event types for maximum compatibility
        newChatToggle.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Chat toggle onclick triggered");
            if (typeof toggleChat === "function") {
                toggleChat();
            } else {
                console.error("toggleChat function not available");
                fallbackToggleChat();
            }
        };

        newChatToggle.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Chat toggle addEventListener triggered");
            if (typeof toggleChat === "function") {
                toggleChat();
            } else {
                console.error("toggleChat function not available");
                fallbackToggleChat();
            }
        });

        // Add touchstart for mobile devices
        newChatToggle.addEventListener("touchstart", function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Chat toggle touchstart triggered");
            if (typeof toggleChat === "function") {
                toggleChat();
            } else {
                console.error("toggleChat function not available");
                fallbackToggleChat();
            }
        });

        console.log("Chat toggle event listeners added successfully");
    } else {
        console.error("Chat toggle not found in DOM");

        // Retry after a delay
        setTimeout(function () {
            console.log("Retrying chat toggle setup...");
            initializeChatFunctionality();
        }, 1000);
    }

    // Setup input listener
    const chatInput = document.getElementById("chat-input");
    if (chatInput) {
        chatInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                sendMessage();
            }
        });
        console.log("Chat input event listener added");
    } else {
        console.error("Chat input not found in DOM");
    }
}

// Fallback function untuk toggle chat jika function utama tidak tersedia
function fallbackToggleChat() {
    console.log("Using fallback toggle chat");
    const chatContainer = document.querySelector(".chat-container");
    if (chatContainer) {
        chatContainer.classList.toggle("active");
        console.log("Chat container toggled successfully");
    } else {
        console.error("Chat container not found for fallback toggle");
    }
}
