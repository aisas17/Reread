<?php /* AI Chat Widget — strings via t() from includes/i18n.php (loaded by header) */ ?>
<div id="chat-widget" class="chat-widget">
    <button id="chat-toggle" class="chat-toggle" title="<?= htmlspecialchars(t('chat.toggle')); ?>">
        <i class="fas fa-comments"></i>
    </button>
    
    <div id="chat-box" class="chat-box hidden">
        <div class="chat-header">
            <h3><?= htmlspecialchars(t('chat.title')); ?></h3>
            <button id="chat-close" class="chat-close">&times;</button>
        </div>
        
        <div id="chat-messages" class="chat-messages">
            <div class="chat-message bot-message">
                <p>👋 <?= htmlspecialchars(t('chat.welcome')); ?></p>
            </div>
        </div>
        
        <div class="chat-input-area">
            <input 
                type="text" 
                id="chat-input" 
                class="chat-input" 
                placeholder="<?= htmlspecialchars(t('chat.placeholder')); ?>" 
                autocomplete="off"
            >
            <button id="chat-send" class="chat-send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    font-family: inherit;
    z-index: 9999;
}

.chat-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.chat-toggle:active {
    transform: scale(0.95);
}

.chat-box {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
}

.chat-box.hidden {
    display: none;
    opacity: 0;
    transform: scale(0.95);
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chat-close {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}

.chat-close:hover {
    transform: rotate(90deg);
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f5f5f5;
}

.chat-message {
    margin-bottom: 12px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bot-message p {
    background: white;
    padding: 12px 16px;
    border-radius: 12px;
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: #333;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.user-message {
    text-align: right;
}

.user-message p {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 16px;
    border-radius: 12px;
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    display: inline-block;
    max-width: 80%;
}

.chat-input-area {
    display: flex;
    gap: 8px;
    padding: 12px;
    background: white;
    border-top: 1px solid #eee;
}

.chat-input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 24px;
    padding: 10px 16px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}

.chat-input:focus {
    border-color: #667eea;
}

.chat-send {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
}

.chat-send:hover:not(:disabled) {
    transform: scale(1.05);
}

.chat-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.chat-loading {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
}

.chat-loading span {
    width: 8px;
    height: 8px;
    background: #667eea;
    border-radius: 50%;
    animation: bounce 1.4s infinite;
}

.chat-loading span:nth-child(2) {
    animation-delay: 0.2s;
}

.chat-loading span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {
    0%, 80%, 100% {
        opacity: 0.5;
        transform: translateY(0);
    }
    40% {
        opacity: 1;
        transform: translateY(-8px);
    }
}

@media (max-width: 480px) {
    .chat-box {
        width: 100vw;
        height: 100vh;
        bottom: 0;
        right: 0;
        border-radius: 0;
        max-width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.getElementById('chat-toggle');
    const chatBox = document.getElementById('chat-box');
    const chatClose = document.getElementById('chat-close');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const chatMessages = document.getElementById('chat-messages');
    
    // Toggle chat box
    chatToggle.addEventListener('click', () => {
        chatBox.classList.toggle('hidden');
        if (!chatBox.classList.contains('hidden')) {
            chatInput.focus();
        }
    });
    
    chatClose.addEventListener('click', () => {
        chatBox.classList.add('hidden');
    });
    
    // Send message
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message to chat
        addMessage(message, 'user');
        chatInput.value = '';
        chatSend.disabled = true;
        
        // Show loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'chat-loading';
        loadingDiv.innerHTML = '<span></span><span></span><span></span>';
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Send to server
        fetch('includes/groq_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            loadingDiv.remove();
            chatSend.disabled = false;
            
            if (data.success) {
                addMessage(data.message, 'bot');
            } else {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
            chatMessages.scrollTop = chatMessages.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
            loadingDiv.remove();
            chatSend.disabled = false;
            addMessage('Sorry, something went wrong. Please try again.', 'bot');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }
    
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}-message`;
        const p = document.createElement('p');
        p.textContent = text;
        messageDiv.appendChild(p);
        chatMessages.appendChild(messageDiv);
    }
    
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});
</script>
