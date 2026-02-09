{{-- n8n Chatbot Widget --}}
<style>
    .n8n-chat .chat-message-markdown ul {
        list-style: disc !important;
        padding-left: 20px !important;
        margin-top: 5px !important;
        margin-bottom: 5px !important;
    }
    .n8n-chat .chat-message-markdown ol {
        list-style: decimal !important;
        padding-left: 20px !important;
        margin-top: 5px !important;
        margin-bottom: 5px !important;
    }
    .n8n-chat .chat-message-markdown li {
        margin-bottom: 2px !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/@n8n/chat/style.css" rel="stylesheet" />
<script type="module">
    import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/chat.bundle.es.js';
    createChat({
        webhookUrl: 'https://n8n.thevistiq.com/webhook/7a9bdb2c-74c1-4cb3-bb23-1f34dbe58fd8/chat',
        initialMessages: [
            'Hi there! 👋',
            'My name is Qwaiting. How can I assist you today with your appointment?'
        ],
        chatbotName: 'Qwaiting',
        chatbotGreeting: 'Start a chat. We\'re here to help you 24/7.'
    });
</script>
