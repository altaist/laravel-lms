<!DOCTYPE html>
<html>
<head>
    <title>Telegram Bot Test Interface</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .messages {
            border: 1px solid #ccc;
            padding: 10px;
            height: 400px;
            overflow-y: auto;
            margin-bottom: 10px;
            font-family: monospace;
        }
        .message {
            margin: 5px 0;
            padding: 5px;
            border-radius: 5px;
            white-space: pre-wrap;
        }
        .incoming { background: #e3f2fd; }
        .outgoing { background: #f5f5f5; }
        .error { background: #ffebee; color: #c62828; }
        .system { background: #f3e5f5; }
        .button-group {
            margin-bottom: 10px;
        }
        button {
            margin-right: 10px;
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
            background: #fff;
            cursor: pointer;
        }
        button:hover {
            background: #f5f5f5;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="messages" id="messages"></div>
    <div class="button-group">
        <button onclick="startPolling()" id="startBtn">Start Auto Polling</button>
        <button onclick="stopPolling()" id="stopBtn" disabled>Stop Polling</button>
        <button onclick="singlePoll()" id="singlePollBtn">Single Poll</button>
        <button onclick="restoreWebhook()" id="webhookBtn">Restore Webhook</button>
    </div>

    <script>
        let pollingInterval = null;
        let offset = -1;

        // Настройка axios
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

        function updateButtons(polling) {
            document.getElementById('startBtn').disabled = polling;
            document.getElementById('stopBtn').disabled = !polling;
            document.getElementById('webhookBtn').disabled = polling;
            document.getElementById('singlePollBtn').disabled = polling;
        }

        function startPolling() {
            if (pollingInterval) return;
            
            pollingInterval = setInterval(pollUpdates, 3000);
            updateButtons(true);
            displayMessage('Started polling...', 'system');
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                updateButtons(false);
                displayMessage('Stopped polling.', 'system');
            }
        }

        async function pollUpdates() {
            try {
                const response = await axios.get(`/telegram/test/poll?offset=${offset}`);
                
                if (response.data.success) {
                    const updates = response.data.updates || [];
                    offset = response.data.next_offset;

                    updates.forEach(update => {
                        if (update.message) {
                            displayMessage(update.message, 'incoming');
                        }
                        if (update.response && update.response.status === 'success') {
                            displayMessage('✓ Message processed', 'system');
                        }
                    });
                } else if (response.data.error) {
                    displayMessage(`Error: ${response.data.error}`, 'error');
                    stopPolling();
                }
            } catch (error) {
                console.error('Polling error:', error);
                displayMessage(`Error: ${error.response?.data?.error || error.message}`, 'error');
                stopPolling();
            }
        }

        async function restoreWebhook() {
            try {
                const response = await axios.post('/telegram/test/restore-webhook');
                if (response.data.success) {
                    displayMessage('Webhook restored successfully', 'system');
                } else {
                    displayMessage(`Error: ${response.data.error}`, 'error');
                }
            } catch (error) {
                console.error('Restore webhook error:', error);
                displayMessage(`Error restoring webhook: ${error.response?.data?.error || error.message}`, 'error');
            }
        }

        async function singlePoll() {
            try {
                const singlePollBtn = document.getElementById('singlePollBtn');
                singlePollBtn.disabled = true;
                displayMessage('Sending single poll request...', 'system');
                
                const response = await axios.get(`/telegram/test/single-poll?offset=${offset}`);
                
                if (response.data.success) {
                    const updates = response.data.updates || [];
                    offset = response.data.next_offset;

                    if (updates.length > 0) {
                        updates.forEach(update => {
                            if (update.message) {
                                displayMessage(update.message, 'incoming');
                            }
                            if (update.response && update.response.status === 'success') {
                                displayMessage('✓ Message processed', 'system');
                            }
                        });
                    } else {
                        displayMessage('No new messages', 'system');
                    }
                } else if (response.data.error) {
                    displayMessage(`Error: ${response.data.error}`, 'error');
                }
            } catch (error) {
                console.error('Single poll error:', error);
                displayMessage(`Error: ${error.response?.data?.error || error.message}`, 'error');
            } finally {
                document.getElementById('singlePollBtn').disabled = false;
            }
        }

        function displayMessage(message, type) {
            const messagesDiv = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            let text = '';
            if (typeof message === 'object') {
                try {
                    text = JSON.stringify(message, null, 2);
                } catch (e) {
                    text = 'Error formatting message';
                }
            } else {
                text = message.toString();
            }
            
            messageDiv.textContent = `[${new Date().toLocaleTimeString()}] ${text}`;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    </script>
</body>
</html>