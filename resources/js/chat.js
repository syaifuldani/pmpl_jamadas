// Fungsi untuk memuat chat dari session
function loadChatHistory() {
    fetch('../config/process_chat.php')
        .then(response => response.json())
        .then(data => {
            const chatBox = document.querySelector('.chat-box');
            if (data.history && data.history.length > 0) {
                data.history.forEach(chat => {
                    // Tambahkan pesan user
                    const userMessage = document.createElement('div');
                    userMessage.className = 'chat-message user-message';
                    userMessage.textContent = chat.pesan_pengguna;
                    chatBox.appendChild(userMessage);

                    // Tambahkan respons bot
                    const botMessage = document.createElement('div');
                    botMessage.className = 'chat-message bot-message';
                    botMessage.innerHTML = chat.respons_jawaban;
                    chatBox.appendChild(botMessage);
                });
                // Scroll ke pesan terakhir
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Panggil fungsi saat chatbot dibuka
document.addEventListener('DOMContentLoaded', loadChatHistory);

const chatToggle = document.querySelector('.chat-toggle');
const chatContainer = document.querySelector('.chat-container');

chatToggle.addEventListener('click', () => {
    chatContainer.classList.toggle('active');
});

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (message) {
        const chatBox = document.querySelector('.chat-box');
        
        // Tambahkan pesan pengguna dengan animasi
        const userMessage = document.createElement('div');
        userMessage.className = 'chat-message user-message';
        userMessage.textContent = message;
        chatBox.appendChild(userMessage);
        
        // Scroll ke bawah dengan animasi smooth
        chatBox.scrollTo({
            top: chatBox.scrollHeight,
            behavior: 'smooth'
        });
        
        // Reset input
        input.value = '';
        
        // Kirim pesan ke server
        fetch('../config/process_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            // Tambahkan respons bot dengan animasi
            const botMessage = document.createElement('div');
            botMessage.className = 'chat-message bot-message';
            botMessage.innerHTML = data.response;
            
            // Tambahkan efek ketikan
            setTimeout(() => {
                chatBox.appendChild(botMessage);
                chatBox.scrollTo({
                    top: chatBox.scrollHeight,
                    behavior: 'smooth'
                });
            }, 500);
        })
        .catch(error => console.error('Error:', error));
    }
}

// Tambahkan event listener untuk Enter key
document.getElementById('chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});